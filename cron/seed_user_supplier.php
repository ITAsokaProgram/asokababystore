<?php
require_once __DIR__ . "/../aa_kon_sett.php";
echo "Mulai proses seeding data dummy User Supplier...\n";
$list_nama_depan = [
    'Budi', 'Siti', 'Agus', 'Dewi', 'Rina', 'Joko', 'Santoso', 'Lestari', 
    'Andi', 'Mega', 'Rudi', 'Nina', 'Eko', 'Sari', 'Hendra', 'Yanti'
];
$list_nama_belakang = [
    'Jaya', 'Abadi', 'Sejahtera', 'Makmur', 'Sentosa', 
    'Mandiri', 'Perkasa', 'Utama', 'Group', 'Logistik'
];
$list_wilayah_master = [
    'DKI Jakarta', 
    'Bogor', 
    'Depok', 
    'Tangerang', 
    'Bekasi', 
    'Bandung', 
    'Surabaya', 
    'Semarang', 
    'Medan', 
    'Makassar',
    'Bali'
];
$list_domain = ['gmail.com', 'yahoo.com', 'outlook.com', 'supplier.id', 'company.net'];
$default_password = password_hash('123456', PASSWORD_DEFAULT);
mysqli_autocommit($conn, false);
try {
    mysqli_query($conn, "TRUNCATE TABLE user_supplier");
    echo "Table user_supplier dikosongkan.\n";
    $sql = "INSERT INTO user_supplier (nama, email, no_telpon, wilayah, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Gagal prepare: " . mysqli_error($conn));
    }
    $total_data = 50; 
    for ($i = 1; $i <= $total_data; $i++) {
        $nama_full = $list_nama_depan[array_rand($list_nama_depan)] . " " . $list_nama_belakang[array_rand($list_nama_belakang)];
        if (rand(0, 1) == 1) {
            $nama_full = "PT " . $nama_full; 
        }
        $clean_name = strtolower(str_replace(' ', '', $nama_full));
        $clean_name = str_replace('pt', '', $clean_name);
        $email = $clean_name . rand(10, 99) . "@" . $list_domain[array_rand($list_domain)];
        $no_telpon = "08" . rand(11, 99)  . rand(1000, 9999)  . rand(1000, 9999);
        $shuffled_wilayah = $list_wilayah_master;
        shuffle($shuffled_wilayah); 
        $count_wilayah = rand(1, 4); 
        $selected_wilayah = array_slice($shuffled_wilayah, 0, $count_wilayah);
        $wilayah_string = implode(", ", $selected_wilayah);
        mysqli_stmt_bind_param(
            $stmt, 
            "sssss", 
            $nama_full, 
            $email, 
            $no_telpon, 
            $wilayah_string, 
            $default_password
        );
        mysqli_stmt_execute($stmt);
        if ($i % 10 == 0) {
            echo "Inserted $i row...\n";
        }
    }
    mysqli_commit($conn);
    echo "BERHASIL! $total_data data dummy user supplier telah ditambahkan.\n";
    echo "Password default untuk login: 123456\n";
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "GAGAL: " . $e->getMessage() . "\n";
}
mysqli_close($conn);
?>