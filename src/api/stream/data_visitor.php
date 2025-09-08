<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

ob_implicit_flush(true);
ob_end_flush();

require_once __DIR__ . "/../../../aa_kon_sett.php";

// set default timezone ke WIB
date_default_timezone_set('Asia/Jakarta');

while (true) {
    /** ---------------------------
     * 1. Total Visits (unik hari ini)
     * --------------------------- */
    $sql = "SELECT COUNT(DISTINCT COALESCE(user_id, ip)) as total_visits 
            FROM visitors 
            WHERE DATE(visit_time) = CURDATE()";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $total_visits = (int)($row['total_visits'] ?? 0);

    /** ---------------------------
     * 2. Online Now (aktif 5 menit terakhir)
     * --------------------------- */
    $sql = "SELECT COUNT(DISTINCT COALESCE(user_id, ip)) as online_now 
            FROM visitors 
            WHERE visit_time >= (NOW() - INTERVAL 5 MINUTE)";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $online_now = (int)($row['online_now'] ?? 0);

    /** ---------------------------
     * 3. Rata-rata Online (1 jam terakhir)
     * --------------------------- */
    $sql = "SELECT ROUND(AVG(cnt)) as avg_online
            FROM (
                SELECT COUNT(DISTINCT COALESCE(user_id, ip)) as cnt
                FROM visitors
                WHERE visit_time >= (NOW() - INTERVAL 1 HOUR)
                GROUP BY MINUTE(visit_time)
            ) t";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $avg_online = (int)($row['avg_online'] ?? 0);

    /** ---------------------------
     * 4. Top 7 Pages Hari Ini
     * --------------------------- */
    $sql = "SELECT COALESCE(page_name, 'Halaman Tidak Dikenal') as page_name, COUNT(*) as views
            FROM visitors
            WHERE DATE(visit_time) = CURDATE()
            GROUP BY page_name
            ORDER BY views DESC
            LIMIT 7";
    $res = $conn->query($sql);
    $pages = [];
    while ($row = $res->fetch_assoc()) {
        $pages[] = [
            "page"  => $row['page_name'],
            "views" => (int)$row['views']
        ];
    }

    /** ---------------------------
     * 5. Recent Activity (10 terakhir)
     * --------------------------- */
    $sql = "SELECT 
                COALESCE(v.page_name, 'Halaman Tidak Dikenal') as page_name,
                v.visit_time,
                COALESCE(SUBSTRING_INDEX(u.nama_lengkap, ' ', 1), 'Guest') AS nama
            FROM visitors v
            LEFT JOIN user_asoka u ON v.user_id = u.id_user
            ORDER BY v.visit_time DESC";
    $res = $conn->query($sql);
    $activities = [];
    while ($row = $res->fetch_assoc()) {
        // konversi waktu DB → WIB
        $dt = new DateTime($row['visit_time'], new DateTimeZone('UTC')); 
        $dt->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $time = $dt->format('H:i:s'); // hanya jam WIB

        $activities[] = [
            "nama" => $row['nama'],
            "page" => $row['page_name'],
            "time" => $time
        ];
    }

    /** ---------------------------
     * 6. Kirim JSON via SSE
     * --------------------------- */
    $payload = [
        "today"      => $total_visits,
        "online"     => $online_now,
        "avg"        => $avg_online,
        "pages"      => $pages,
        "activities" => $activities
    ];

    echo "data: " . json_encode($payload) . "\n\n";
    flush();

    sleep(5); // update tiap 5 detik
}
