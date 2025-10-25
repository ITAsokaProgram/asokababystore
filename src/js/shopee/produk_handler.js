import { updateStock, updatePrice, syncStock, syncAllStock, manageStokOl } from './api_service.js';
const updatePriceRange = (form) => {
    const productCard = form.closest('.update-form-wrapper');
    if (!productCard) return;
    const variantPriceSpans = productCard.querySelectorAll('.variant-price');
    if (variantPriceSpans.length === 0) return;
    const prices = Array.from(variantPriceSpans).map(span => {
        return parseInt(span.innerText.replace(/\./g, ''), 10);
    });
    const minPrice = Math.min(...prices);
    const maxPrice = Math.max(...prices);
    const formatter = new Intl.NumberFormat('id-ID');
    let newRangeText;
    if (minPrice === maxPrice) {
        newRangeText = formatter.format(minPrice);
    } else {
        newRangeText = `${formatter.format(minPrice)} - ${formatter.format(maxPrice)}`;
    }
    const itemId = form.querySelector('input[name="item_id"]').value;
    const mainPriceDisplay = document.getElementById(`price-display-${itemId}`);
    if (mainPriceDisplay) {
        mainPriceDisplay.innerText = newRangeText;
    }
};
const updateTotalStock = (form) => {
    const productCard = form.closest('.update-form-wrapper');
    if (!productCard) return;
    const variantStockSpans = productCard.querySelectorAll('.variant-stock');
    if (variantStockSpans.length === 0) return;
    const totalStock = Array.from(variantStockSpans).reduce((sum, span) => {
        const stock = parseInt(span.innerText, 10);
        return sum + (isNaN(stock) ? 0 : stock);
    }, 0);
    const itemId = form.querySelector('input[name="item_id"]').value;
    const mainStockDisplay = document.getElementById(`stock-display-${itemId}`);
    if (mainStockDisplay) {
        mainStockDisplay.innerText = totalStock;
    }
};
const initializeSearchAndFilter = () => {
    const searchInput = document.getElementById('product-search');
    const clearSearchBtn = document.getElementById('clear-search'); 
    if (!searchInput) return; 
    const toggleClearButton = () => {
        if (!clearSearchBtn) return; 
        if (searchInput.value) {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }
    };
    toggleClearButton();
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        toggleClearButton();
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = e.target.value.trim();
            const currentUrl = new URL(window.location);
            if (searchTerm) {
                currentUrl.searchParams.set('search', searchTerm);
            } else {
                currentUrl.searchParams.delete('search');
            }
            currentUrl.searchParams.delete('offset');
            window.location.href = currentUrl.href;
        }, 500); 
    });
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input', { bubbles: true })); 
            searchInput.focus();
        });
    }
};
const initializeKeyboardShortcuts = () => {
    const shortcuts = {
        'ctrl+k': () => document.getElementById('product-search')?.focus(),
        'esc': () => {
            const searchInput = document.getElementById('product-search');
            if (searchInput && document.activeElement === searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.blur();
            }
        }
    };
    document.addEventListener('keydown', (e) => {
        const key = (e.ctrlKey || e.metaKey ? 'ctrl+' : '') + e.key.toLowerCase();
        if (shortcuts[key]) {
            e.preventDefault();
            shortcuts[key]();
        }
    });
};
const handleFormSubmit = async (event, form, apiFunction, actionType) => {
    event.preventDefault();
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    const inputField = form.querySelector('input[type="number"]');
    if (inputField && (inputField.value === '' || inputField.value < 0)) {
        Swal.fire({
            icon: 'warning',
            title: 'Input Tidak Valid',
            text: 'Mohon masukkan nilai yang valid (minimal 0)',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        inputField.focus();
        return;
    }
    submitButton.innerHTML = '<span class="loading-spinner"></span>';
    submitButton.disabled = true;
    submitButton.style.opacity = '0.7';
    submitButton.style.cursor = 'not-allowed';
    try {
        const formData = new FormData(form);
        const data = await apiFunction(formData);
        if (data.success) {
            submitButton.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
            submitButton.classList.add('success');
            submitButton.style.opacity = '1';
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            if (inputField) {
                inputField.style.transition = 'all 0.3s ease';
                inputField.style.transform = 'scale(0.95)';
                inputField.style.opacity = '0.5';
                setTimeout(() => {
                    inputField.value = '';
                    inputField.style.transform = 'scale(1)';
                    inputField.style.opacity = '1';
                }, 200);
                inputField.blur();
            }
            const modelId = form.dataset.modelId;
            const itemId = form.dataset.itemId;
            const uniqueId = modelId || itemId;
            if (data.hasOwnProperty('new_stock')) {
                const stockDisplay = document.getElementById(`stock-display-${uniqueId}`);
                if (stockDisplay) {
                    stockDisplay.style.transition = 'all 0.3s ease';
                    stockDisplay.style.transform = 'scale(1.3)';
                    stockDisplay.style.color = '#1e40af';
                    stockDisplay.style.fontWeight = '700';
                    setTimeout(() => {
                        stockDisplay.innerText = data.new_stock;
                        if (form.dataset.modelId) {
                            updateTotalStock(form);
                        }
                    }, 150);
                    setTimeout(() => {
                        stockDisplay.style.transform = 'scale(1)';
                        stockDisplay.style.fontWeight = '600';
                    }, 300);
                }
            }
            if (data.hasOwnProperty('new_price')) {
                const formattedPrice = new Intl.NumberFormat('id-ID').format(data.new_price);
                const priceDisplay = document.getElementById(`price-display-${uniqueId}`);
                if (priceDisplay) {
                    priceDisplay.style.transition = 'all 0.3s ease';
                    priceDisplay.style.transform = 'scale(1.3)';
                    priceDisplay.style.color = '#15803d';
                    priceDisplay.style.fontWeight = '700';
                    setTimeout(() => {
                        priceDisplay.innerText = formattedPrice;
                    }, 150);
                    setTimeout(() => {
                        priceDisplay.style.transform = 'scale(1)';
                        priceDisplay.style.fontWeight = '600';
                    }, 300);
                }
                if(form.dataset.modelId) { 
                    updatePriceRange(form);
                }
            }
            setTimeout(() => {
                submitButton.innerHTML = originalButtonText;
                submitButton.classList.remove('success');
            }, 2000);
        } else {
            throw new Error(data.message || 'Terjadi kesalahan');
        }
    } catch (error) {
        submitButton.innerHTML = '<i class="fas fa-times"></i> Gagal';
        submitButton.classList.add('error');
        submitButton.style.opacity = '1';
        const errorMessage = error.message || 'Terjadi kesalahan. Silakan coba lagi.';
        const parts = errorMessage.split('\n\nPesan Teknis:');
        const mainMessage = parts[0].replace(/\n/g, '<br>'); 
        const technicalDetails = parts.length > 1 ? parts[1] : '';
        let alertHtml = `<div class="text-left text-gray-700">${mainMessage}</div>`;
        if (technicalDetails) {
            alertHtml += `
                <details class="mt-4">
                    <summary class="cursor-pointer text-sm font-semibold text-gray-600 hover:text-gray-800">
                        <i class="fas fa-code mr-1"></i> Detail Teknis
                    </summary>
                    <pre class="mt-2 bg-gray-100 p-3 rounded-lg text-xs text-gray-600 overflow-auto" style="white-space: pre-wrap; word-break: break-all;"><code>${technicalDetails.trim()}</code></pre>
                </details>
            `;
        }
        console.error("Error:", error);
        Swal.fire({
            icon: 'error',
            title: 'Update Gagal!', 
            html: alertHtml,       
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Tutup',
            showClass: {
                popup: 'animate__animated animate__shakeX'
            }
        });
        if (inputField) {
            inputField.style.animation = 'shake 0.5s';
            setTimeout(() => {
                inputField.style.animation = '';
            }, 500);
        }
        setTimeout(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.classList.remove('error');
        }, 2500);
    } finally {
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.style.opacity = '1';
            submitButton.style.cursor = 'pointer';
        }, 2000);
    }
};
const handleSyncWithConfirmation = async (event, form) => {
    event.preventDefault();
    const sku = form.querySelector('input[name="sku"]').value;
    const result = await Swal.fire({
        title: 'Konfirmasi Sync Stok',
        html: `
            <div class="text-left text-gray-700">
                <p class="mb-2">Anda akan menyamakan stok Shopee dengan stok database untuk:</p>
                <div class="bg-gray-100 p-3 rounded-lg mt-3">
                    <p class="font-mono text-sm"><strong>SKU:</strong> ${sku}</p>
                </div>
                <p class="mt-3 text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i> Stok di Shopee akan diubah mengikuti stok di database.
                </p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#8b5cf6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i> Ya, Sync Sekarang',
        cancelButtonText: 'Batal',
        reverseButtons: true
    });
    if (result.isConfirmed) {
        await handleFormSubmit(event, form, syncStock, 'sync');
    }
};
const handleSyncAllClick = async (event) => {
  const btn = event.currentTarget;
  const originalHtml = btn.innerHTML;
  const totalCount = btn.dataset.totalCount || 'semua';
  const result = await Swal.fire({
    title: `Konfirmasi Sync Total`,
    html: `Anda akan menyinkronkan stok untuk <strong>${totalCount}</strong> produk.<br><br>Stok di Shopee akan di-update massal sesuai dengan stok di database (berdasarkan SKU). Proses ini mungkin memakan waktu.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#4f46e5',
    cancelButtonColor: '#6b7280',
    confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i> Ya, Sync Semuanya!',
    cancelButtonText: 'Batal',
    reverseButtons: true
  });
  if (!result.isConfirmed) return;
  btn.innerHTML = '<span class="loading-spinner"></span> Menyinkronkan...';
  btn.disabled = true;
  Swal.fire({
    title: 'Sinkronisasi Dimulai...',
    html: `Memproses ${totalCount} item. Harap tunggu...<br><br>Jangan tutup halaman ini. Server sedang mengambil semua data produk Anda.`,
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
  try {
    const data = {}; 
    const response = await syncAllStock(data);
    if (response.success) {
      let failedDetailsHtml = '';
      if (response.failed > 0 && response.failed_details.length > 0) {
          failedDetailsHtml = `
            <details class="mt-4">
              <summary class="cursor-pointer text-sm font-semibold text-gray-600 hover:text-gray-800">
                <i class="fas fa-code mr-1"></i> Lihat Detail Kegagalan
              </summary>
              <pre class="mt-2 bg-gray-100 p-3 rounded-lg text-xs text-left text-gray-600 overflow-auto" style="white-space: pre-wrap; word-break: break-all; max-height: 150px;"><code>${response.failed_details.join('\n')}</code></pre>
            </details>
          `;
      }
      let skippedDetailsHtml = ''; 
      if (response.skipped > 0 && response.skipped_details && response.skipped_details.length > 0) {
        skippedDetailsHtml = `
          <details class="mt-4">
            <summary class="cursor-pointer text-sm font-semibold text-gray-600 hover:text-gray-800">
              <i class="fas fa-code mr-1"></i> Lihat Detail Item Dilewati
            </summary>
            <pre class="mt-2 bg-gray-100 p-3 rounded-lg text-xs text-left text-gray-600 overflow-auto" style="white-space: pre-wrap; word-break: break-all; max-height: 150px;"><code>${response.skipped_details.join('\n')}</code></pre>
          </details>
        `;
      }
      Swal.fire({
        title: 'Sinkronisasi Selesai!',
        html: `
          <div class="text-left space-y-2">
            <p><strong><i class="fas fa-cubes text-blue-500"></i> Total Produk/Variasi ditemukan:</strong> ${response.total_items_found} item</p>
            <p><strong><i class="fas fa-check-circle text-green-500"></i> Berhasil disinkronkan:</strong> ${response.synced} item</p>
            <p><strong><i class="fas fa-times-circle text-red-500"></i> Gagal disinkronkan:</strong> ${response.failed} item</p>
            <p><strong><i class="fas fa-minus-circle text-gray-500"></i> Dilewati (SKU N/A / Stok Sama / Tdk di DB):</strong> ${response.skipped} item</p>
          </div>
          ${skippedDetailsHtml}  
          ${failedDetailsHtml}   
          <p class="mt-4">Halaman akan dimuat ulang untuk menampilkan stok terbaru...</p>
        `,
        icon: 'success',
        allowOutsideClick: false,
        confirmButtonText: 'Muat Ulang Halaman',
      }).then(() => {
        location.reload();
      });
    } else {
      throw new Error(response.message || 'Gagal menyinkronkan data.');
    }
  } catch (error) {
    console.error('Sync All Error:', error);
    Swal.fire({
      title: 'Error!',
      text: `Terjadi kesalahan: ${error.message}`,
      icon: 'error'
    });
  } finally {
    btn.innerHTML = originalHtml;
    btn.disabled = false;
  }
};
const enhanceInputFields = () => {
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.01)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const submitBtn = this.closest('form').querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.click();
                }
            }
        });
        input.addEventListener('blur', function() {
            if (this.value !== '') {
                const val = parseInt(this.value);
                if (!isNaN(val)) {
                    this.value = val;
                }
            }
        });
    });
};
const showKeyboardHint = (message) => {
    const existingHint = document.querySelector('.keyboard-hint');
    if (existingHint) existingHint.remove();
    const hint = document.createElement('div');
    hint.className = 'keyboard-hint';
    hint.innerHTML = `<i class="fas fa-keyboard mr-1"></i> ${message}`;
    document.body.appendChild(hint);
    setTimeout(() => hint.classList.add('show'), 10);
    setTimeout(() => {
        hint.classList.remove('show');
        setTimeout(() => hint.remove(), 300);
    }, 2000);
};
const scrollToElement = (element) => {
    if (!element) return;
    const yOffset = -100;
    const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({
        top: y,
        behavior: 'smooth'
    });
};
const initializeAutosave = () => {
    const forms = document.querySelectorAll('.update-stock-form, .update-price-form');
    forms.forEach(form => {
        const input = form.querySelector('input[type="number"]');
        if (!input) return;
        const formId = form.dataset.modelId || form.dataset.itemId;
        const formType = form.classList.contains('update-stock-form') ? 'stock' : 'price';
        const storageKey = `shopee_draft_${formType}_${formId}`;
        const savedValue = sessionStorage.getItem(storageKey);
        if (savedValue) {
            input.value = savedValue;
            input.style.background = '#fef3c7';
            setTimeout(() => {
                input.style.background = 'white';
            }, 1000);
        }
        input.addEventListener('input', () => {
            if (input.value) {
                sessionStorage.setItem(storageKey, input.value);
            } else {
                sessionStorage.removeItem(storageKey);
            }
        });
        form.addEventListener('submit', () => {
            setTimeout(() => {
                sessionStorage.removeItem(storageKey);
            }, 1000);
        });
    });
};
const addShakeAnimation = () => {
    if (!document.getElementById('shake-animation-style')) {
        const style = document.createElement('style');
        style.id = 'shake-animation-style';
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
    }
};

