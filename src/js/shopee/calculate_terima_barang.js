export function unformatRupiah(value) {
    if (typeof value !== 'string') {
        value = String(value);
    }
    return value.replace(/\./g, '').replace(/,/g, '.');
}
