<?php
require_once __DIR__ . "/../aa_kon_sett.php";
echo "Mulai proses seeding data dummy (Multi-Value Comma Separated)...\n";
$list_pic_master = ['Annisa', 'Dewi', 'Erna'];
$list_mop = ['Potong Tagihan', 'Transfer'];
$list_cabang = [
    ['1901', 'ASOKA BABY STORE PRAMUKA'],
    ['1902', 'ASOKA BABY STORE KALIABANG'],
    ['1903', 'ASOKA BABY STORE CIKARANG'],
    ['1904', 'ASOKA BABY STORE SEMPER'],
    ['1905', 'ASOKA BABY STORE DEPOK']
];
$list_program = [
    'Listing Fee Produk Baru',
    'Rebate Tahunan 2024',
    'Sewa Gondola Endcap',
    'Participation Fee Anniversary',
    'Program Diskon Kemerdekaan',
    'Claim Biaya Logistik'
];
$list_supplier = [
    'PT UNILEVER INDONESIA TBK',
    'PT MAYORA INDAH TBK',
    'CV KARYA JAYA PERKASA',
    'PT WINGS SURYA',
    'PT INDOFOOD CBP SUKSES MAKMUR',
    'PT NESTLE INDONESIA',
    'CV BERKAH ABADI'
];
$list_user = ['admin', 'finance01', 'purchasing02', 'edo'];
mysqli_autocommit($conn, false);
try {
    mysqli_query($conn, "TRUNCATE TABLE program_supplier");
    $sql = "INSERT INTO program_supplier (
        nomor_dokumen, pic, nama_supplier, kode_cabang, nama_cabang, 
        periode_program, nama_program, nilai_program, mop, 
        top_date, nilai_transfer, tanggal_transfer, tgl_fpk, 
        nsfp, dpp, ppn, pph, nomor_bukpot, kd_user
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt)
        throw new Exception("Gagal prepare: " . mysqli_error($conn));
    $total_data = 220;
    for ($i = 1; $i <= $total_data; $i++) {
        $doc_count = rand(1, 3);
        $docs = [];
        for ($d = 0; $d < $doc_count; $d++) {
            $type = rand(0, 2);
            $uniq = sprintf('%03d', $i) . rand(10, 99);
            if ($type == 0)
                $docs[] = "SKP" . $uniq;
            elseif ($type == 1)
                $docs[] = "KWT" . $uniq;
            else
                $docs[] = "ASK-CDT" . $uniq . "-" . rand(100, 999);
        }
        $nomor_dokumen = implode(", ", $docs);
        $shuffled_pic = $list_pic_master;
        shuffle($shuffled_pic); 
        $pic_count = rand(1, 3); 
        $selected_pics = array_slice($shuffled_pic, 0, $pic_count);
        $pic = implode(", ", $selected_pics);
        $idx_cabang = array_rand($list_cabang);
        $kode_cabang = $list_cabang[$idx_cabang][0];
        $nama_cabang = $list_cabang[$idx_cabang][1];
        $supplier = $list_supplier[array_rand($list_supplier)];
        $program = $list_program[array_rand($list_program)];
        $mop = $list_mop[array_rand($list_mop)];
        $kd_user = $list_user[array_rand($list_user)];
        $timestamp = strtotime("-" . rand(0, 365) . " days");
        $tgl_program = date('Y-m-d', $timestamp);
        $periode = date('F Y', $timestamp);
        $top_date = date('Y-m-d', strtotime($tgl_program . " + " . rand(7, 30) . " days"));
        $tgl_transfer = date('Y-m-d', strtotime($top_date . " + " . rand(1, 5) . " days"));
        $tgl_fpk = date('Y-m-d', strtotime($tgl_program . " - " . rand(1, 5) . " days"));
        $nilai_program = rand(1000000, 50000000);
        $dpp = $nilai_program;
        $ppn = $dpp * 0.11;
        $pph = $dpp * 0.02;
        $nilai_transfer = $dpp + $ppn - $pph;
        $nsfp = '040' . rand(100, 999) . '-' . rand(10, 99) . '.' . rand(10000000, 99999999);
        $bukpot = strtoupper(substr(md5(uniqid()), 0, 8));
        $types = str_repeat("s", 19);
        mysqli_stmt_bind_param(
            $stmt,
            $types,
            $nomor_dokumen, 
            $pic,           
            $supplier,
            $kode_cabang,
            $nama_cabang,
            $periode,
            $program,
            $nilai_program,
            $mop,
            $top_date,
            $nilai_transfer,
            $tgl_transfer,
            $tgl_fpk,
            $nsfp,
            $dpp,
            $ppn,
            $pph,
            $bukpot,
            $kd_user
        );
        mysqli_stmt_execute($stmt);
        if ($i % 50 == 0)
            echo "Inserted $i row...\n";
    }
    mysqli_commit($conn);
    echo "BERHASIL! Data dummy dengan multi-value (comma separated) telah ditambahkan.\n";
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "GAGAL: " . $e->getMessage() . "\n";
}
mysqli_close($conn);
?>