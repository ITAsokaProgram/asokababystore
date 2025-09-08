<?php
include '../../aa_kon_sett.php';
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$range = $_GET['range'] ?? 'month';
$tanggal = $_GET['tanggal'] ?? null;
$startInput = $_GET['start'] ?? null;
$endInput   = $_GET['end'] ?? null;
function rangeParameter($rangeFilter, $date, $startInput = null, $endInput = null)
{
    // Jika ada input tanggal manual dari UI (format: dd/mm/yyyy)
    if ($startInput && $endInput) {
        // Convert ke format Y-m-d
        $start = DateTime::createFromFormat('d/m/Y', $startInput)->format('Y-m-d');
        $end = DateTime::createFromFormat('d/m/Y', $endInput)->format('Y-m-d');
        return [$start, $end];
    }

    // Filter berbasis range otomatis
    $isValidDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
    $end = date('Y-m-d');
    if ($isValidDate) {
        $end = $date;
    }

    switch ($rangeFilter) {
        case 'day':
            $start = $end;
            break;
        case 'week':
            $start = date('Y-m-d', strtotime($end . ' -6 days'));
            break;
        case 'month':
        default:
            $start = date('Y-m-d', strtotime($end . ' -30 days'));
            break;
    }

    return [$start, $end];
}
list($startDate, $endDate) = rangeParameter($range, $tanggal, $startInput, $endInput);
function forQuery($query, $conn, $params = [])
{
    $sql = $conn->prepare($query);
    if (!$sql) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters jika ada
    if (!empty($params)) {
        // Buat format string (semua dianggap string: 's', atau bisa disesuaikan)
        $types = str_repeat('s', count($params));
        $sql->bind_param($types, ...$params);
    }

    $sql->execute();
    $result = $sql->get_result();

    if (!$result) {
        die("Execution failed: " . $conn->error);
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);
    $sql->close();
    return $data;
}

$sql = " SELECT t.no_bon, DATE_FORMAT(t.tgl_trans,'%d-%m-%Y') AS tanggal, t.descp, t.kd_store,t.diskon,t.kode_promo
FROM trans_b t
LEFT JOIN t_trans tr ON t.no_bon = tr.no_bon
WHERE DATE(t.tgl_trans) BETWEEN ? AND ? GROUP BY t.no_bon";

$data = forQuery($sql, $conn, [$startDate, $endDate]);
echo json_encode(['data' => $data]);
$conn->close();
