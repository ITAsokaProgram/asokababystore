<?php
include "../../../aa_kon_sett.php";
include "../../auth/middleware_login.php";
header("Content-Type:application/json");
$data = json_decode(file_get_contents("php://input"), true);
$verify = authenticate_request();
$kode = $data['kode'] ?? ''; 
$limit = 10; 
if (isset($data['limit']) && is_numeric($data['limit'])) {
    $limit = (int) $data['limit'];
}
if (empty($kode)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kode customer dibutuhkan']);
    exit;
}
$sql = "SELECT 
    p.no_faktur,
    p.tanggal,
    p.jam,
    ks.Nm_Store,
    p.belanja,
    p.nama_kasir, 
    r.rating AS rating,
    r.id AS review_id,
    r.komentar as komentar,
    rd.review_id AS detail_review_id,
    rd.status AS detail_status,
    (SELECT COUNT(*) 
     FROM review_conversation rc 
     WHERE rc.review_id = r.id AND rc.sudah_dibaca = 0 AND rc.pengirim_type = 'admin'
    ) as unread_count,
    (SELECT COUNT(*) 
     FROM review_conversation rc 
     WHERE rc.review_id = r.id AND rc.pengirim_type = 'admin'
     LIMIT 1
    ) as has_admin_reply_check
    FROM pembayaran_b AS p
    LEFT JOIN kode_store AS ks ON ks.Kd_Store = p.kd_store
    LEFT JOIN review AS r ON r.no_bon = p.no_faktur
    LEFT JOIN review_detail AS rd on rd.review_id = r.id
    WHERE p.kd_cust = ?
    ORDER BY p.tanggal DESC, p.jam DESC
    LIMIT ?"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $kode, $limit);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$formattedData = array_map(function ($item) {
    return [
        'no_faktur'     => $item['no_faktur'],
        'tanggal'       => $item['tanggal'],
        'jam'           => $item['jam'],
        'Nm_Store'      => $item['Nm_Store'],
        'belanja'       => $item['belanja'],
        'nama_kasir'    => $item['nama_kasir'],
        'rating'        => $item['rating'],
        'review_id'     => $item['review_id'],
        'komentar'      => $item['komentar'],
        'detail_review_id' => $item['detail_review_id'],
        'detail_status' => $item['detail_status'],
        'unread_count'  => (int) $item['unread_count'],
        'conversation_started' => ((int) $item['has_admin_reply_check'] > 0)
    ];
}, $data);
http_response_code(200);
echo json_encode([
    'status' => 'success', 
    'message' => 'Data berhasil fetch', 
    'execution_time' => 'Optimized', 
    'data' => $formattedData
]);
$stmt->close();
$conn->close();
?>