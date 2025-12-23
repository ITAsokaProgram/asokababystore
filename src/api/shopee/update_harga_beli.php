<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_update_hb.log');
header('Content-Type: application/json');
try {
    if (!file_exists('../../../aa_kon_sett.php')) {
        throw new Exception("File konfigurasi database tidak ditemukan.");
    }
    include '../../../aa_kon_sett.php';
    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new Exception("Koneksi Database Gagal.");
    }
    $sql_update_receipt = "
        UPDATE s_shopee_produk sp
                    INNER JOIN (
                        SELECT r1.barcode, 
                            (r1.netto + r1.ppn) AS total_beli_receipt
                        FROM receipt r1
                        INNER JOIN (
                            SELECT barcode, MAX(tgl_tiba) AS max_tgl
                            FROM receipt 
                            WHERE kd_store = '3190'
                            GROUP BY barcode
                        ) r2 ON r1.barcode = r2.barcode AND r1.tgl_tiba = r2.max_tgl
                        WHERE r1.kd_store = '3190'
                        GROUP BY r1.barcode 
                    ) src ON sp.barcode = src.barcode
                    LEFT JOIN s_stok_ol so ON sp.barcode = so.item_n AND so.KD_STORE = '9998'
                    SET 
                        sp.hb_old = CASE 
                            WHEN sp.harga_beli = src.total_beli_receipt THEN sp.hb_old
                            WHEN sp.harga_beli IS NULL OR sp.harga_beli = 0 THEN src.total_beli_receipt
                            ELSE sp.harga_beli 
                        END,
                        sp.harga_beli = src.total_beli_receipt,
                        sp.keterangan = 'Dari Receipt (Last Data)'
                    WHERE so.item_n IS NULL;
    ";
    if (!$conn->query($sql_update_receipt)) {
        throw new Exception("Gagal update dari Receipt: " . $conn->error);
    }
    $affected_receipt = $conn->affected_rows;
    $sql_update_stok_ol = "
        UPDATE s_shopee_produk sp
        INNER JOIN s_stok_ol so ON sp.barcode = so.item_n
        SET 
            sp.hb_old = sp.harga_beli,
            sp.harga_beli = so.hrg_beli,
            sp.keterangan = 'Dari Stok OL'
        WHERE 
            so.KD_STORE = '9998'
    ";
    if (!$conn->query($sql_update_stok_ol)) {
        throw new Exception("Gagal update dari Stok OL: " . $conn->error);
    }
    $affected_ol = $conn->affected_rows;
    $logger->info("Update Harga Beli Sukses. Receipt: $affected_receipt, OL: $affected_ol");
    echo json_encode([
        'success' => true,
        'message' => "Update Selesai.\nReceipt (Stok OL Kosong): $affected_receipt item.\nStok OL: $affected_ol item."
    ]);
} catch (Throwable $t) {
    $logger->error("Error Update Harga Beli: " . $t->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Error: " . $t->getMessage()
    ]);
}
?>