<?php
include '../../../../aa_kon_sett.php';
include 'method_promo.php';
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
try{
    $data = json_decode(file_get_contents("php://input"),true);
    $status = $data['status'];
    $idPromo = $data['id_promo'];
    $ket = $data['keterangan'];
    $sql = "UPDATE master_promo SET status = ?, keterangan = ? WHERE id_promo = ?";
    $params = [$status, $ket, $idPromo];
    $fetch = updatePromo($sql,$conn,$params);
    if($fetch){
        http_response_code(200);
        echo json_encode(['message'=>'Berhasil mengubah data']);
    } else {
        http_response_code(400);
        echo json_encode(['message'=>'Gagal fetch data, no response']);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['message'=>'Server error please reconnect']);
}