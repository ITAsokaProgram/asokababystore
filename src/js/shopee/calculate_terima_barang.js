export function unformatRupiah(value) {
    if (typeof value !== 'string') {
        value = String(value);
    }
    return value.replace(/\./g, '').replace(/,/g, '.');
}

export function calculateReceiveForm(hargaBeli, price) {
    hargaBeli = parseFloat(hargaBeli) || 0;
    price = parseFloat(price) || 0;

    const ppn = hargaBeli * 0.11;
    const netto = hargaBeli + ppn;
    const admin_s = hargaBeli * 0.01;
    const ongkir = price * 0.12; 
    const promo = hargaBeli * 0.005;
    const biayaPesanan = 150; 
    
    const avg_cost = netto + admin_s + ongkir + biayaPesanan - promo;
    const net_price = avg_cost;
    
    const harga_rekomendasi = avg_cost; 

    return {
        ppn,
        netto,
        admin_s,
        ongkir,
        promo,
        biayaPesanan,
        avg_cost,
        net_price,
        harga_rekomendasi
    };
}