const handleManageStokOlClick = (event) => {
    const button = event.currentTarget;
    const { mode, sku, plu, descp, vendor, hrgBeli, price } = button.dataset;
    const kd_store = '9998'; 
    let title = '';
    let receiptHtml = ''; 
    let qtyHtml = '';     
    if (mode === 'add') {
        title = 'Masukkan ke Stok Online';
        receiptHtml = '';
    } else { 
        title = 'Edit / Tambah Stok Online';
        qtyHtml = ''; 
        receiptHtml = `
            <h3 class="text-left text-green-600 font-semibold col-span-2 mt-4 border-b">Data Penerimaan Baru</h3>
            <label for="swal-qty_rec">QTY Diterima (REC):</label>
            <input type="number" id="swal-qty_rec" class="swal2-input" value="0" placeholder="Jumlah QTY yang diterima/masuk">
            <label for="swal-no_lpb">No LPB:</label>
            <input type="text" id="swal-no_lpb" class="swal2-input" value="">
            <label for="swal-kode_supp">Kode Supplier:</label>
            <input type="text" id="swal-kode_supp" class="swal2-input" value="${vendor}">
            <label for="swal-avg_cost">Avg Cost:</label>
            <input type="number" id="swal-avg_cost" class="swal2-input" value="0" placeholder="0">
            <label for="swal-ppn">PPN:</label>
            <input type="number" id="swal-ppn" class="swal2-input" value="0" placeholder="0">
            <label for="swal-netto">Netto:</label>
            <input type="number" id="swal-netto" class="swal2-input" value="0" placeholder="0">
            <label for="swal-net_price">Net Price:</label>
            <input type="number" id="swal-net_price" class="swal2-input" value="0" placeholder="0">
            <label for="swal-admin_s">Admin S:</label>
            <input type="number" id="swal-admin_s" class="swal2-input" value="0" placeholder="0">
            <label for="swal-ongkir">Ongkir:</label>
            <input type="number" id="swal-ongkir" class="swal2-input" value="0" placeholder="0">
            <label for="swal-promo">Promo:</label>
            <input type="number" id="swal-promo" class="swal2-input" value="0" placeholder="0">
            <label for="swal-biaya_psn">Biaya Pesan:</label>
            <input type="number" id="swal-biaya_psn" class="swal2-input" value="0" placeholder="0">
        `;
    }
    const modalHtml = `
        <form id="stok-ol-form" class="swal-form-grid">
            <p class="text-left text-sm text-gray-800 font-bold col-span-2">SKU: ${sku}</p>
            <h3 class="text-left text-blue-600 font-semibold col-span-2 mt-2 border-b">Info Stok Online</h3>
            <label for="swal-kd_store">KD Store:</label>
            <input type="text" id="swal-kd_store" class="swal2-input" value="${kd_store}" readonly>
            <label for="swal-plu">PLU:</label>
            <input type="text" id="swal-plu" class="swal2-input" value="${plu}" placeholder="PLU Produk">
            <label for="swal-descp">Deskripsi:</label>
            <input type="text" id="swal-descp" class="swal2-input" value="${descp}" placeholder="Deskripsi Produk">
            <label for="swal-vendor">Vendor:</label>
            <input type="text" id="swal-vendor" class="swal2-input" value="${vendor}" placeholder="Vendor">
            <label for="swal-hrg_beli">Harga Beli:</label>
            <input type="number" id="swal-hrg_beli" class="swal2-input" placeholder="0">
            <label for="swal-price">Harga Jual:</label>
            <input type="number" id="swal-price" class="swal2-input"  placeholder="0">
            ${qtyHtml}
            ${receiptHtml}
        </form>
    `;
    Swal.fire({
        title: title,
        html: modalHtml,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#4f46e5',
        focusConfirm: false,
        preConfirm: () => {
            const formData = new FormData();
            formData.append('mode', mode);
            formData.append('sku', sku); 
            formData.append('kd_store', kd_store);
            formData.append('plu', document.getElementById('swal-plu').value);
            formData.append('descp', document.getElementById('swal-descp').value);
            formData.append('vendor', document.getElementById('swal-vendor').value);
            formData.append('hrg_beli', document.getElementById('swal-hrg_beli').value);
            formData.append('price', document.getElementById('swal-price').value);
            if (mode === 'add') {
                formData.append('qty_rec', 0);
                formData.append('no_lpb', ''); 
                formData.append('avg_cost', 0);
                formData.append('ppn', 0);
                formData.append('netto', 0);
            } else { 
                const qty_rec_val = parseFloat(document.getElementById('swal-qty_rec').value) || 0;
                const no_lpb_val = document.getElementById('swal-no_lpb').value.trim();
                if (qty_rec_val > 0 && !no_lpb_val) {
                    Swal.showValidationMessage('No LPB wajib diisi jika QTY Diterima > 0');
                    return false;
                }
                if (qty_rec_val <= 0 && no_lpb_val) {
                    Swal.showValidationMessage('QTY Diterima tidak boleh 0 jika No LPB diisi');
                    return false;
                }
                formData.append('qty_rec', qty_rec_val);
                formData.append('no_lpb', no_lpb_val); 
                formData.append('kode_supp', document.getElementById('swal-kode_supp').value);
                formData.append('avg_cost', document.getElementById('swal-avg_cost').value);
                formData.append('ppn', document.getElementById('swal-ppn').value);
                formData.append('netto', document.getElementById('swal-netto').value);
                formData.append('net_price', document.getElementById('swal-net_price').value);
                formData.append('admin_s', document.getElementById('swal-admin_s').value);
                formData.append('ongkir', document.getElementById('swal-ongkir').value);
                formData.append('promo', document.getElementById('swal-promo').value);
                formData.append('biaya_psn', document.getElementById('swal-biaya_psn').value);
            }
            return formData;
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Data sedang diproses, harap tunggu.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            try {
                const response = await manageStokOl(formData);
                if (!response.success) {
                    throw new Error(response.message);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                const mode = formData.get('mode');
                if (mode === 'add') {
                    button.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    button.style.opacity = '0';
                    button.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        button.remove();
                    }, 300);

                    const variantCard = button.closest('.variant-card');
                    if (variantCard) {
                        variantCard.style.transition = 'background-color 0.3s ease';
                        variantCard.style.backgroundColor = '#ffeaf0';
                    } else {
                        const productCard = button.closest('.product-card');
                        if (productCard) {
                            productCard.style.transition = 'background-color 0.3s ease';
                            productCard.style.backgroundColor = '#ffeaf0';
                        }
                    }
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    html: `<p>Terjadi kesalahan: ${error.message}</p>`
                });
            }
        }
    });
};
document.addEventListener('DOMContentLoaded', () => {
    addShakeAnimation();
    initializeSearchAndFilter();
    initializeKeyboardShortcuts();
    setTimeout(() => {
        showKeyboardHint('Tekan Ctrl+K untuk mencari produk');
    }, 1000);
    enhanceInputFields();
    initializeAutosave();
    document.querySelectorAll('.update-stock-form').forEach(form => {
        form.addEventListener('submit', (event) => handleFormSubmit(event, form, updateStock, 'stock'));
    });
    document.querySelectorAll('.update-price-form').forEach(form => {
        form.addEventListener('submit', (event) => handleFormSubmit(event, form, updatePrice, 'price'));
    });
    document.querySelectorAll('.sync-stock-form').forEach(form => {
        form.addEventListener('submit', (event) => handleSyncWithConfirmation(event, form));
    });
    document.querySelectorAll('.btn-manage-stok-ol').forEach(button => {
        if (button.disabled) return;
        button.addEventListener('click', (event) => handleManageStokOlClick(event));
    });
    const syncAllBtn = document.getElementById('sync-all-stock-btn');
    if (syncAllBtn) {
        syncAllBtn.addEventListener('click', handleSyncAllClick);
    }
    const profileImg = document.getElementById("profile-img");
    const profileCard = document.getElementById("profile-card");
    if (profileImg && profileCard) {
        profileImg.addEventListener("click", function (event) {
            event.preventDefault();
            profileCard.classList.toggle("show");
        });
        document.addEventListener("click", function (event) {
            if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
                profileCard.classList.remove("show");
            }
        });
    }
    document.body.classList.add('loaded');
});