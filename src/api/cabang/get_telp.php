<?php

require_once __DIR__ . "./../../../aa_kon_sett.php";

header("Content-Type:application/json");


$sql = "SELECT telp FROM kode_store";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $data = json_encode(["data" => $result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(204);
    echo json_encode(["status"=> "true","message"=> "Data kosong"]);
}
$stmt->close();
$conn->close();
