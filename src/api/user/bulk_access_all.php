<?php
header("Content-Type: application/json");
include '../../../aa_kon_sett.php';
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
try {
    $conn->begin_transaction();
    $sqlMenu = "SELECT menu_code, endpoint_url FROM menu_website WHERE menu_code != 'dashboard_sales_graph'";
    $resultMenu = $conn->query($sqlMenu);
    $menus = [];
    if ($resultMenu->num_rows > 0) {
        while ($row = $resultMenu->fetch_assoc()) {
            $menus[] = $row;
        }
    }
    $sqlUser = "SELECT kode FROM user_account WHERE aktif = 'True'";
    $resultUser = $conn->query($sqlUser);
    $users = [];
    if ($resultUser->num_rows > 0) {
        while ($row = $resultUser->fetch_assoc()) {
            $users[] = $row['kode'];
        }
    }
    if (empty($menus) || empty($users)) {
        throw new Exception("Data menu atau user aktif tidak ditemukan.");
    }
    $query = "INSERT IGNORE INTO user_internal_access (id_user, menu_code, endpoint_url, can_view, can_edit, can_delete) VALUES (?, ?, ?, 1, 1, 0)";
    $stmt = $conn->prepare($query);
    $affectedRows = 0;
    foreach ($users as $userId) {
        foreach ($menus as $menu) {
            $stmt->bind_param("sss", $userId, $menu['menu_code'], $menu['endpoint_url']);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $affectedRows++;
            }
        }
    }
    $conn->commit();
    echo json_encode([
        "status" => "success",
        "message" => "Berhasil memberikan akses massal.",
        "details" => "Update akses ke " . count($users) . " user. Total record baru: " . $affectedRows
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>