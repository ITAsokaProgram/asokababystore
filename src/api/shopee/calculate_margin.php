<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../utils/Logger.php';
include '../../../aa_kon_sett.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$sku = $data['sku'] ?? '';
$hrg_beli = (float) ($data['hrg_beli'] ?? 0);
$price = (float) ($data['price'] ?? 0);
$kd_store = '9998';
if (empty($sku)) {
    echo json_encode(['success' => false, 'message' => 'SKU tidak ditemukan']);
    exit;
}
$stmt_kat = $conn->prepare("SELECT admin, x_ongkir, x_ongkir_max, x_promo, x_promo_max, biaya_pesanan FROM s_kategori WHERE KD_STORE = ? AND ITEM_N = ?");
$stmt_kat->bind_param("ss", $kd_store, $sku);
$stmt_kat->execute();
$kat_result = $stmt_kat->get_result();
$kategori_data = $kat_result->fetch_assoc();
$stmt_kat->close();
$admin_pct = (float) ($kategori_data['admin'] ?? 0);
$ongkir_pct = (float) ($kategori_data['x_ongkir'] ?? 0);
$ongkir_max = (float) ($kategori_data['x_ongkir_max'] ?? 0);
$promo_pct = (float) ($kategori_data['x_promo'] ?? 0);
$promo_max = (float) ($kategori_data['x_promo_max'] ?? 0);
$biaya_pesanan_cost = (float) ($kategori_data['biaya_pesanan'] ?? 0);
$ppn = 0;
$netto = $hrg_beli + $ppn;

// HB
$admin_cost = ($netto * $admin_pct) / 100;
$ongkir_cost_raw = ($netto * $ongkir_pct) / 100;
$ongkir_cost = ($ongkir_max > 0 && $ongkir_cost_raw > $ongkir_max) ? $ongkir_max : $ongkir_cost_raw;
$promo_cost_raw = ($netto * $promo_pct) / 100;
$promo_cost = ($promo_max > 0 && $promo_cost_raw > $promo_max) ? $promo_max : $promo_cost_raw;
$hb_plus_lainnya = $netto + $admin_cost + $ongkir_cost + $promo_cost + $biaya_pesanan_cost;
// HJ
$admin_cost_margin = ($price * $admin_pct) / 100;
$ongkir_cost_margin_raw = ($price * $ongkir_pct) / 100;
$ongkir_cost_margin = ($ongkir_max > 0 && $ongkir_cost_margin_raw > $ongkir_max) ? $ongkir_max : $ongkir_cost_margin_raw;
$promo_cost_margin_raw = ($price * $promo_pct) / 100;
$promo_cost_margin = ($promo_max > 0 && $promo_cost_margin_raw > $promo_max) ? $promo_max : $promo_cost_margin_raw;
$margin = $price - ($netto + $admin_cost_margin + $ongkir_cost_margin + $promo_cost_margin + $biaya_pesanan_cost);
$margin_pct = ($price > 0) ? ($margin / $price) * 100 : 0;
echo json_encode([
    'success' => true,
    'data' => [
        'sku' => $sku,
        'hrg_beli' => $hrg_beli,
        'ppn' => $ppn,
        'netto' => $netto,
        'current_price' => $price,
        'costs' => [
            'admin_pct' => $admin_pct,
            'admin_rp' => $admin_cost_margin,
            'ongkir_pct' => $ongkir_pct,
            'ongkir_rp' => $ongkir_cost_margin,
            'promo_pct' => $promo_pct,
            'promo_rp' => $promo_cost_margin,
            'biaya_pesanan' => $biaya_pesanan_cost,
            'total_lain' => $admin_cost_margin + $ongkir_cost_margin + $promo_cost_margin + $biaya_pesanan_cost
        ],
        'hpp_total' => $hb_plus_lainnya,
        'margin_rp' => $margin,
        'margin_pct' => $margin_pct,
        'based_on_hb' => [
            'admin_rp' => $admin_cost,
            'ongkir_rp' => $ongkir_cost,
            'promo_rp' => $promo_cost,
            'biaya_pesanan' => $biaya_pesanan_cost,
            'total_lain' => $admin_cost + $ongkir_cost + $promo_cost + $biaya_pesanan_cost,
            'hpp_total' => $hb_plus_lainnya
        ]
    ]
]);
?>