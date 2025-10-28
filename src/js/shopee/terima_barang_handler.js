import {
    getTempReceiptItems,
    addTempReceiptItem,
    addTempReceiptItemByPlu,
    updateTempReceiptItem, 
    deleteTempReceiptItems,
    deleteAllTempReceiptItems,
    saveTempReceipt
} from './api_service.js';
import { unformatRupiah } from './calculate_terima_barang.js';

document.addEventListener('DOMContentLoaded', () => {
    function formatRupiah(value) {
        let number = parseFloat(value);
        if (isNaN(number)) number = 0;

        const hasDecimal = number % 1 !== 0;
        
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: hasDecimal ? 2 : 0,
            maximumFractionDigits: 2
        }).format(number);
    }
    
    function formatPersen(value) {
        let number = parseFloat(value);
        if (isNaN(number)) number = 0;
        return number.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function updateTempFooter() {
        if (!tempTableBody) return;
        const rows = tempTableBody.querySelectorAll('tr[data-plu]');
        let totalQty = 0;
        let grandTotalNet = 0;

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('[data-field="qty_rec"]').value) || 0;
            const hrg_beli_raw = row.querySelector('[data-field="hrg_beli"]').value;
            const hrg_beli = parseFloat(unformatRupiah(hrg_beli_raw)) || 0;

            totalQty += qty;
            grandTotalNet += (hrg_beli * qty);
        });

        const ppn = grandTotalNet * 0.11;
        const totalPenerimaan = grandTotalNet + ppn;


        const totalQtyEl = document.getElementById('temp-total-qty');
        const grandTotalNetEl = document.getElementById('temp-grand-total-net');
        const ppnEl = document.getElementById('temp-ppn');
        const totalPenerimaanEl = document.getElementById('temp-total-penerimaan');

        if (totalQtyEl) totalQtyEl.textContent = formatRupiah(totalQty);
        if (grandTotalNetEl) grandTotalNetEl.textContent = 'Rp ' + formatRupiah(grandTotalNet);
        if (ppnEl) ppnEl.textContent = 'Rp ' + formatRupiah(ppn);
        if (totalPenerimaanEl) totalPenerimaanEl.textContent = 'Rp ' + formatRupiah(totalPenerimaan);
    }

    const stockTable = document.getElementById('stock-table');
    if (stockTable) {
        stockTable.addEventListener('click', async (e) => {
            if (e.target.classList.contains('btn-add-to-temp')) {
                const button = e.target;
                const row = button.closest('tr');
                const itemData = { ...row.dataset };
                
                itemData.plu = itemData.plu;
                itemData.DESCP = itemData.descp;
                itemData.VENDOR = itemData.vendor;
                itemData.hrg_beli = parseFloat(itemData.hrgBeli) || 0;
                itemData.price = parseFloat(itemData.price) || 0;
                itemData.ITEM_N = itemData.itemN;

                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                try {
                    const result = await addTempReceiptItem(itemData);
                    Swal.fire('Berhasil', result.message, 'success');
                    loadTempTable();
                } catch (error) {
                    Swal.fire('Gagal', error.message, 'error');
                } finally {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-plus"></i> Tambah';
                }
            }
        });
    }

    const tempTableBody = document.getElementById('temp-receipt-body');
    const saveTempForm = document.getElementById('save-temp-form');
    const selectAllCheckbox = document.getElementById('select-all-temp');
    const deleteSelectedButton = document.getElementById('delete-selected-temp');
    const deleteAllButton = document.getElementById('delete-all-temp');
    const noLpbInput = document.getElementById('no_lpb');

    function updateSelectAllState() {
        if (!selectAllCheckbox || !tempTableBody) return;

        const allCheckboxes = tempTableBody.querySelectorAll('.input-cb-temp');
        const checkedCheckboxes = tempTableBody.querySelectorAll('.input-cb-temp:checked');
        const total = allCheckboxes.length;
        const checkedCount = checkedCheckboxes.length;

        if (total === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }

        if (checkedCount === total) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    async function loadTempTable() {
        if (!tempTableBody) return;
        tempTableBody.innerHTML = '<tr><td colspan="16" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Memuat data keranjang...</td></tr>';
        try {
            const result = await getTempReceiptItems();
            if (result.success && result.items.length > 0) {
                tempTableBody.innerHTML = result.items.map(item => createTempRowHtml(item)).join('');
            } else {
                tempTableBody.innerHTML = '<tr><td colspan="16" class="text-center p-4 text-gray-500">Keranjang penerimaan masih kosong.</td></tr>';
            }
            updateSelectAllState();
            updateTempFooter(); 
        } catch (error) {
            tempTableBody.innerHTML = `<tr><td colspan="16" class="text-center p-4 text-red-500">Gagal memuat data: ${error.message}</td></tr>`;
            updateTempFooter(); 
        }
    }

    function createTempRowHtml(item) {
        const qty = parseFloat(item.QTY_REC) || 0;
        const hrg_beli = parseFloat(item.hrg_beli) || 0;
        const price = parseFloat(item.price) || 0;
        
        const itemN = item.ITEM_N || item.plu; 
        const avg_cost = parseFloat(item.calc_weighted_avg_cost) || 0;
        const ppn = parseFloat(item.ppn) || 0;
        const netto = parseFloat(item.netto) || 0;
        const hb_plus_lainnya = parseFloat(item.calc_hb_plus_lainnya) || 0;
        const margin = parseFloat(item.calc_margin) || 0;
        
        const admin_pct = parseFloat(item.kategori_admin_pct) || 0;
        const ongkir_pct = parseFloat(item.kategori_ongkir_pct) || 0;
        const promo_pct = parseFloat(item.kategori_promo_pct) || 0;
        const biaya_pesanan = parseFloat(item.kategori_biaya_pesanan) || 0;

        const total = netto * qty;

        let marginClass = 'text-gray-900';
        if (margin < 0) {
            marginClass = 'text-red-600 font-bold';
        } else if (margin > 0) {
            marginClass = 'text-green-600 font-bold';
        }

        return `
        <tr data-plu="${item.plu}">
            <td class="p-3 text-center"><input type="checkbox" class="input-cb-temp" value="${item.plu}"></td>
            <td class="p-3 font-semibold">${itemN}</td>
            <td class="p-3">${item.descp}</td>
            
            <td class="p-3"><input type="number" value="${qty}" min="0" class="input-qty input-temp-update" data-field="qty_rec"></td>
            
            <td class="p-3"><input type="text" value="${formatRupiah(avg_cost)}" class="input-qty input-disabled" data-field="calc_weighted_avg_cost" disabled></td>
            
            <td class="p-3"><input type="text" value="${formatRupiah(hrg_beli)}" min="0" class="input-qty input-temp-update input-rupiah" data-field="hrg_beli" inputmode="decimal"></td>
            
            <td class="p-3"><input type="text" value="${formatRupiah(ppn)}" class="input-qty input-disabled" data-field="ppn" disabled></td>
            <td class="p-3"><input type="text" value="${formatRupiah(netto)}" class="input-qty input-disabled" data-field="netto" disabled></td>
            <td class="p-3"><input type="text" value="${formatRupiah(hb_plus_lainnya)}" class="input-qty input-disabled" data-field="calc_hb_plus_lainnya" disabled></td>
            
            <td class="p-3"><input type="text" value="${formatRupiah(price)}" min="0" class="input-qty input-temp-update input-rupiah" data-field="price" inputmode="decimal"></td>
            
            <td class="p-3"><input type="text" value="${formatRupiah(margin)}" class="input-qty input-disabled ${marginClass}" data-field="calc_margin" disabled></td>
            <td class="p-3"><input type="text" value="${formatPersen(admin_pct)}" class="input-qty input-disabled" data-field="kategori_admin_pct" disabled></td>
            <td class="p-3"><input type="text" value="${formatPersen(ongkir_pct)}" class="input-qty input-disabled" data-field="kategori_ongkir_pct" disabled></td>
            <td class="p-3"><input type="text" value="${formatPersen(promo_pct)}" class="input-qty input-disabled" data-field="kategori_promo_pct" disabled></td>
            <td class="p-3"><input type="text" value="${formatRupiah(biaya_pesanan)}" class="input-qty input-disabled" data-field="kategori_biaya_pesanan" disabled></td>
            
            <td class="p-3"><input type="text" value="${formatRupiah(total)}" class="input-qty input-disabled font-bold text-gray-900" data-field="total" disabled></td>
        </tr>
        `;
    }

    if (tempTableBody) {
        let updateTimeout;

        tempTableBody.addEventListener('blur', (e) => {
            if (e.target.classList.contains('input-rupiah')) {
                const input = e.target;
                const unformattedValue = unformatRupiah(input.value);
                input.value = formatRupiah(unformattedValue);
            }
        }, true);

        tempTableBody.addEventListener('input', (e) => {
            if (e.target.classList.contains('input-temp-update')) {
                clearTimeout(updateTimeout);
                const row = e.target.closest('tr');
                
                e.target.style.borderColor = '#667eea';
                
                updateTimeout = setTimeout(() => {
                    sendUpdateToServer(row, e.target); 
                }, 1000); 
            }
        });
        tempTableBody.addEventListener('change', (e) => {
            if (e.target.classList.contains('input-cb-temp')) {
                updateSelectAllState();
            }
        });
    }

    async function sendUpdateToServer(row, inputElement) {
        const plu = row.dataset.plu;
        const hrg_beli_raw = row.querySelector('[data-field="hrg_beli"]').value;
        const price_raw = row.querySelector('[data-field="price"]').value;
        const qty_rec = parseFloat(row.querySelector('[data-field="qty_rec"]').value) || 0;

        const hrg_beli = parseFloat(unformatRupiah(hrg_beli_raw)) || 0;
        const price = parseFloat(unformatRupiah(price_raw)) || 0;

        try {   
            const updatedItem = await updateTempReceiptItem(plu, qty_rec, hrg_beli, price);
            
            if (updatedItem.success && updatedItem.item) {
                const item = updatedItem.item;
                const margin = parseFloat(item.calc_margin) || 0;
                
                row.querySelector('[data-field="calc_weighted_avg_cost"]').value = formatRupiah(item.calc_weighted_avg_cost);
                row.querySelector('[data-field="ppn"]').value = formatRupiah(item.ppn);
                row.querySelector('[data-field="netto"]').value = formatRupiah(item.netto);
                row.querySelector('[data-field="calc_hb_plus_lainnya"]').value = formatRupiah(item.calc_hb_plus_lainnya);
                
                const marginInput = row.querySelector('[data-field="calc_margin"]');
                marginInput.value = formatRupiah(margin);
                
                marginInput.classList.remove('text-red-600', 'text-green-600', 'text-gray-900', 'font-bold');
                if (margin < 0) {
                    marginInput.classList.add('text-red-600', 'font-bold');
                } else if (margin > 0) {
                    marginInput.classList.add('text-green-600', 'font-bold');
                } else {
                    marginInput.classList.add('text-gray-900');
                }

                row.querySelector('[data-field="kategori_admin_pct"]').value = formatPersen(item.kategori_admin_pct);
                row.querySelector('[data-field="kategori_ongkir_pct"]').value = formatPersen(item.kategori_ongkir_pct);
                row.querySelector('[data-field="kategori_promo_pct"]').value = formatPersen(item.kategori_promo_pct);
                row.querySelector('[data-field="kategori_biaya_pesanan"]').value = formatRupiah(item.kategori_biaya_pesanan);

                const newTotal = (parseFloat(item.netto) || 0) * qty_rec;
                row.querySelector('[data-field="total"]').value = formatRupiah(newTotal);

                if (inputElement) inputElement.style.borderColor = '#10b981'; 

                updateTempFooter();

            } else {
                 throw new Error(updatedItem.message || 'Data item tidak diterima dari server.');
            }

        } catch (error) {
            console.error('Update Gagal:', error.message);
            if (inputElement) inputElement.style.borderColor = '#ef4444'; 
            Swal.fire('Update Gagal', `Gagal menyimpan data untuk PLU ${plu}: ${error.message}`, 'error');
        } finally {
            if (inputElement) {
                setTimeout(() => {
                    inputElement.style.borderColor = ''; 
                }, 1500);
            }
        }
    }
    if (saveTempForm) {
        saveTempForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const no_lpb = noLpbInput.value.trim();
            if (!no_lpb) {
                Swal.fire('Validasi Gagal', 'Nomor LPB wajib diisi.', 'error');
                noLpbInput.focus();
                return;
            }
            Swal.fire({
                title: 'Konfirmasi Penerimaan',
                html: `Anda akan menyimpan semua item di keranjang dengan No LPB <strong>${no_lpb}</strong>. Lanjutkan?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    handleSaveSubmit(no_lpb);
                }
            });
        });
    }

    async function handleSaveSubmit(no_lpb) {
        Swal.fire({
            title: 'Memproses...',
            html: 'Sedang menyimpan data penerimaan barang. Mohon tunggu...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        try {
            const result = await saveTempReceipt(no_lpb);
            Swal.fire({
                title: 'Berhasil!',
                text: result.message,
                icon: 'success'
            }).then(() => {
                window.location.reload();
            });
        } catch (error) {
            console.error('Error saat menyimpan:', error);
            Swal.fire({
                title: 'Terjadi Kesalahan',
                text: error.message || 'Gagal menyimpan data ke server.',
                icon: 'error'
            });
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', (e) => {
            const checkboxes = tempTableBody.querySelectorAll('.input-cb-temp');
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;
            });
            updateSelectAllState(); 
        });
    }

    if (deleteSelectedButton) {
        deleteSelectedButton.addEventListener('click', async () => {
            const checkboxes = tempTableBody.querySelectorAll('.input-cb-temp:checked');
            const plusToDelete = Array.from(checkboxes).map(cb => cb.value);
            if (plusToDelete.length === 0) {
                Swal.fire('Tidak Ada Item', 'Pilih item yang ingin dihapus.', 'warning');
                return;
            }
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Anda akan menghapus ${plusToDelete.length} item terpilih dari keranjang. Lanjutkan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const deleteResult = await deleteTempReceiptItems(plusToDelete);
                        Swal.fire('Berhasil', deleteResult.message, 'success');
                        loadTempTable();
                    } catch (error) {
                        Swal.fire('Gagal', error.message, 'error');
                    }
                }
            });
        });
    }

    if (deleteAllButton) {
        deleteAllButton.addEventListener('click', () => {
            Swal.fire({
                title: 'Konfirmasi Hapus Semua',
                text: `Anda akan MENGHAPUS SEMUA item di keranjang. Aksi ini tidak dapat dibatalkan. Lanjutkan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const deleteResult = await deleteAllTempReceiptItems();
                        Swal.fire('Berhasil', deleteResult.message, 'success');
                        loadTempTable();
                    } catch (error) {
                        Swal.fire('Gagal', error.message, 'error');
                    }
                }
            });
        });
    }

    const quickAddPluInput = document.getElementById('quick-add-plu');
    const quickAddButton = document.getElementById('quick-add-button');
    const quickAddVendor = document.getElementById('quick-add-vendor');

    const handleQuickAdd = async () => {
        const plu = quickAddPluInput.value.trim();
        const vendor = quickAddVendor.value.trim();
        if (!vendor || vendor === 'ALL') {
            Swal.fire('Validasi Gagal', "Pilih Vendor di terlebih dahulu.", 'error');
            quickAddVendor.focus();
            return;
        }
        if (!plu) {
            Swal.fire('Validasi Gagal', 'PLU tidak boleh kosong.', 'error');
            quickAddPluInput.focus();
            return;
        }
        quickAddButton.disabled = true;
        quickAddButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        try {
            const result = await addTempReceiptItemByPlu(plu, vendor);
            Swal.fire('Berhasil', result.message, 'success');
            loadTempTable();
            quickAddPluInput.value = '';
            quickAddPluInput.focus();
        } catch (error) {
            Swal.fire('Gagal', error.message, 'error');
        } finally {
            quickAddButton.disabled = false;
            quickAddButton.innerHTML = '<i class="fas fa-plus"></i>';
        }
    };

    if (quickAddButton) {
        quickAddButton.addEventListener('click', handleQuickAdd);
    }
    if (quickAddPluInput) {
        quickAddPluInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleQuickAdd();
            }
        });
    }

    loadTempTable();
});