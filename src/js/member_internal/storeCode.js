export const storeCode = (idSelect) => {
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
        'AHIN': '2101',
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

    const selectedBranch = document.getElementById(idSelect).value;
    if (selectedBranch === 'SEMUA CABANG') {
        return Object.values(storeCodes); // bisa array
    } else {
        return [storeCodes[selectedBranch] || '']; // return array
    }
}

export default storeCode;