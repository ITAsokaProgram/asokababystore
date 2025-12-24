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

        // Convert kd_store to array if it's not already
        if (!is_array($kd_store)) {
            $kd_store = explode(',', $kd_store);
        }

        // Validate required data
        if (empty($kd_store) || !$startDate || !$endDate) {
            throw new Exception('Missing required parameters: kd_store, start_date, or end_date');
        }

        // Execute query based on conditions
        if (!empty($query)) {
            // Fetch data based on category (allCate or BABY/DST/SPM)
            $resultData = fetchData($conn, $startDate, $endDate, $kd_store, $query, $filter);
            echo json_encode([
                'status' => 'success',
                'data' => $resultData,
            ]);
        } elseif (!empty($kode_supp) && !empty($kategori)) {
            // Fetch supplier detail data
            $resultData = fetchSupplierDetail($conn, $startDate, $endDate, $kd_store, $kode_supp, $kategori, $filter);
            $resultForTableSupplierChart = fetchBarangSupplier($conn, $kd_store, $kategori, $startDate, $endDate, $kode_supp, $filter);
            echo json_encode([
                'status' => 'success',
                'data' => $resultData,
                'supplierTable' => $resultForTableSupplierChart
            ]);
        } else {
            // Return empty data
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
            // PERBAIKAN DI SINI:
            // Subquery Supplier diperbaiki agar kompatibel dengan Multi-Store & Group By Strict Mode
            // Kita menghapus 'kd_store' dan 'type_kategori' dari SELECT subquery supplier
            // dan menggunakan MAX(nama_supp)

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

            // Urutan parameter harus sama persis dengan urutan tanda tanya (?) di SQL
            $params = array_merge(
                [$kategori, $startDate, $endDate], // Param Subquery 1 (Persentase Qty)
                $kd_store,                         // Store Subquery 1
                [$kategori, $startDate, $endDate], // Param Subquery 2 (Persentase Rp)
                $kd_store,                         // Store Subquery 2
                $kd_store,                         // Param Subquery LEFT JOIN Supplier
                [$kategori, $startDate, $endDate], // Param Main Query WHERE
                $kd_store                          // Store Main Query
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

function fetchSupplierDetail($conn, $startDate, $endDate, $kd_store, $kode_supp, $kategori, $filter)
{
    try {
        $placeholders = implode(',', array_fill(0, count($kd_store), '?'));

        $sql = "SELECT 
            t.barcode, 
            t.descp, 
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
            CASE 
                WHEN DATEDIFF(?, ?) BETWEEN 0 AND 31 THEN DATE_FORMAT(t.tgl_trans, '%d-%m')
                WHEN DATEDIFF(?, ?) BETWEEN 32 AND 365 THEN DATE_FORMAT(t.tgl_trans, '%m-%Y')
                ELSE DATE_FORMAT(t.tgl_trans, '%Y')
            END AS periode
        FROM trans_b t
        WHERE t.type_kategori = ?
        AND t.kode_supp = ?
        AND t.tgl_trans BETWEEN ? AND ?
        AND t.kd_store IN ($placeholders)
        GROUP BY periode, t.barcode, t.descp, t.type_kategori
        ORDER BY t.tgl_trans ASC, $filter ASC";

        // Perbaikan: Parameter binding harus hati-hati dengan urutan
        $params = array_merge(
            [$kategori, $startDate, $endDate], // Subquery 1
            $kd_store,                         // Store Subquery 1
            [$kode_supp],                      // Kode Supp Subquery 1
            [$kategori, $startDate, $endDate], // Subquery 2
            $kd_store,                         // Store Subquery 2
            [$kode_supp],                      // Kode Supp Subquery 2
            [$endDate, $startDate, $endDate, $startDate], // DATEDIFF params
            [$kategori, $kode_supp, $startDate, $endDate], // Main WHERE
            $kd_store // Main Store
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