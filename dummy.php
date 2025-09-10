<?php
require_once __DIR__ . '/config.php';

// Insert 50 dummy rows with specific distribution:
// - 1..20 : have tanggal_beli only
// - 21..30: have tanggal_beli + tanggal_perbaikan and status 'Service'
// - 31..50: all fields filled (except image_url)

$total = 50;

for ($i = 1; $i <= $total; $i++) {
    $nama_barang = "Barang Dummy " . $i;
    $merk = "Merk" . rand(1, 8);
    $harga_beli = rand(10000, 1000000);
    $nama_toko = "Toko " . rand(1, 12);
    // default nullables
    $tanggal_beli = null;
    $tanggal_ganti = null;
    $mutasi_untuk = null;
    $mutasi_dari = null;
    $kd_store = '1502';
    $tanggal_perbaikan = null;
    $status = 'Baru';
    $image_url = null;
    $tanggal_mutasi = null;
    $tanggal_rusak = null;
    $group_aset = null;

    if ($i <= 20) {
        // only tanggal_beli present
        $tanggal_beli = date('Y-m-d H:i:s', strtotime("-" . rand(1, 365) . " days"));
        $status = (rand(0, 1) ? 'Baru' : 'Bekas');
        $mutasi_untuk = "Dept " . rand(1, 3);
        $mutasi_dari = "Dept " . rand(1, 3);
    } elseif ($i <= 30) {
        // tanggal_beli + tanggal_perbaikan, status Service
        $tanggal_beli = date('Y-m-d H:i:s', strtotime("-" . rand(30, 365) . " days"));
        $tanggal_perbaikan = date('Y-m-d H:i:s', strtotime("-" . rand(1, 29) . " days"));
        $status = 'Service';
        $mutasi_untuk = "Dept " . rand(1, 3);
        $mutasi_dari = "Dept " . rand(1, 3);
    } else {
        // fill everything except image
        $tanggal_beli = date('Y-m-d H:i:s', strtotime("-" . rand(10, 800) . " days"));
        $tanggal_ganti = (rand(0, 1) ? date('Y-m-d H:i:s', strtotime($tanggal_beli . " +" . rand(30, 400) . " days")) : null);
        $mutasi_untuk = "Dept " . rand(1, 6);
        $mutasi_dari = "Dept " . rand(1, 6);
        $tanggal_perbaikan = (rand(0, 1) ? date('Y-m-d H:i:s', strtotime($tanggal_beli . " +" . rand(5, 200) . " days")) : null);
        $status = ['Baru','Bekas','Service'][array_rand(['Baru','Bekas','Service'])];
        $tanggal_mutasi = date('Y-m-d H:i:s', strtotime($tanggal_beli . " +" . rand(1, 500) . " days"));
        $tanggal_rusak = (rand(0, 1) ? date('Y-m-d H:i:s', strtotime($tanggal_beli . " +" . rand(50, 700) . " days")) : null);
        $group_aset = "Group " . chr(65 + rand(0, 5));
    }

    $sql = "INSERT INTO history_aset (
        nama_barang,
        merk,
        harga_beli,
        nama_toko,
        tanggal_beli,
        tanggal_ganti,
        mutasi_untuk,
        mutasi_dari,
        kd_store,
        tanggal_perbaikan,
        status,
        image_url,
        tanggal_mutasi,
        tanggal_rusak,
        group_aset
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed at row $i: (" . $conn->errno . ") " . $conn->error . "\n";
        continue;
    }

    // bind all as strings; NULL values will be sent as NULL
    $stmt->bind_param(
        'sssssssssssssss',
        $nama_barang,
        $merk,
        $harga_beli,
        $nama_toko,
        $tanggal_beli,
        $tanggal_ganti,
        $mutasi_untuk,
        $mutasi_dari,
        $kd_store,
        $tanggal_perbaikan,
        $status,
        $image_url,
        $tanggal_mutasi,
        $tanggal_rusak,
        $group_aset
    );

    if (!$stmt->execute()) {
        echo "Error insert dummy ke-$i: " . $stmt->error . "\n";
    } else {
        echo "Insert dummy ke-$i sukses (ID: " . $stmt->insert_id . ")\n";
    }

    $stmt->close();
}

$conn->close();
?>
