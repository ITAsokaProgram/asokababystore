<?php
include '../../../../aa_kon_sett.php';
include 'method_promo.php';
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$sql = "SELECT DISTINCT nama_supp, kode_supp FROM supplier";

try{
    $fetch = readSupplier($sql,$conn);
    if($fetch){
        http_response_code(200);
        echo json_encode(['message'=> 'Berhasil fetch data', 'data_supplier'=>$fetch]);
    } else {
        http_response_code(404);
        echo json_encode(['message'=> "Data tidak ditemukan"]);
    }
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(['message'=> "Server Error Atau Mengalami Kendala" , "error"=>$e->getMessage()]);
}
$conn->close();
