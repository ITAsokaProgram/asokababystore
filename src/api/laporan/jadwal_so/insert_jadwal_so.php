<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
require_once __DIR__ . '/../../../utils/Logger.php';
header('Content-Type: application/json');
$logger = new AppLogger('debug_jadwal_so.log');
$user_kode = 'UNKNOWN';
$user_name = 'System';
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        $decoded = verify_token($matches[1]);
        $logger->info("CHECKING DECODED DATA: " . json_encode($decoded));
        if ($decoded) {
            if (is_array($decoded)) {
                $user_kode = $decoded['kode'] ?? 'UNKNOWN';
                $user_name = $decoded['nama'] ?? 'System';
            } elseif (is_object($decoded)) {
                $user_kode = $decoded->kode ?? 'UNKNOWN';
                $user_name = $decoded->nama ?? 'System';
            }
        }
    }
} catch (Exception $e) {
    $logger->error("Auth Error: " . $e->getMessage());
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}
try {
    $input = json_decode(file_get_contents('php://input'), true);
    $logger->info("Incoming Input Data: " . json_encode($input));
    $selected_stores = $input['stores'] ?? [];
    $selected_suppliers = $input['suppliers'] ?? [];
    $tgl_schedule = $input['tgl_schedule'] ?? '';
    if (empty($selected_stores) || empty($selected_suppliers) || empty($tgl_schedule)) {
        throw new Exception("Data tidak lengkap (Cabang, Supplier, atau Tanggal kosong).");
    }
    $date_schedule = new DateTime($tgl_schedule);
    $formatted_schedule = $date_schedule->format('Y-m-d H:i:s');
    $tgl_buat = date('Y-m-d H:i:s');
    $jam_buat = date('H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $nama_komp = "Dari Website";
    $today_dmY = date('dmy');
    $total_inserted = 0;
    $conn->begin_transaction();
    $store_details = [];
    $store_ids_str = implode(',', array_map(function($id) use ($conn) { return "'" . $conn->real_escape_string($id) . "'"; }, $selected_stores));
    $q_store = $conn->query("SELECT Kd_Store, Nm_Store, Nm_Alias FROM kode_store WHERE Kd_Store IN ($store_ids_str)");
    while($r = $q_store->fetch_assoc()) {
        $store_details[$r['Kd_Store']] = $r;
    }
    $supp_ids_str = implode(',', array_map(function($id) use ($conn) { return "'" . $conn->real_escape_string($id) . "'"; }, $selected_suppliers));
    $q_supp = $conn->query("SELECT kd_store, kode_supp, nama_supp FROM supplier WHERE kode_supp IN ($supp_ids_str) AND kd_store IN ($store_ids_str)");
    $valid_supp_map = [];
    while($r = $q_supp->fetch_assoc()) {
        $valid_supp_map[$r['kd_store'] . '_' . $r['kode_supp']] = $r['nama_supp'];
    }
    $sequence_map = []; 
    foreach ($selected_stores as $kd_store) {
        if (!isset($store_details[$kd_store])) continue;
        $store_info = $store_details[$kd_store];
        $no_kor_prefix = $kd_store . $user_kode . $today_dmY;
        if (!isset($sequence_map[$kd_store])) {
            $sql_check = "SELECT no_kor FROM jadwal_so_copy WHERE no_kor LIKE '$no_kor_prefix%' ORDER BY no_kor DESC LIMIT 1";
            $res_check = $conn->query($sql_check);
            if ($res_check && $res_check->num_rows > 0) {
                $last_no = $res_check->fetch_assoc()['no_kor'];
                $last_seq = (int)substr($last_no, -3);
                $sequence_map[$kd_store] = $last_seq;
            } else {
                $sequence_map[$kd_store] = 0;
            }
        }
        foreach ($selected_suppliers as $kode_supp) {
            $nama_supp = '';
            $key_check = $kd_store . '_' . $kode_supp;
            if (isset($valid_supp_map[$key_check])) {
                $nama_supp = $valid_supp_map[$key_check];
            } else {
                $q_fallback = $conn->query("SELECT nama_supp FROM supplier WHERE kode_supp = '$kode_supp' LIMIT 1");
                if ($q_fallback->num_rows > 0) {
                    $nama_supp = $q_fallback->fetch_assoc()['nama_supp'];
                } else {
                    $nama_supp = 'UNKNOWN';
                }
            }
            $sequence_map[$kd_store]++;
            $seq_padded = str_pad($sequence_map[$kd_store], 3, '0', STR_PAD_LEFT);
            $no_kor_final = $no_kor_prefix . $seq_padded;
            $stmt = $conn->prepare("INSERT INTO jadwal_so_copy (
                Kd_Store, Nm_Alias, Nm_Store, 
                kode_supp, nama_supp, 
                kd_otorisasi, nama_otorisasi, 
                Tgl_schedule, no_kor, tgl_kor, 
                tgl_buat, jam, nama_komp, ip_adress, 
                status, sync
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, 'Tunggu', 'False')");
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("sssssssssssss", 
                $kd_store, 
                $store_info['Nm_Alias'], 
                $store_info['Nm_Store'],
                $kode_supp,
                $nama_supp,
                $user_kode, 
                $user_name, 
                $formatted_schedule,
                $no_kor_final,
                $tgl_buat,
                $jam_buat,
                $nama_komp,
                $ip_address
            );
            if (!$stmt->execute()) {
                if ($stmt->errno === 1062) {
                    $stmt->close();
                    throw new Exception("Gagal: Data Duplikat. Jadwal untuk Cabang " . $store_info['Nm_Store'] . " ($kd_store) dan Supplier $nama_supp ($kode_supp) sudah ada.");
                } else {
                    $raw_error = $stmt->error;
                    $stmt->close();
                    throw new Exception("Database Error: " . $raw_error);
                }
            }
            $total_inserted++;
            $stmt->close();
        }
    }
    $conn->commit();
    $logger->info("Success Insert Jadwal SO. Total: " . $total_inserted . " by " . $user_name);
    echo json_encode([
        'success' => true, 
        'message' => "Berhasil membuat $total_inserted jadwal SO.",
        'data' => ['total' => $total_inserted]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    $logger->error("Insert Failed: " . $e->getMessage());
    http_response_code(200); 
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>