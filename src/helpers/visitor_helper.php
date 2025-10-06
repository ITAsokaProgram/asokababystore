
<?php

function logVisitor($conn, $userId, $pageName)
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $page = $_SERVER['REQUEST_URI'];

    $stmt = $conn->prepare("
        SELECT id FROM visitors
        WHERE COALESCE(user_id, ip) = COALESCE(?, ?) AND page = ? AND visit_time >= (NOW() - INTERVAL 5 MINUTE)
        LIMIT 1
    ");
    $stmt->bind_param("sss", $userId, $ip, $page);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt = $conn->prepare("
            INSERT INTO visitors (user_id, ip, user_agent, page, page_name) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $userId, $ip, $ua, $page, $pageName);
        $stmt->execute();
    }
}