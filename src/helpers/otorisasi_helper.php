<?php

function cekOtorisasi($conn, $kode_user, $password, $tipe_fitur)
{
    $sql = "SELECT kode_user FROM otorisasi_user 
            WHERE kode_user = ? AND password = ? 
            AND (tipe = ? OR tipe = 'all') LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $kode_user, $password, $tipe_fitur);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}