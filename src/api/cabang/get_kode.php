<?php

require_once __DIR__ . "./../../../aa_kon_sett.php";
require_once __DIR__ . "./../../auth/middleware_login.php";
header("Content-Type:application/json");

$verif = authenticate_request();


$sqlUserCabang = "SELECT kd_store FROM user_account WHERE kode = ?";
$stmtUserCabang = $conn->prepare($sqlUserCabang);
$stmtUserCabang->bind_param("s", $verif->kode);
$stmtUserCabang->execute();
$resultUserCabang = $stmtUserCabang->get_result();
if ($resultUserCabang->num_rows > 0) {
    $userCabang = $resultUserCabang->fetch_assoc();
    if ($userCabang['kd_store'] == "Pusat") {
        $sql = "SELECT Kd_Store as store, Nm_Alias as nama_cabang FROM kode_store WHERE display = 'on' ORDER BY nm_alias ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    } else {
        $kdStoreArray = explode(',', $userCabang['kd_store']);
        $kdStoreImplode = "'" . implode("','", $kdStoreArray) . "'";
        $sql = "SELECT Kd_Store as store, Nm_Alias as nama_cabang FROM kode_store WHERE Kd_Store IN ($kdStoreImplode) and display = 'on' ORDER BY nm_alias ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        http_response_code(200);
        $data = json_encode(["data" => $result->fetch_all(MYSQLI_ASSOC)]);
        echo $data;
    } else {
        http_response_code(204);
        echo json_encode(["status" => "true", "message" => "Data kosong"]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server bermasalah"]);
}
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $data = json_encode(["data" => $result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(204);
    echo json_encode(["status" => "true", "message" => "Data kosong"]);
}
$stmt->close();
$conn->close();
