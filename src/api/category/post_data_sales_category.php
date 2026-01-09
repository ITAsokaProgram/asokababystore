<?php
require_once "../../../aa_kon_sett.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$input = json_decode(file_get_contents('php://input'), true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $kategori = $input['kategori'] ?? '';
        $kode_supp = $input['kode_supp'] ?? '';
        $kd_store = $input['kd_store'];
        $startDateObj = DateTime::createFromFormat('d-m-Y', $input['start_date']);
        $endDateObj = DateTime::createFromFormat('d-m-Y', $input['end_date']);
        $allowedOrderColumns = ['total', 'total_qty'];
        $filter = isset($_GET['filter']) && in_array($_GET['filter'], $allowedOrderColumns) ? $_GET['filter'] : 'total';
        $startDate = $startDateObj ? $startDateObj->format('Y-m-d') : null;
        $endDate = $endDateObj ? $endDateObj->format('Y-m-d') : null;
        $query = $input['query'] ?? '';
        if (!is_array($kd_store)) {
            $kd_store = explode(',', $kd_store);
        }
        if (empty($kd_store) || !$startDate || !$endDate) {
            throw new Exception('Missing required parameters: kd_store, start_date, or end_date');
        }
        if (!empty($query)) {
            $resultData = fetchData($conn, $startDate, $endDate, $kd_store, $query, $filter);
            echo json_encode([
                'status' => 'success',
                'data' => $resultData,
            ]);
        } elseif (!empty($kode_supp) && !empty($kategori)) {
            $diffDays = 0;
            if ($startDateObj && $endDateObj) {
                $interval = $startDateObj->diff($endDateObj);
                $diffDays = $interval->days;
            }
            $resultData = fetchSupplierDetail($conn, $startDate, $endDate, $kd_store, $kode_supp, $kategori, $filter, $diffDays);
            $resultForTableSupplierChart = fetchBarangSupplier($conn, $kd_store, $kategori, $startDate, $endDate, $kode_supp, $filter);
            echo json_encode([
                'status' => 'success',
                'data' => $resultData,
                'supplierTable' => $resultForTableSupplierChart
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'data' => [],
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}
function fetchData($conn, $startDate, $endDate, $kd_store, $kategori, $filter)
{
    try {
        $placeholders = implode(',', array_fill(0, count($kd_store), '?'));
        if ($kategori === 'allCate') {
            $sql = "SELECT
                type_kategori,
                SUM(qty * hrg_promo) AS total,
                SUM(qty) AS total_qty,
                (SUM(qty) * 100.0 / (
                    SELECT SUM(qty)
                    FROM trans_b
                    WHERE type_kategori IN ('BABY', 'DST', 'SPM')
                    AND tgl_trans BETWEEN ? AND ?
                    AND kd_store IN ($placeholders)
                )) AS persentase,
                (SUM(hrg_promo * qty) * 100.0 / (
                    SELECT SUM(hrg_promo * qty)
                    FROM trans_b           
                    WHERE type_kategori IN ('BABY', 'DST', 'SPM')
                    AND tgl_trans BETWEEN ? AND ?
                    AND kd_store IN ($placeholders)
                )) AS persentase_rp
            FROM trans_b
            WHERE type_kategori IN ('BABY', 'DST', 'SPM')
            AND tgl_trans BETWEEN ? AND ?
            AND kd_store IN ($placeholders)
            GROUP BY type_kategori";
            $params = array_merge(
                [$startDate, $endDate],
                $kd_store,
                [$startDate, $endDate],
                $kd_store,
                [$startDate, $endDate],
                $kd_store
            );
        } else {
            $sql = "SELECT 
                s.kode_supp,
                s.nama_supp,
                t.type_kategori,
                SUM(t.qty) AS total_qty,
                SUM(t.qty * t.hrg_promo) AS total,
                (SUM(t.qty) * 100.0 / (
                    SELECT SUM(qty)
                    FROM trans_b
                    WHERE type_kategori = ?
                    AND tgl_trans BETWEEN ? AND ? 
                    AND kd_store IN ($placeholders)
                )) AS persentase,
                (SUM(t.hrg_promo * t.qty) * 100.0 / (
                    SELECT SUM(hrg_promo * qty)
                    FROM trans_b           
                    WHERE type_kategori = ?
                    AND tgl_trans BETWEEN ? AND ?
                    AND kd_store IN ($placeholders)
                )) AS persentase_rp
            FROM trans_b t
            LEFT JOIN (
                SELECT kode_supp, MAX(nama_supp) as nama_supp 
                FROM supplier 
                WHERE kd_store IN ($placeholders) 
                GROUP BY kode_supp
            ) AS s ON t.kode_supp = s.kode_supp
            WHERE t.type_kategori = ?
            AND t.tgl_trans BETWEEN ? AND ? 
            AND t.kd_store IN ($placeholders)
            GROUP BY s.kode_supp, s.nama_supp, t.type_kategori 
            ORDER BY $filter DESC";
            $params = array_merge(
                [$kategori, $startDate, $endDate],
                $kd_store,
                [$kategori, $startDate, $endDate],
                $kd_store,
                $kd_store,
                [$kategori, $startDate, $endDate],
                $kd_store
            );
        }
        $paramTypes = str_repeat('s', count($params));
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed in fetchData: " . $conn->error);
        }
        $stmt->bind_param($paramTypes, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed in fetchData: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    } catch (Exception $e) {
        if (isset($stmt) && $stmt !== false) {
            $stmt->close();
        }
        throw $e;
    }
}
function fetchSupplierDetail($conn, $startDate, $endDate, $kd_store, $kode_supp, $kategori, $filter, $diffDays)
{
    try {
        $placeholders = implode(',', array_fill(0, count($kd_store), '?'));
        $dateFormatSQL = ($diffDays > 31) ? '%m-%Y' : '%d-%m-%Y';
        $sql = "SELECT 
            MAX(t.barcode) as barcode, 
            MAX(t.descp) as descp, -- Ambil nama sembarang, atau 'Total Penjualan' agar di chart muncul labelnya
            SUM(t.qty) AS total_qty,
            t.type_kategori as kategori,
            SUM(t.hrg_promo * t.qty) AS Total,
            (SUM(t.qty) * 100.0 / (
                SELECT SUM(qty)
                FROM trans_b
                WHERE type_kategori = ?
                AND tgl_trans BETWEEN ? AND ?
                AND kd_store IN ($placeholders)
                AND kode_supp = ?
            )) AS persentase,
            (SUM(t.hrg_promo * t.qty) * 100.0 / (
                SELECT SUM(hrg_promo * qty)
                FROM trans_b           
                WHERE type_kategori = ?
                AND tgl_trans BETWEEN ? AND ?
                AND kd_store IN ($placeholders) 
                AND kode_supp = ?
            )) AS persentase_rp,
            DATE_FORMAT(t.tgl_trans, ?) AS periode
        FROM trans_b t
        WHERE t.type_kategori = ?
        AND t.kode_supp = ?
        AND t.tgl_trans BETWEEN ? AND ?
        AND t.kd_store IN ($placeholders)
        GROUP BY periode, t.type_kategori
        ORDER BY t.tgl_trans ASC";
        $params = array_merge(
            [$kategori, $startDate, $endDate],
            $kd_store,
            [$kode_supp],
            [$kategori, $startDate, $endDate],
            $kd_store,
            [$kode_supp],
            [$dateFormatSQL],
            [$kategori, $kode_supp, $startDate, $endDate],
            $kd_store
        );
        $paramTypes = str_repeat('s', count($params));
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed in fetchSupplierDetail: " . $conn->error);
        }
        $stmt->bind_param($paramTypes, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed in fetchSupplierDetail: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    } catch (Exception $e) {
        if (isset($stmt) && $stmt !== false) {
            $stmt->close();
        }
        throw $e;
    }
}
function fetchBarangSupplier($conn, $kd_store, $kategori, $startDate, $endDate, $kode_supp, $filter)
{
    try {
        $placeholders = implode(',', array_fill(0, count($kd_store), '?'));
        $sql = "SELECT 
            t.barcode, 
            t.descp AS nama_barang, 
            t.type_kategori,
            SUM(t.qty) AS total_qty,
            SUM(t.hrg_promo * t.qty) AS total
        FROM trans_b t
        WHERE t.kd_store IN ($placeholders)
        AND t.type_kategori = ?
        AND t.tgl_trans BETWEEN ? AND ?
        AND t.kode_supp = ?
        GROUP BY t.barcode, t.descp, t.type_kategori
        ORDER BY $filter DESC";
        $params = array_merge($kd_store, [$kategori, $startDate, $endDate, $kode_supp]);
        $paramTypes = str_repeat('s', count($params));
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed in fetchBarangSupplier: " . $conn->error);
        }
        $stmt->bind_param($paramTypes, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed in fetchBarangSupplier: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    } catch (Exception $e) {
        if (isset($stmt) && $stmt !== false) {
            $stmt->close();
        }
        throw $e;
    }
}
?>