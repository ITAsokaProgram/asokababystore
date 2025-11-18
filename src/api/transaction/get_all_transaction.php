<?php
include '../../../aa_kon_sett.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fungsi untuk membersihkan karakter non-UTF-8
function clean_utf8_recursive($input)
{
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = clean_utf8_recursive($value);
        }
        return $input;
    } elseif (is_string($input)) {
        // Gunakan iconv sebagai pengganti mb_convert_encoding
        $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $input);
        return $converted === false ? '' : $converted;
    }
    return $input;
}

// Ambil dan decode input JSON
$input = json_decode(file_get_contents('php://input'), true);

$startDateObj = DateTime::createFromFormat('d-m-Y', $input['start_date']);
$endDateObj = DateTime::createFromFormat('d-m-Y', $input['end_date']);
$cabang = $input['cabang'];

$startDate = $startDateObj ? $startDateObj->format('Y-m-d') : null;
$endDate = $endDateObj ? $endDateObj->format('Y-m-d') : null;

if (!is_array($cabang)) {
    $cabang = explode(',', $cabang);
}
$placeholders = implode(',', array_fill(0, count($cabang), '?'));
// Hitung rentang hari
$date1 = new DateTime($startDate);
$date2 = new DateTime($endDate);
$interval = $date1->diff($date2);
$rangeInDays = $interval->days;

// Tentukan format dan GROUP BY berdasarkan range
if ($rangeInDays <= 31) {
    $periodeFormat = "%d-%m";
    $groupByClause = "DATE_FORMAT(tgl_trans, '%d-%m')";
} elseif ($rangeInDays <= 365) {
    $periodeFormat = "%m-%Y";
    $groupByClause = "DATE_FORMAT(tgl_trans, '%m-%Y')";
} else {
    $periodeFormat = "%Y";
    $groupByClause = "YEAR(tgl_trans)";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sqlTrans = "SELECT 
    DATE_FORMAT(tgl_trans, '$periodeFormat') AS periode,
    COUNT(DISTINCT no_bon) AS total_transaksi,
    COUNT(DISTINCT CASE WHEN kd_cust NOT IN ('', '89898989', '999999999') AND kd_cust IS NOT NULL 
                   THEN no_bon END) AS transaksi_member,
    COUNT(DISTINCT CASE WHEN COALESCE(kd_cust, '') IN ('', '89898989', '999999999') 
                   THEN no_bon END) AS transaksi_non_member,
    SUM(CASE WHEN hrg_promo < harga THEN qty ELSE 0 END) AS total_barang_terjual_promo,
    SUM(CASE WHEN hrg_promo >= harga THEN qty ELSE 0 END) AS total_barang_terjual_non_promo,
    ROUND(100.0 * COUNT(DISTINCT CASE WHEN kd_cust NOT IN ('', '89898989', '999999999') AND kd_cust IS NOT NULL 
                                 THEN no_bon END) / 
          NULLIF(COUNT(DISTINCT no_bon), 0), 2) AS persentase_member,
    ROUND(100.0 * COUNT(DISTINCT CASE WHEN COALESCE(kd_cust, '') IN ('', '89898989', '999999999') 
                                 THEN no_bon END) / 
          NULLIF(COUNT(DISTINCT no_bon), 0), 2) AS persentase_non_member
FROM trans_b
WHERE tgl_trans BETWEEN ? AND ?
  AND kd_store IN ($placeholders)
GROUP BY $groupByClause
ORDER BY tgl_trans";

        $paramsTrans = array_merge([$startDate, $endDate], $cabang);
        $paramTypes = str_repeat('s', count($paramsTrans));
        $stmt1 = $conn->prepare($sqlTrans);
        if (!$stmt1) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt1->bind_param($paramTypes, ...$paramsTrans);
        if (!$stmt1->execute()) {
            throw new Exception("Execute failed: " . $stmt1->error);
        }
        $dataTransaksi = $stmt1->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt1->close();

        // QUERY BARANG
        $sqlBarang = "SELECT 
                        DATE(tgl_trans) AS tanggal,
                        barcode,
                        descp,
                        SUM(qty) AS total_terjual,
                        MAX(harga) AS harga,
                        MAX(hrg_promo) AS hrg_promo
                    FROM trans_b
                    WHERE tgl_trans BETWEEN ? AND ?
                      AND kd_store IN ($placeholders)
                    GROUP BY DATE(tgl_trans), barcode, descp
                    ORDER BY tanggal, total_terjual DESC LIMIT 100";

        $paramsBarang = array_merge([$startDate, $endDate], $cabang);
        $stmt2 = $conn->prepare($sqlBarang);
        if (!$stmt2) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt2->bind_param($paramTypes, ...$paramsBarang);
        if (!$stmt2->execute()) {
            throw new Exception("Execute failed: " . $stmt2->error);
        }
        $dataBarang = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();

        // Bersihkan data agar bisa di-encode JSON
        $cleanTransaksi = clean_utf8_recursive($dataTransaksi);
        $cleanBarang = clean_utf8_recursive($dataBarang);

        echo json_encode([
            'status' => 'success',
            'data' => $cleanTransaksi,
            'barang' => $cleanBarang
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'JSON Encoding Error: ' . $e->getMessage()
        ]);
    } finally {
        $conn->close();
    }
}
?>