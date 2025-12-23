import { getItemHistory } from './api_service.js';

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('itemHistoryModal');
    const closeButton = document.getElementById('closeHistoryModal');
    const modalItemName = document.getElementById('modalItemName');
    const modalBody = document.getElementById('modalBodyContent');

    if (!modal || !closeButton || !modalItemName || !modalBody) {
        console.warn('Elemen modal history tidak ditemukan. Pastikan HTML modal ada di halaman.');
        return;
    }

    document.body.addEventListener('click', (e) => {
        const target = e.target.closest('.open-history-modal');
        if (target) {
            const plu = target.dataset.plu;
            const descp = target.dataset.descp;

            if (!plu) return;

            modalItemName.textContent = descp || '...';
            modalBody.innerHTML = '<p class="text-center text-gray-500">Memuat data...</p>';
            modal.style.display = 'flex';

            fetchHistoryData(plu);
        }
    });

    closeButton.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

async function fetchHistoryData(plu) {
    const modalBody = document.getElementById('modalBodyContent');
    
    try {
        const data = await getItemHistory(plu);

        if (data.success && data.history.length > 0) {
            renderHistory(data.history);
        } else if (data.success) {
            modalBody.innerHTML = '<p class="text-center text-gray-500">Tidak ada history penerimaan ditemukan untuk item ini.</p>';
        } else {
            throw new Error(data.message || 'Gagal mengambil data.');
        }

    } catch (error) {
        console.error('Error fetching history:', error);
        const errorMessage = (error.message && (error.message.includes('Token') || error.message.includes('format salah')))
            ? 'Sesi tidak valid atau berakhir. Silakan login ulang.'
            : (error.message || 'Gagal memuat data.');
            
        modalBody.innerHTML = `<p class="text-center text-red-500">Error: ${errorMessage}</p>`;
    }
}

function renderHistory(history) {
    const modalBody = document.getElementById('modalBodyContent');
    let tableHtml = `
        <table class="min-w-full w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="py-3 px-4">Tanggal</th>
                    <th scope="col" class="py-3 px-4">No. Faktur</th>
                    <th scope="col" class="py-3 px-4">No. LPB</th>
                    <th scope="col" class="py-3 px-4 text-right">Qty</th>
                    <th scope="col" class="py-3 px-4 text-right">Hrg. Beli</th>
                    <th scope="col" class="py-3 px-4">Kasir</th>
                </tr>
            </thead>
            <tbody class="bg-white">
    `;

    history.forEach(item => {
        const date = new Date(item.tgl_pesan);
        const formattedDate = date.toLocaleDateString('id-ID', {
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit'
        }).replace('.', ':');

        const qty = parseFloat(item.QTY_REC).toLocaleString('id-ID');
        const hrg_beli = parseFloat(item.hrg_beli).toLocaleString('id-ID', { minimumFractionDigits: 0 });

        tableHtml += `
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4 whitespace-nowrap">${formattedDate}</td>
                <td class="py-3 px-4 font-medium">${item.no_faktur}</td>
                <td class="py-3 px-4">${item.no_lpb}</td>
                <td class="py-3 px-4 text-right font-semibold text-blue-600">${qty}</td>
                <td class="py-3 px-4 text-right">Rp ${hrg_beli}</td>
                <td class="py-3 px-4">${item.kode_kasir}</td>
            </tr>
        `;
    });

    tableHtml += '</tbody></table>';
    modalBody.innerHTML = tableHtml;
}