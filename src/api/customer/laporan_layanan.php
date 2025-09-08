<?php
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = sintaksQuery("SELECT * FROM contact_us ORDER BY dikirim DESC ");
    $processQueryContact = getContact($conn, $sql);
    echo json_encode(['data' => $processQueryContact]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = $data['kode'];
    $status = $data['status'];
    if (empty($kode) && empty($status)) {
        return;
        exit;
    }
    $validStatus = preg_match('/^[a-za-Z]+$/', $status);
    $validKode = preg_match('/^[0-9]$/', $kode);
    $sql = "UPDATE contact_us SET status = ? WHERE no_hp = ?";
    $params = [$status, $kode];
    $prosessUpdateQuery = updateQuery($conn, $sql, $params);
    echo json_encode(['message' => "Berhasil Kirim Pesan"]);
}


function getContact($conn, $sql)
{
    $query = $sql;
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

function updateQuery($conn, $sql, $params = [])
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    // Tentukan tipe data parameter, misalnya "ss" untuk dua string
    $types = str_repeat("s", count($params)); // asumsikan semua string
    $stmt->bind_param($types, ...$params);

    return $stmt->execute();
}

function sintaksQuery($sql)
{
    $query = $sql;
    return $query;
}

$conn->close();
