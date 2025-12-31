<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

$term = $_GET['term'] ?? '';
$kd_store = $_GET['kd_store'] ?? ''; // Ambil kd_store dari parameter GET

if (strlen($term) < 1) {
    echo json_encode([]);
    exit;
}

try {
    $term = "%" . $conn->real_escape_string($term) . "%";

    // Base Query
    // PENTING: Gunakan tanda kurung ( ) pada OR agar logika AND kd_store bekerja benar
    $sql = "SELECT DISTINCT kode_supp, nama_supp 
            FROM supplier 
            WHERE (kode_supp LIKE ? OR nama_supp LIKE ?)";

    // Jika kd_store dikirim dan tidak kosong, tambahkan filter
    if (!empty($kd_store)) {
        $sql .= " AND kd_store = ?";
    }

    $sql .= " LIMIT 20";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL Prepare Failed: " . $conn->error);
    }

    // Bind param dinamis sesuai kondisi
    if (!empty($kd_store)) {
        // sss -> term, term, kd_store
        $stmt->bind_param("sss", $term, $term, $kd_store);
    } else {
        // ss -> term, term
        $stmt->bind_param("ss", $term, $term);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['kode_supp'],
            'text' => $row['kode_supp'] . " - " . $row['nama_supp'],
            'nama' => $row['nama_supp']
        ];
    }

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>