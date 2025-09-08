export const fetchUser = async (token) => {
    const response = await fetch(`/src/auth/verify_token_pubs?token=${token}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    });
    if(!response.ok) {
        throw new Error('Failed to fetch user data');
    } else {
        const data = await response.json();
        return data;
    }
}

export const fetchStatusUser = async (token) => {
    const response = await fetch(`/src/api/customer/get_status_customer`, {
        method: 'GET',
        headers: {
            'Content-Type' : 'application/json',
            'Authorization' : `Bearer ${token}`
        },
    });
    if(response.status === 401){
        window.location.href = '/src/component/error_token'
    } else {
        const data = await response.json();
        return data;
    }
}


export const fetchPointUser = async (token,hp) => {
    const response = await fetch('/src/api/customer/get_poin_customer', {
        method: "POST",
        headers : {
            'Content-Type' : 'application/json',
            'Authorization' : `Bearer ${token}`
        },
        body: JSON.stringify({kode : hp})
    })
    if(response.status === 401) {
        window.location.href = '/src/component/error_token'
    } else {
        const data = await response.json()
        return data
    }
}
export default {fetchUser, fetchStatusUser, fetchPointUser};