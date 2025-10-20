
const handleResponse = async (response) => {
    if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: 'An unknown error occurred' }));
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }
    return response.json();
};

const sendRequest = async (url, formData) => {
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        return handleResponse(response);
    } catch (error) {
        console.error('Fetch error:', error);
        throw error;
    }
};

export const updateStock = (formData) => {
    return sendRequest('/src/api/shopee/update_stock.php', formData);
};

export const updatePrice = (formData) => {
    return sendRequest('/src/api/shopee/update_price.php', formData);
};

export const syncStock = (formData) => {
    console.log('Syncing stock with formData:', formData);
    return sendRequest('/src/api/shopee/sync_stock.php', formData);
};