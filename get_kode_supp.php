<?php

require_once "aa_kon_sett.php";
header("Content-Type:application/json");
$search = isset($_GET['search']) ? $_GET['search']:'';
$limit = 1000;
$sql = "SELECT kode_supp FROM supplier where kode_supp like ?  GROUP BY kode_supp limit ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%".$search."%";
$stmt->bind_param('ss', $searchTerm,$limit);
$stmt->execute();
$result = $stmt->get_result();
$data = [];


while($row = $result->fetch_assoc()){
    $data[] = $row;
};
$conn->close();
echo json_encode($data);