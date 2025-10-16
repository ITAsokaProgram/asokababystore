function getCookie(name) {
    let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    if (match) return match[2];
    return null;
}

const token = getCookie('admin_token') || localStorage.getItem('token');

if (token) {
    fetch('/src/auth/decode_token', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const userRole = data.data.role;
            document.getElementById('user-nama').textContent = data.data.nama;
            document.getElementById('user-role').textContent = userRole;
            document.getElementById('user-nama-dropdown').textContent = data.data.nama;
            document.getElementById('user-role-dropdown').textContent = userRole;
        } else {
            console.error('Gagal mengambil user:', data.message);
        }
    })
    .catch(err => console.error('Fetch error:', err));
}
