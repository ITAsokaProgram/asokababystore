import { bulkTerimaBarang } from './api_service.js';
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('bulk-receive-form');
    if (!form) {
        return; 
    }
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const noLpbInput = document.getElementById('no_lpb');
        const kdStoreInput = document.getElementById('form_kd_store');
        const no_lpb = noLpbInput.value.trim();
        const kd_store = kdStoreInput.value.trim();
        if (!no_lpb) {
            Swal.fire('Validasi Gagal', 'Nomor LPB wajib diisi.', 'error');
            noLpbInput.focus();
            return;
        }
        if (!kd_store) {
            Swal.fire('Validasi Gagal', 'Kode Store tidak ditemukan. Silakan muat ulang data.', 'error');
            return;
        }
        const items = [];
        const rows = document.querySelectorAll('#stock-table tbody tr');
        rows.forEach(row => {
            const qty_rec = parseFloat(row.querySelector('input[name="qty_rec"]').value) || 0;
            if (qty_rec > 0) {
                const dataset = row.dataset;
                const item = {
                    plu: dataset.plu,
                    barcode: dataset.plu, 
                    descp: dataset.descp,
                    avg_cost: parseFloat(dataset.avgCost) || 0,
                    hrg_beli: parseFloat(dataset.hrgBeli) || 0,
                    ppn: parseFloat(dataset.ppn) || 0,
                    netto: parseFloat(dataset.netto) || 0,
                    price: parseFloat(dataset.price) || 0,
                    net_price: parseFloat(dataset.price) || 0, 
                    qty_rec: qty_rec,
                    admin_s: parseFloat(row.querySelector('input[name="admin_s"]').value) || 0,
                    ongkir: parseFloat(row.querySelector('input[name="ongkir"]').value) || 0,
                    promo: parseFloat(row.querySelector('input[name="promo"]').value) || 0,
                    biaya_psn: parseFloat(row.querySelector('input[name="biaya_psn"]').value) || 0,
                    kode_supp: dataset.vendor 
                };
                items.push(item);
            }
        });
        if (items.length === 0) {
            Swal.fire('Tidak Ada Data', 'Tidak ada item yang memiliki "Qty Terima" lebih dari 0.', 'warning');
            return;
        }
        const payload = {
            kd_store,
            no_lpb,
            items
        };
        Swal.fire({
            title: 'Konfirmasi Penerimaan',
            html: `Anda akan menerima <strong>${items.length}</strong> jenis item dengan No LPB <strong>${no_lpb}</strong>. Lanjutkan?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                handleBulkSubmit(payload);
            }
        });
    });
});
async function handleBulkSubmit(payload) {
    Swal.fire({
        title: 'Memproses...',
        html: 'Sedang menyimpan data penerimaan barang. Mohon tunggu...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    try {
        const result = await bulkTerimaBarang(payload);
        Swal.fire({
            title: 'Berhasil!',
            text: result.message,
            icon: 'success'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        console.error('Error saat bulk insert:', error);
        Swal.fire({
            title: 'Terjadi Kesalahan',
            text: error.message || 'Gagal menyimpan data ke server.',
            icon: 'error'
        });
    }
}