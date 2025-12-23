<?php
require_once __DIR__ . '/../../../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Nama file dan lokasi penyimpanan
$filename = "asoka-" . date("Ymd") . ".csv";
$filepath = "/var/www/asokababystore.com/file_kalbe/" . $filename;

// Inisialisasi header kolom Excel
$content = "PhoneNo,OutletID,OutletName,ReceiptNo,ReceiptDate,TotalPrice,BarcodeCode,ProductName,Quantity,Price,TotalPrice\n";

// Query
$sql = "
SELECT 
    REPLACE(REPLACE(CONCAT('''',kd_cust), CHAR(13), ''), CHAR(10), '') AS PhoneNo,
    REPLACE(REPLACE(id_outlet, CHAR(13), ''), CHAR(10), '') AS OutletID,
    REPLACE(REPLACE(nama_outlet, CHAR(13), ''), CHAR(10), '') AS OutletName,
    REPLACE(REPLACE(no_bon, CHAR(13), ''), CHAR(10), '') AS ReceiptNo,
    tgl_trans AS ReceiptDate,
    SUM(harga * qty) AS TotalPrice,
    REPLACE(REPLACE(barcode, CHAR(13), ''), CHAR(10), '') AS BarcodeCode,
    REPLACE(REPLACE(descp, CHAR(13), ''), CHAR(10), '') AS ProductName,
    SUM(qty) AS Quantity,
    harga AS Price,
    SUM(harga * qty) AS TotalPrice
FROM trans_b t
LEFT JOIN kalbe_outlet k ON t.kd_store = k.kd_store
WHERE 
    tgl_trans = CURDATE() - INTERVAL 1 DAY
    AND NOT kd_cust IN ('',' ','898989','999999999')
    AND kode_supp IN ('JSM001','MRG002','MRG003','HDC001','JSM002')
    AND t.kd_store IN ('1376','1377','1378','1379','1501','1502','1503','1504','1505','1506','1611','1641','1642','2101','2102','2103','2104','3190','3191')
GROUP BY no_bon, plu
ORDER BY t.kd_store, tgl_trans, no_bon, plu
";

// Eksekusi query
$result = $conn->query($sql);

// Cek error query
if (!$result) {
    die("❌ Query gagal: " . $conn->error);
}

// Tambahkan baris data
while ($row = $result->fetch_assoc()) {
   $line = [
        $row['PhoneNo'],
        $row['OutletID'],
        $row['OutletName'],
        $row['ReceiptNo'],
        $row['ReceiptDate'],
        $row['TotalPrice'],
        $row['BarcodeCode'],
        $row['ProductName'],
        $row['Quantity'],
        $row['Price'],
        $row['TotalPrice']
    ];
    $content .= '"' . implode('","', $line) . '"' . "\n";
}

// Simpan file ke server
// if (file_put_contents($filepath, $content) !== false) {
//     echo date('Y-m-d H:i:s') . "✅ File berhasil disimpan di server: $filepath\n";
// } else {
//     echo date('Y-m-d H:i:s') . "❌ Gagal menyimpan file. Cek izin folder: " . dirname($filepath) . "\n";
// }

// FTP Upload
require __DIR__ . '/../../../vendor/autoload.php';

use phpseclib3\Net\SFTP;

$sftp = new SFTP('sftp-server.chakra.uno', 22);
if (!$sftp->login('asoka', 'x0KdhrYuzpjp1SvsOAsfYkMKOkfV9vLM')) {
    exit(date('Y-m-d H:i:s') . "❌ Login SFTP gagal\n");
}

$localFile = '/var/www/asokababystore.com/file_kalbe/' . $filename;
$remoteFile = $filename;

if ($sftp->put($remoteFile, $content, SFTP::SOURCE_STRING)) {
    echo date('Y-m-d H:i:s') . "✅ Upload ke SFTP berhasil!\n";
} else {
    echo date('Y-m-d H:i:s') . "❌ Upload ke SFTP gagal!\n";
}
$conn->close();
?>
