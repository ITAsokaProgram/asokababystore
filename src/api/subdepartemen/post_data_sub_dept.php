<?php
include '../../../aa_kon_sett.php';


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Mendapatkan data pengguna dari sesi dengan aman
$nama = htmlspecialchars($_SESSION['nama'] ?? '');
$hak = htmlspecialchars($_SESSION['hak'] ?? '');
$safe_name = htmlspecialchars($_SESSION['username'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $queryType = $_POST['query_type'] ?? 'query1';
    $kd_store = filter_input(INPUT_POST, 'kd_store', FILTER_SANITIZE_STRING);
    $subDataDept = filter_input(INPUT_POST, 'subdept', FILTER_SANITIZE_STRING);
    $kode_supp = filter_input(INPUT_POST, 'kode_supp', FILTER_SANITIZE_STRING);
    $allowedOrderColumns = ['Total', 'Qty'];
    $filter = isset($_GET['filter']) && in_array($_GET['filter'], $allowedOrderColumns) ? $_GET['filter'] : 'Qty';
    $startDate = DateTime::createFromFormat('d-m-Y', $_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : null;
    $endDate = DateTime::createFromFormat('d-m-Y', $_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null;

    if (!$startDate || !$endDate) {
        echo json_encode(['status' => 'error', 'message' => 'Format tanggal tidak valid.']);
        exit();
    }

    // $page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT) ?: 1;
    // $limit = $_POST['limit'] ?? '';
    // $offset = max(($page - 1) * $limit, 0);

    try {
        if (!is_array($kd_store)) {
            $kd_store = explode(',', $kd_store);
        }
        $placeholders = implode(',', array_fill(0, count($kd_store), '?'));
        if ($queryType === "query1") {
            // Buat placeholder (?) sesuai jumlah kd_store
            $placeholders = implode(',', array_fill(0, count($kd_store), '?'));

            // **Query untuk mendapatkan data**
            $sql = "SELECT 
    t.kd_store,
    t.subdept, 
    s.nama_subdept, 
    SUM(t.qty) AS Qty, 
    SUM(t.qty * t.hrg_promo) AS Total,
    CONCAT(ROUND((SUM(t.qty) * 100.0 / total_qty.total), 2), '%') AS Percentage,
    ROUND((SUM(t.hrg_promo * t.qty) * 100.0 / total_qty.total_rp), 2) AS persentase_rp
FROM 
    trans t
JOIN 
    subdept s ON t.subdept = s.SubDept
CROSS JOIN (
    SELECT 
        SUM(qty) AS total,
        SUM(hrg_promo * qty) AS total_rp
    FROM trans_b 
    WHERE kd_store IN ($placeholders) 
    AND tgl_trans BETWEEN ? AND ?
) total_qty
WHERE 
    t.kd_store IN ($placeholders)
    AND t.tgl_trans BETWEEN ? AND ?
GROUP BY t.subdept ORDER BY $filter DESC  ";

            $stmt = $conn->prepare($sql);

            // Gabungkan parameter (kd_store harus dipecah satu per satu)
            $params = array_merge(
                $kd_store,
                [$startDate, $endDate],
                $kd_store,
                [$startDate, $endDate]
            );

            // **Tentukan tipe parameter**
            $paramTypes =
                str_repeat('s', count($kd_store)) .  // kd_store subquery
                "ss" .                               // startDate, endDate subquery
                str_repeat('s', count($kd_store)) .  // kd_store main
                "ss";
            // Bind parameter dengan cara yang benar
            $stmt->bind_param($paramTypes, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $labels = [];
            $data = [];
            $tableData = [];
            while ($row = $result->fetch_assoc()) {
                $labels[] = htmlspecialchars($row['nama_subdept'] ?? '');
                $data[] = [$row['subdept'], $row['Qty'], $row['Total']];
                $tableData[] = $row;
            }

            // **Query untuk menghitung total data**
            $countSql = "SELECT COUNT(DISTINCT subdept) AS total FROM trans_b WHERE kd_store IN ($placeholders) AND tgl_trans BETWEEN ? AND ?";
            $countStmt = $conn->prepare($countSql);

            $countParams = array_merge($kd_store, [$startDate, $endDate]);
            $countParamTypes = str_repeat('s', count($kd_store)) . "ss";

            $countStmt->bind_param($countParamTypes, ...$countParams);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $rowCount = $countResult->fetch_assoc()['total'];
            // $totalPages = ceil($rowCount / $limit);
        }

        if ($queryType === "query2") {
            if (!is_array($kd_store)) {
                $kd_store = explode(',', $kd_store);
            }
            $placeholders = implode(',', array_fill(0, count($kd_store), '?'));
            // **Query untuk mendapatkan data berdasarkan supplier**
            $sql = "SELECT t.subdept,t.kode_supp, s.nama_supp, SUM(t.qty) AS Qty, SUM(qty * hrg_promo) AS Total,
            CONCAT(ROUND((SUM(qty) * 100.0 / (SELECT SUM(qty) FROM trans_b WHERE kd_store IN ($placeholders) AND subdept = ? AND tgl_trans BETWEEN ? AND ?)), 2), '%') AS Percentage,
                    (SUM(hrg_promo * qty) * 100.0 / (
                    SELECT SUM(hrg_promo * qty)
                    FROM trans_b         
                    WHERE subdept = ?
                    AND tgl_trans BETWEEN ? AND ?
                    AND kd_store IN ($placeholders)
                )) AS persentase_rp
                    FROM trans_b t
                    LEFT JOIN (SELECT kode_supp,nama_supp FROM supplier GROUP BY kode_supp) s ON t.kode_supp = s.kode_supp
                    WHERE t.kd_store IN ($placeholders) AND t.tgl_trans BETWEEN ? AND ?
                    AND t.subdept = ?
                    GROUP BY t.kode_supp
                    ORDER BY $filter DESC";
            $params = array_merge(
                $kd_store,
                [$subDataDept, $startDate, $endDate],
                [$subDataDept, $startDate, $endDate],
                $kd_store,
                $kd_store,
                [$startDate, $endDate, $subDataDept]
            );
            // **Tentukan tipe parameter**
            $paramTypes =
                str_repeat('s', count($kd_store)) .
                'sss' .
                'sss' .
                str_repeat('s', count($kd_store)) .
                str_repeat('s', count($kd_store)) .
                'sss';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($paramTypes, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $labels = [];
            $data = [];
            $tableData = [];
            while ($row = $result->fetch_assoc()) {
                $labels[] = htmlspecialchars($row['nama_supp'] ?? '');
                $data[] = [$row['subdept'], $row['Qty'], $row['Total']];
                $tableData[] = $row;
            }
            // **Query untuk menghitung total data*
            $countSql = "SELECT COUNT(DISTINCT kode_supp) AS total FROM trans_b WHERE kd_store IN ($placeholders) AND tgl_trans BETWEEN ? AND ? AND subdept = ?";
            $countStmt = $conn->prepare($countSql);
            $countParams = array_merge($kd_store, [$startDate, $endDate, $subDataDept]);
            $countParamTypes = str_repeat('s', count($kd_store)) . "sss";
            $countStmt->bind_param($countParamTypes, ...$countParams);
            // **Eksekusi query total data**
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $rowCount = $countResult->fetch_assoc()['total'];
        }

        if ($queryType === 'query3') {
            if (!is_array($kd_store)) {
                $kd_store = explode(',', $kd_store);
            }
            if (empty($kd_store)) {
                die("Error: kd_store tidak boleh kosong.");
            }

            $placeholders = implode(',', array_fill(0, count($kd_store), '?'));

            $sql = "SELECT MAX(
IF(kode_promo='', '', 
    IF(diskon > 0, 
        CONCAT(kode_promo, ' (DISKON : ', diskon, '%)'), 
        CONCAT(kode_promo, ' (POTONGAN HARGA)'))
    )
) AS promo,
CASE 
    WHEN DATEDIFF(?, ?) BETWEEN 0 AND 31 THEN DATE_FORMAT(tgl_trans, '%d-%m')
    WHEN DATEDIFF(?, ?) BETWEEN 32 AND 365 THEN DATE_FORMAT(tgl_trans, '%m-%Y')
    ELSE DATE_FORMAT(tgl_trans, '%Y')
END AS periode,
SUM(qty) AS Qty, 
SUM(qty * hrg_promo) AS Total,
CONCAT(
    ROUND(
        (SUM(qty) * 100.0 / NULLIF(
            (SELECT SUM(qty) FROM trans_b WHERE kd_store IN ($placeholders) AND subdept = ? AND kode_supp = ? AND tgl_trans BETWEEN ? AND ?), 0)
        ), 2
    ), '%'
) AS Percentage,
IFNULL(
    ROUND(
        SUM(hrg_promo * qty) * 100.0 / NULLIF((
            SELECT SUM(hrg_promo * qty)
            FROM trans_b         
            WHERE subdept = ?
            AND tgl_trans BETWEEN ? AND ?
            AND kode_supp = ?
            AND kd_store IN ($placeholders)
        ), 0)
    , 2)
, 0) AS persentase_rp
                    FROM trans_b
                    WHERE kd_store IN ($placeholders)
AND tgl_trans BETWEEN ? AND ?
AND subdept=?
AND kode_supp=?
GROUP BY periode
ORDER BY tgl_trans ASC, $filter ASC";

            $params = array_merge(
                [$endDate, $startDate, $endDate, $startDate], 
                $kd_store,                                    
                [$subDataDept, $kode_supp, $startDate, $endDate],
                [$subDataDept, $startDate, $endDate, $kode_supp],
                $kd_store,  
                $kd_store,                                    
                [$startDate, $endDate, $subDataDept, $kode_supp]
            );

            $paramTypes =
                str_repeat('s', 4) .
                str_repeat('s', count($kd_store)) .
                str_repeat('s', 4) .
                str_repeat('s', 4) .
                str_repeat('s', count($kd_store)) .
                str_repeat('s', count($kd_store)) .
                str_repeat('s', 4);

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error dalam prepare statement: " . $conn->error);
            }
            $stmt->bind_param($paramTypes, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $labels = [];
            $data = [];
            $tableData = [];

            while ($row = $result->fetch_assoc()) {
                $labels[] = htmlspecialchars($row['promo']);
                $data[] = $row['Qty'];
                $tableData[] = $row;
            }

            // **Query untuk menghitung total data**
            $countSql = "SELECT COUNT(DISTINCT kode_supp) AS total FROM trans_b WHERE kd_store IN ($placeholders) AND tgl_trans BETWEEN ? AND ? AND kode_supp = ?";
            $countStmt = $conn->prepare($countSql);
            if (!$countStmt) {
                die("Error dalam prepare statement count: " . $conn->error);
            }

            $countParams = array_merge($kd_store, [$startDate, $endDate, $kode_supp]);
            $countParamTypes = str_repeat('s', count($kd_store)) . "sss";
            $countStmt->bind_param($countParamTypes, ...$countParams);

            // **Eksekusi query total data**
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $rowCount = $countResult->fetch_assoc()['total'];
            // $totalPages = ceil($rowCount / $limit);
        }

        if ($queryType === 'query4') {
            if (!is_array($kd_store)) {
                $kd_store = explode(',', $kd_store);
            }

            if (empty($kd_store)) {
                die("Error: kd_store tidak boleh kosong.");
            }

            $placeholders = implode(',', array_fill(0, count($kd_store), '?'));

            $sql = "SELECT descp AS promo,barcode, SUM(qty) AS Qty, SUM(qty * hrg_promo) AS Total 
            FROM trans_b
            WHERE kd_store IN ($placeholders)
            AND tgl_trans BETWEEN ? AND ?
            AND subdept =?
            AND kode_supp=? 
            GROUP BY plu ORDER BY $filter DESC";

            $params = array_merge($kd_store, [$startDate, $endDate, $subDataDept, $kode_supp]);
            $paramTypes = str_repeat('s', count($kd_store)) . "ssss";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error prepare statement: " . $conn->error);
            }

            $stmt->bind_param($paramTypes, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $labels = [];
            $data = [];
            $tableData = [];

            while ($row = $result->fetch_assoc()) {
                $labels[] = htmlspecialchars($row['promo']);
                $data[] = $row['Qty'];
                $tableData[] = $row;
            }

            // **Query untuk menghitung total data**
            $countSql = "SELECT COUNT(*) AS total_data
                 FROM (
                     SELECT descp
                     FROM trans_b
                     WHERE kd_store IN ($placeholders)
                     AND tgl_trans BETWEEN ? AND ?
                     AND subdept=? 
                     AND kode_supp=? 
                     GROUP BY plu
                 ) AS subquery";

            $countParams = array_merge($kd_store, [$startDate, $endDate, $subDataDept, $kode_supp]);
            $countParamTypes = str_repeat('s', count($kd_store)) . "ssss";

            $countStmt = $conn->prepare($countSql);
            if (!$countStmt) {
                die("Error prepare count query: " . $conn->error);
            }

            $countStmt->bind_param($countParamTypes, ...$countParams);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $rowCount = $countResult->fetch_assoc()['total_data'];
        }


        // **Tutup koneksi**
        $stmt->close();
        $countStmt->close();
        $conn->close();
        // **Kirimkan JSON response**
        echo json_encode([
            'status' => 'success',
            'labels' => $labels,
            'data' => $data,
            'tableData' => $tableData,
            'totalData' => $rowCount
        ]);
    } catch (Exception $e) {
        error_log("❌ Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
    }
    exit;
}
?>