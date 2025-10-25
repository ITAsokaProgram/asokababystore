import {
    getTempReceiptItems,
    addTempReceiptItem,
    addTempReceiptItemByPlu,
    updateTempReceiptItem,
    deleteTempReceiptItems,
    deleteAllTempReceiptItems,
    saveTempReceipt
} from './api_service.js';
document.addEventListener('DOMContentLoaded', () => {
    function formatRupiah(value) {
        let cleanValue = String(value).replace(/\./g, '').replace(/,/g, '.');
        let number = parseFloat(cleanValue);
        if (isNaN(number)) number = 0;
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0, 
            maximumFractionDigits: 2  
        }).format(number);
    }
    function unformatRupiah(value) {
        if (typeof value !== 'string') {
            value = String(value);
        }
        return value.replace(/\./g, '').replace(/,/g, '.');
    }
    const stockTable = document.getElementById('stock-table');
    if (stockTable) {
        stockTable.addEventListener('click', async (e) => {
            if (e.target.classList.contains('btn-add-to-temp')) {
                const button = e.target;
                const row = button.closest('tr');
                const itemData = { ...row.dataset };
                itemData.avg_cost = parseFloat(itemData.avgCost) || 0;
                itemData.hrg_beli = parseFloat(itemData.hrgBeli) || 0;
                itemData.ppn = parseFloat(itemData.ppn) || 0;
                itemData.netto = parseFloat(itemData.netto) || 0;
                itemData.price = parseFloat(itemData.price) || 0;
                itemData.plu = itemData.plu;
                itemData.DESCP = itemData.descp;
                itemData.VENDOR = itemData.vendor;
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
    async function loadTempTable() {
        if (!tempTableBody) return;
        // Ubah colspan menjadi 12
        tempTableBody.innerHTML = '<tr><td colspan="12" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Memuat data keranjang...</td></tr>';
        try {
            const result = await getTempReceiptItems();
            if (result.success && result.items.length > 0) {
                tempTableBody.innerHTML = result.items.map(item => createTempRowHtml(item)).join('');
            } else {
                // Ubah colspan menjadi 12
                tempTableBody.innerHTML = '<tr><td colspan="12" class="text-center p-4 text-gray-500">Keranjang penerimaan masih kosong.</td></tr>';
            }
        } catch (error) {
            // Ubah colspan menjadi 12
            tempTableBody.innerHTML = `<tr><td colspan="12" class="text-center p-4 text-red-500">Gagal memuat data: ${error.message}</td></tr>`;
        }
    }
    function createTempRowHtml(item) {
        const hrg_beli = parseFloat(item.hrg_beli) || 0;
        const price = parseFloat(item.price) || 0;
        const qty = parseFloat(item.QTY_REC) || 0;
        const admin_s = hrg_beli * 0.01;
        const ongkir = price * 0.12; 
        return `
            <tr data-plu="${item.plu}">
                <td class="p-3 text-center"><input type="checkbox" class="input-cb-temp" value="${item.plu}"></td>
                <td class="p-3 font-semibold">${item.plu}</td>
                <td class="p-3">${item.descp}</td>
                <td class="p-3"><input type="number" value="${qty}" min="0" class="input-qty input-temp-update" data-field="qty_rec"></td>
                <td class="p-3"><input type="text" value="${formatRupiah(hrg_beli)}" min="0" class="input-qty input-temp-update input-rupiah" data-field="hrg_beli" inputmode="decimal"></td>
                <td class="p-3"><input type="text" value="${formatRupiah(price)}" min="0" class="input-qty input-temp-update input-rupiah" data-field="price" inputmode="decimal"></td>
                <td class="p-3"><input type="text" value="${formatRupiah(admin_s)}" class="input-qty input-disabled" disabled></td>
                <td class="p-3"><input type="text" value="${formatRupiah(ongkir)}" class="input-qty input-disabled" disabled></td>
                <td class="p-3"><input type="number" value="0" class="input-qty input-disabled" disabled></td>
                <td class="p-3"><input type="number" value="0" class="input-qty input-disabled" disabled></td>
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
                updateCalculatedFields(row);
                updateTimeout = setTimeout(() => {
                    sendUpdateToServer(row);
                }, 1000);
            }
        });
    }
    function updateCalculatedFields(row) {
        const hrg_beli_raw = row.querySelector('[data-field="hrg_beli"]').value;
        const price_raw = row.querySelector('[data-field="price"]').value;
        const hrg_beli = parseFloat(unformatRupiah(hrg_beli_raw)) || 0;
        const price = parseFloat(unformatRupiah(price_raw)) || 0;
        const admin_s = hrg_beli * 0.01;
        const ongkir = price * 0.12; 
        row.querySelector('.input-disabled[disabled]').value = formatRupiah(admin_s);
        row.querySelectorAll('.input-disabled[disabled]')[1].value = formatRupiah(ongkir);
    }
    async function sendUpdateToServer(row) {
        const plu = row.dataset.plu;
        const hrg_beli_raw = row.querySelector('[data-field="hrg_beli"]').value;
        const price_raw = row.querySelector('[data-field="price"]').value;
        const qty_rec = parseFloat(row.querySelector('[data-field="qty_rec"]').value) || 0;
        const hrg_beli = parseFloat(unformatRupiah(hrg_beli_raw)) || 0;
        const price = parseFloat(unformatRupiah(price_raw)) || 0;
        const qtyInput = row.querySelector('[data-field="qty_rec"]');
        qtyInput.style.borderColor = '#667eea';
        try {
            await updateTempReceiptItem(plu, qty_rec, hrg_beli, price);
            qtyInput.style.borderColor = '#10b981';
        } catch (error) {
            console.error('Update Gagal:', error.message);
            qtyInput.style.borderColor = '#ef4444';
            Swal.fire('Update Gagal', `Gagal menyimpan data untuk PLU ${plu}: ${error.message}`, 'error');
        } finally {
            setTimeout(() => {
                qtyInput.style.borderColor = '';
            }, 1500);
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