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


$admin_cost_hb = ($hrg_beli * $admin_pct) / 100;
$ongkir_cost_hb = ($ongkir_max > 0) ? min(($hrg_beli * $ongkir_pct) / 100, $ongkir_max) : ($hrg_beli * $ongkir_pct) / 100;
$promo_cost_hb = ($promo_max > 0) ? min(($hrg_beli * $promo_pct) / 100, $promo_max) : ($hrg_beli * $promo_pct) / 100;
$total_hb_display = $hrg_beli + $admin_cost_hb + $ongkir_cost_hb + $promo_cost_hb + $biaya_pesanan_cost;

$admin_cost_hj = ($price * $admin_pct) / 100;
$ongkir_cost_hj = ($ongkir_max > 0) ? min(($price * $ongkir_pct) / 100, $ongkir_max) : ($price * $ongkir_pct) / 100;
$promo_cost_hj = ($promo_max > 0) ? min(($price * $promo_pct) / 100, $promo_max) : ($price * $promo_pct) / 100;
$hpp_real = $hrg_beli + $admin_cost_hj + $ongkir_cost_hj + $promo_cost_hj + $biaya_pesanan_cost;
$total_hj_display = $price + $admin_cost_hj + $ongkir_cost_hj + $promo_cost_hj + $biaya_pesanan_cost;

$margin = $price - $hpp_real;
$margin_pct = ($price > 0) ? ($margin / $price) * 100 : 0;
echo json_encode([
    'success' => true,
    'data' => [
        'sku' => $sku,
        'hrg_beli' => $hrg_beli,
        'current_price' => $price,
        'costs' => [
            'admin_pct' => $admin_pct,
            'admin_rp' => $admin_cost_hj,
            'ongkir_pct' => $ongkir_pct,
            'ongkir_rp' => $ongkir_cost_hj,
            'promo_pct' => $promo_pct,
            'promo_rp' => $promo_cost_hj,
            'biaya_pesanan' => $biaya_pesanan_cost,
            'total_hj' => $total_hj_display
        ],
        'margin_rp' => $margin,
        'margin_pct' => $margin_pct,
        'based_on_hb' => [
            'admin_rp' => $admin_cost_hb,
            'ongkir_rp' => $ongkir_cost_hb,
            'promo_rp' => $promo_cost_hb,
            'biaya_pesanan' => $biaya_pesanan_cost,
            'total_display' => $total_hb_display
        ]
    ]
]);