<?php
require_once __DIR__ . '/../../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';

header('Content-Type: application/json');

try {
    // Verifikasi Token (Opsional, jika diperlukan uncomment baris bawah)
    // $decoded = verify_token(get_bearer_token()); 

    // Cek koneksi DB
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Koneksi database gagal");
    }

    // Query ambil cabang
    // Perhatikan besar kecil huruf nama tabel dan kolom (Case Sensitive di Linux)
    $sql = "SELECT Kd_Store, Nm_Store, Nm_Alias FROM kode_store WHERE display = 'on' ORDER BY Kd_Store ASC";
    
    $result = $conn->query($sql);

    // --- PERBAIKAN DISINI: Cek apakah query berhasil ---
    if (!$result) {
        // Jika gagal, lempar error spesifik dari MySQL
        throw new Exception("Query Error: " . $conn->error . " (SQL: $sql)");
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>