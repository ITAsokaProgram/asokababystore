function getToken() {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; admin_token=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}
const handleResponse = async (response) => {
  if (!response.ok) {
    let errorData;
    try {
      errorData = await response.json();
    } catch (e) {
      throw new Error(errorData.message || `HTTP error! status: ${response.status} - ${response.statusText}`);
    }
    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
  }
  return response.json();
};
const sendRequest = async (url, formData) => {
  try {
    const token = getToken();
    const headers = new Headers();
    if (token) {
      headers.append('Authorization', `Bearer ${token}`);
    }
    const response = await fetch(url, {
      method: 'POST',
      headers: headers, 
      body: formData
    });
    return handleResponse(response);
  } catch (error) {
    console.error('Fetch error:', error);
    throw error;
  }
};
const sendRequestJSON = async (url, dataObject) => {
  try {
    const token = getToken();
    const headers = new Headers({
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    });
    if (token) {
      headers.append('Authorization', `Bearer ${token}`);
    }
    const response = await fetch(url, {
      method: 'POST',
      headers: headers, 
      body: JSON.stringify(dataObject)
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
  return sendRequest('/src/api/shopee/sync_stock.php', formData);
};
export const syncAllStock = (data) => {
  return sendRequestJSON('/src/api/shopee/sync_all_stock.php', data);
};
export const manageStokOl = (formData) => {
  return sendRequest('/src/api/shopee/manage_stok_ol.php', formData);
};