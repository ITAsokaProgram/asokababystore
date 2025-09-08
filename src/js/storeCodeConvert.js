let storeCode = "";
let cachedChartData = null;
let cachedChartMode = null;
let chartHistoryStack = [];
const storeCodes = {
    'ABIN': '1502',
    'ACE': '1505',
    'ACIB': '1379',
    'ACIL': '1504',
    'ACIN': '1641',
    'ACSA': '1902',
    'ADET': '1376',
    'ADMB': '3190',
    'AHA': '1506',
    'AHIN': '2102',
    'ALANG': '1503',
    'ANGIN': '2102',
    'APEN': '1908',
    'APIK': '3191',
    'APRS': '1501',
    'ARAW': '1378',
    'ARUNG': '1611',
    'ASIH': '2104',
    'ATIN': '1642',
    'AWIT': '1377',
    'AXY': '2103',
};

const allCabang = Object.values(storeCodes);

// Event listener ketika cabang berubah
$('#cabang').on('change', function () {
    const selectedBranch = $(this).val(); // Ambil nilai cabang yang dipilih
    if (selectedBranch === 'SEMUA CABANG') {
        storeCode = allCabang.join(',')
    } else {
        storeCode = storeCodes[selectedBranch] || ''; // Set nilai input kode_store
    }
});
// Trigger event saat halaman dimuat untuk mengisi nilai awal
$('#cabang').trigger('change');