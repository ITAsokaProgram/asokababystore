<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
use Cloudinary\Cloudinary;
header('Content-Type: application/json');

$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? '';
$token = $auth_header ? str_replace('Bearer ', '', $auth_header) : null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}
$verif = verify_token($token);
if (!$verif) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Invalid token']);
    exit;
}

// Configure Cloudinary
$env = parse_ini_file(__DIR__ . '/../../../config.env');
$cloudinary = new \Cloudinary\Cloudinary([
    'cloud' => [
        'cloud_name' => $env['CLOUDINARY_NAME'],
        'api_key' => $env['CLOUDINARY_KEY'],
        'api_secret' => $env['CLOUDINARY_SECRET'],
    ],
]);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $id = $_POST['edit_idhistory_aset'] ?? null;
    if (!$id) throw new Exception('Missing idhistory_aset');

    // load existing row
    $q = $conn->prepare('SELECT * FROM history_aset WHERE idhistory_aset = ? LIMIT 1');
    $q->bind_param('i', $id);
    $q->execute();
    $old = $q->get_result()->fetch_assoc();
    $q->close();
    if (!$old) throw new Exception('Record not found');

    // fields to consider
    $fields = ['nama_barang','merk','harga_beli','nama_toko','tanggal_beli','tanggal_ganti','tanggal_perbaikan','tanggal_mutasi','tanggal_rusak','group_aset','mutasi_untuk','mutasi_dari','kd_store','status', 'no_seri', 'keterangan'];
    $updates = [];
    $types = '';
    $values = [];
    $changes = [];

        foreach ($fields as $f) {
            // accept both plain field or prefixed 'edit_' (frontend sends edit_<field>)
            $newv = $_POST['edit_' . $f] ?? ($_POST[$f] ?? null);
        // normalize datetime / date inputs similar to insert
        if (in_array($f, ['tanggal_beli','tanggal_ganti','tanggal_perbaikan','tanggal_mutasi','tanggal_rusak']) && $newv !== null) {
            // replace T with space
            $newv = str_replace('T',' ',$newv);
            // if only date provided (YYYY-MM-DD), append midnight
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $newv)) {
                $newv .= ' 00:00:00';
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $newv)) $newv .= ':00';
            if ($newv === '') $newv = null;
        }
        if ($newv !== null && (string)$newv !== (string)($old[$f] ?? '')) {
            $updates[] = "$f = ?";
            $types .= 's';
            $values[] = $newv;
            $changes[] = [ 'field' => $f, 'old' => $old[$f] ?? null, 'new' => $newv ];
        }
    }

    // handle image upload
    $image_url = $old['image_url'] ?? null;
        // handle image upload - accept 'edit_image' as uploaded file field
        if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
            $upload = $cloudinary->uploadApi()->upload($_FILES['edit_image']['tmp_name'], ['folder' => 'aset_barang']);
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = $cloudinary->uploadApi()->upload($_FILES['image']['tmp_name'], ['folder' => 'aset_barang']);
        } else {
            $upload = null;
        }
        if ($upload) {
            $image_url = $upload['secure_url'];
            $updates[] = 'image_url = ?';
            $types .= 's';
            $values[] = $image_url;
            $changes[] = ['field' => 'image_url', 'old' => $old['image_url'] ?? null, 'new' => $image_url];
    }

    if (count($updates) === 0) {
        echo json_encode(['status' => true, 'message' => 'No changes']);
        exit;
    }

    // build dynamic update
    $sql = 'UPDATE history_aset SET ' . implode(', ', $updates) . ' WHERE idhistory_aset = ?';
    $types .= 'i';
    $values[] = $id;

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('DB prepare failed: '.$conn->error);
    // bind dynamic
    $bind = [];
    $bind[] = $types;
    for ($i=0;$i<count($values);$i++){
        $bind[] = &$values[$i];
    }
    call_user_func_array([$stmt,'bind_param'],$bind);
    if (!$stmt->execute()) throw new Exception('Failed to update: '.$stmt->error);
    $stmt->close();

    // insert into log_history_aset
    $userId = $verif->kode ?? ($verif->kode ?? null) ;
    $logStmt = $conn->prepare('INSERT INTO log_history_aset (idhistory_aset, id_user, log_activity, created_at) VALUES (?, ?, ?, NOW())');
    $log_activity = json_encode($changes, JSON_UNESCAPED_UNICODE);
    $logStmt->bind_param('iis', $id, $userId, $log_activity);
    $logStmt->execute();
    $logStmt->close();

    echo json_encode(['status' => true, 'message' => 'Updated', 'changes' => $changes]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
