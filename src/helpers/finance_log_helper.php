<?php

/**
 * Mencatat log aktivitas keuangan
 *
 * @param mysqli $conn Koneksi database
 * @param string|int $userId ID User yang melakukan aksi
 * @param string $table Nama table target (misal: ff_pembelian)
 * @param string $refId ID Referensi (misal: No Invoice, ID, atau NSFP)
 * @param string $action Jenis aksi: 'INSERT', 'UPDATE', 'DELETE'
 * @param array|null $oldData Data lama (sebelum diedit). Wajib null jika INSERT.
 * @param array|null $newData Data baru (inputan user). Wajib null jika DELETE.
 */
function write_finance_log($conn, $userId, $table, $refId, $action, $oldData = null, $newData = null)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $jsonOld = $oldData ? json_encode($oldData) : null;
    $jsonNew = $newData ? json_encode($newData) : null;

    $stmt = $conn->prepare("
        INSERT INTO finance_activity_logs 
        (table_name, ref_id, action, user_id, old_data, new_data, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param("ssssssss", $table, $refId, $action, $userId, $jsonOld, $jsonNew, $ip, $ua);
        $stmt->execute();
        $stmt->close();
    }
}
?>