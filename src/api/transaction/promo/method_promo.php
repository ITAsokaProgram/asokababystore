<?php
include '../../../../aa_kon_sett.php';
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


function runQuery($sql, $conn)
{
    return $conn->prepare($sql);
}

function insertPromo($sql, $conn, $params = [])
{
    $query = $sql;
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    return $stmt->execute();
}

function readPromo($sql, $conn, $params = [])
{
    $query = $sql;
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    return $data;
}

function fetchPromo($sql, $conn)
{
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Query error: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}
function getParamTypes($params)
{
    $types = '';
    foreach ($params as $param) {
        if (is_int($param)) {
            $types .= 'i'; // integer
        } elseif (is_double($param)) {
            $types .= 'd'; // double
        } elseif (is_string($param)) {
            $types .= 's'; // string
        } else {
            $types .= 'b'; // blob
        }
    }
    return $types;
}
function updatePromo($sql, $conn, $params = [])
{
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $types = getParamTypes($params);
        $stmt->bind_param($types, ...$params);
    }
    return $stmt->execute();
}

function updatePromoDetail($sql,$conn,$params = []){
    // Mulai transaksi
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        if (!empty($params)) {
            $types = getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Commit jika berhasil
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback jika gagal
        $conn->rollback();
        error_log("Update promo failed: " . $e->getMessage());
        return false;
    }
}

function readMasterBarang($sql, $conn, $kode)
{
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kode);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function readSupplier($sql, $conn)
{
    $query = $sql;
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    return $data;
}

function readCabang($sql, $conn)
{
    $query = $sql;
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query error: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}
