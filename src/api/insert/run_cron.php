<?php
require_once __DIR__ . '/../../../config.php';
include "insert_data_pembayaran_to_pembayaran_b.php";
include "insert_data_trans_to_trans_b.php";

include __DIR__ . '/../../../cron/update_kategori_trans_b.php';

sleep(30);
include "delete_data_pembayaran.php";
sleep(30);
include "delete_data_trans.php";

$conn->close();

?>