export const fetchTransaksi = async (token, hp) => {
    const response = await fetch(`/src/api/customer/history_transaction`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ kode: hp })
    });
    if (response.status === 401) {
        window.location.href = '/src/component/error_token'
    } else {
        const data = await response.json();
        return data;
    }
}

export default fetchTransaksi;