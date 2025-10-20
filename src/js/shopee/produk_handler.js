
import { updateStock, updatePrice, syncStock } from './api_service.js';
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

document.addEventListener('DOMContentLoaded', () => {

    const handleFormSubmit = async (event, form, apiFunction) => {
        event.preventDefault();
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        submitButton.innerHTML = '<span class="loading-spinner"></span>';
        submitButton.disabled = true;
        submitButton.style.opacity = '0.7';
        submitButton.style.cursor = 'not-allowed';

        try {
            const formData = new FormData(form);
            const data = await apiFunction(formData);

            if (data.success) {
                submitButton.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                submitButton.style.opacity = '1';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    timerProgressBar: true
                });

                const inputField = form.querySelector('input[type="number"]');
                if (inputField) {
                    inputField.value = '';
                    inputField.blur();
                }
                
                const modelId = form.dataset.modelId;
                const itemId = form.dataset.itemId;
                const uniqueId = modelId || itemId;

                if (data.hasOwnProperty('new_stock')) {
                    const stockDisplay = document.getElementById(`stock-display-${uniqueId}`);
                    if (stockDisplay) {
                        stockDisplay.style.transition = 'all 0.3s ease';
                        stockDisplay.style.transform = 'scale(1.2)';
                        stockDisplay.style.color = '#1e40af';
                        stockDisplay.innerText = data.new_stock;
                        
                        setTimeout(() => {
                            stockDisplay.style.transform = 'scale(1)';
                        }, 300);
                    }
                    
                    if (form.dataset.modelId) {
                        updateTotalStock(form);
                    }
                }
                
                if (data.hasOwnProperty('new_price')) {
                    const formattedPrice = new Intl.NumberFormat('id-ID').format(data.new_price);
                    const priceDisplay = document.getElementById(`price-display-${uniqueId}`);
                    if (priceDisplay) {
                        priceDisplay.style.transition = 'all 0.3s ease';
                        priceDisplay.style.transform = 'scale(1.2)';
                        priceDisplay.style.color = '#15803d';
                        priceDisplay.innerText = formattedPrice;
                        
                        setTimeout(() => {
                            priceDisplay.style.transform = 'scale(1)';
                        }, 300);
                    }
                    
                    if(form.dataset.modelId) { 
                        updatePriceRange(form);
                    }
                }
                
                setTimeout(() => {
                    submitButton.innerHTML = originalButtonText;
                }, 1500);
                
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            submitButton.innerHTML = '<i class="fas fa-times"></i> Gagal';
            submitButton.style.opacity = '1';
            
            const errorMessage = error.message || 'Terjadi kesalahan. Silakan coba lagi.';
            
            const parts = errorMessage.split('\n\nPesan Teknis:');
            const mainMessage = parts[0].replace(/\n/g, '<br>'); 
            const technicalDetails = parts.length > 1 ? parts[1] : '';

            let alertHtml = `<div class="text-left text-gray-700">${mainMessage}</div>`;
            if (technicalDetails) {
                alertHtml += `
                    <div class="mt-4 text-left">
                        <pre class="bg-gray-100 p-3 rounded-lg text-xs text-gray-600" style="white-space: pre-wrap; word-break: break-all;"><code>${technicalDetails.trim()}</code></pre>
                    </div>
                `;
            }
            console.error("error:", error);

            Swal.fire({
                icon: 'error',
                title: 'Update Gagal!', 
                html: alertHtml,       
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Tutup'
            });
            
            setTimeout(() => {
                submitButton.innerHTML = originalButtonText;
            }, 2000);
        } finally {
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.style.opacity = '1';
                submitButton.style.cursor = 'pointer';
            }, 1500);
        }
    };

    document.querySelectorAll('.update-stock-form').forEach(form => {
        form.addEventListener('submit', (event) => handleFormSubmit(event, form, updateStock));
    });

    document.querySelectorAll('.update-price-form').forEach(form => {
        form.addEventListener('submit', (event) => handleFormSubmit(event, form, updatePrice));
    });

    document.querySelectorAll('.sync-stock-form').forEach(form => {
        form.addEventListener('submit', (event) => handleFormSubmit(event, form, syncStock));
    });
    
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });
        
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.01)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
});