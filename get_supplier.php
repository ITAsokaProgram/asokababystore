<?php

require_once 'aa_kon_sett.php';

$sql = "SELECT kode_supp from supplier GROUP BY kode_supp";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
$stmt->close();
$conn->close();