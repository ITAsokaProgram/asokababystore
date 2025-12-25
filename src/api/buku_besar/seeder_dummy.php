<?php
// src/api/buku_besar/seeder_dummy.php

ini_set('display_errors', 1);
ini_set('max_execution_time', 300); // Tambah waktu jaga-jaga
header('Content-Type: application/json');

// Sesuaikan path ini dengan struktur folder Anda
require_once __DIR__ . '/../../../aa_kon_sett.php';

// Data Dummy Helper untuk variasi
$suppliers = [
    ['code' => 'A18001', 'name' => 'PRATAMA GLOBAL SENTOSA'],
    ['code' => 'S00021', 'name' => 'SUMBER MAKMUR JAYA'],
    ['code' => 'B99212', 'name' => 'CV. ABADI SENTOSA'],
    ['code' => 'K11201', 'name' => 'TOKO KITA BERSAMA'],
    ['code' => 'Z88221', 'name' => 'DISTRIBUTOR UTAMA']
];

$stores = ['1642', '1001', '2020'];
$kets = ['Lunas', 'Cicilan', 'Pending', 'Tempo'];
$kd_user_dummy = '9990024'; // Menggunakan ID user dari contoh data Anda

$successCount = 0;
$failCount = 0;
$errors = [];

// Siapkan Query (Sama persis dengan struktur INSERT di file save Anda)
$query = "INSERT INTO buku_besar 
    (tgl_nota, no_faktur, kode_supplier, nama_supplier, 
    potongan, ket_potongan, total_bayar, tanggal_bayar, 
    kode_store, ket, kd_user)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);

// Loop 100 kali
for ($i = 1; $i <= 100; $i++) {

    // 1. Generate No Faktur Unik
    // Format: TEST-[TIMESTAMP]-[URUTAN]-[RANDOM]
    // Contoh: TEST-17351234-001-992
    $uniqId = time() . mt_rand(100, 999);
    $no_faktur = "TEST-" . date('ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT) . "-" . mt_rand(10, 99);

    // 2. Random Data Lainnya
    $randSup = $suppliers[array_rand($suppliers)];
    $kode_supplier = $randSup['code'];
    $nama_supplier = $randSup['name'];

    $kode_store = $stores[array_rand($stores)];

    // Random Tanggal (Antara 1 Des 2025 - 25 Des 2025)
    $day = str_pad(mt_rand(1, 25), 2, '0', STR_PAD_LEFT);
    $tgl_nota = "2025-12-" . $day;
    $tanggal_bayar = "2025-12-" . $day; // Anggap bayar di hari yg sama utk dummy

    // Random Uang
    $total_bayar = mt_rand(100000, 5000000); // 100rb - 5jt
    $potongan = mt_rand(0, 1) ? mt_rand(5000, 50000) : 0; // 50% kemungkinan ada potongan
    $ket_potongan = $potongan > 0 ? "Diskon Promo" : "-";

    $ket = $kets[array_rand($kets)];

    // 3. Binding & Execute
    // Tipe data: ssssdsdssss (sesuai file asli Anda)
    $stmt->bind_param(
        "ssssdsdssss",
        $tgl_nota,
        $no_faktur,
        $kode_supplier,
        $nama_supplier,
        $potongan,
        $ket_potongan,
        $total_bayar,
        $tanggal_bayar,
        $kode_store,
        $ket,
        $kd_user_dummy
    );

    if ($stmt->execute()) {
        $successCount++;
    } else {
        $failCount++;
        $errors[] = "Row $i Failed: " . $stmt->error;
    }
}

echo json_encode([
    'success' => true,
    'message' => "Selesai. Berhasil: $successCount, Gagal: $failCount",
    'errors' => $errors
], JSON_PRETTY_PRINT);

?>