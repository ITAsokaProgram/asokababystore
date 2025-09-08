<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
include "../aa_kon_sett.php"; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {

    // Ambil parameter dari request
    if (empty($_POST)) {
        echo json_encode(["status" => "error", "message" => "Tidak ada data yang diterima"]);
        exit;
    }
    $branch = filter_input(INPUT_POST, 'kd_store', FILTER_SANITIZE_SPECIAL_CHARS);
    $subdept = $_POST['subdept'] ?? '';
    $kd_store = $_POST['cabang'] ?? '';
    $startDate = DateTime::createFromFormat('d-m-Y', $_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : null;
    $endDate = DateTime::createFromFormat('d-m-Y', $_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null;
    $storeCodes = [
        'ABIN' => '1502',
        'ACE' => '1505',
        'ACIB' => '1379',
        'ACIL' => '1504',
        'ACIN' => '1641',
        'ACSA' => '1902',
        'ADET' => '1376',
        'ADMB' => '3190',
        'AHA' => '1506',
        'AHIN' => '2102',
        'ALANG' => '1503',
        'ANGIN' => '2102',
        'APEN' => '1908',
        'APIK' => '3191',
        'APRS' => '1501',
        'ARAW' => '1378',
        'ARUNG' => '1611',
        'ASIH' => '2104',
        'ATIN' => '1642',
        'AWIT' => '1377',
        'AXY' => '2103'
    ];
    $kd_store = $storeCodes[$branch] ?? '1505';
    $response = [
        "subdept" => $subdept,
        "cabang" => $kd_store,
        "start_date" => $startDate,
        "end_date" => $endDate
    ];
    // Validasi input
    if (empty($_POST['subdept']) || empty($_POST['kd_store']) || empty($_POST['start_date']) || empty($_POST['end_date'])) {
        echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
        exit;
    }
    
    

    // Query SQL untuk mendapatkan data supplier berdasarkan subdept
    $sql = "SELECT t.`kode_supp`, s.nama_supp, SUM(t.qty) AS Qty, SUM(t.qty * t.hrg_promo) AS Total
        FROM trans_b t
        LEFT JOIN supplier s ON t.`kode_supp` = s.`kode_supp`
        WHERE t.kd_store = ? 
        AND t.tgl_trans BETWEEN ? AND ?
        AND t.subdept = ?
        GROUP BY t.kode_supp
        ORDER BY Qty DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $kd_store, $start_date, $end_date, $subdept);
    $stmt->execute();
    $result = $stmt->get_result();

    $tableData = [];
    while ($row = $result->fetch_assoc()) {
        $tableData[] = $row;
    }
    error_log("📌 Data POST: " . print_r($_POST, true));
    echo json_encode(["debug" => $_POST]);
}

