<?php

function log_nota($conn, $user, $action, $faktur, $old, $new) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $old_json = $old ? json_encode($old) : null;
    $new_json = $new ? json_encode($new) : null;
    
    $stmt = $conn->prepare("INSERT INTO serah_terima_nota_logs (user_id, action, no_faktur, old_data, new_data, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $user, $action, $faktur, $old_json, $new_json, $ip, $ua);
    $stmt->execute();
    $stmt->close();
}


?>