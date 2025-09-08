<?php

include "../../../aa_kon_sett.php";
header("Content-Type:application/json");

$email = $_GET['email'];
$sqlNoHp = "SELECT no_hp FROM user_asoka WHERE email = ?";
$stmt = $conn->prepare($sqlNoHp);
$stmt->bind_param('s',$email);
$stmt->execute();
$result = $stmt->get_result();
$result->fetch_assoc();
$stmt->close();

echo json_encode(['data'=> $result]);

$conn->close();