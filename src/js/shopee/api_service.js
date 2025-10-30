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
const sendRequestGET = async (url) => {
  try {
    const token = getToken();
    const headers = new Headers({
      'Accept': 'application/json'
    });
    if (token) {
      headers.append('Authorization', `Bearer ${token}`);
    }
    const response = await fetch(url, {
      method: 'GET',
      headers: headers
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
export const bulkTerimaBarang = (data) => {
  return sendRequestJSON('/src/api/shopee/bulk_terima_barang.php', data);
};
export const getItemHistory = (plu) => {
  const url = `/src/api/shopee/get_item_history.php?plu=${encodeURIComponent(plu)}`;
  return sendRequestGET(url);
};
export const getTempReceiptItems = () => {
    return sendRequestGET('/src/api/shopee/temp_receipt_handler.php?action=get');
};

export const addTempReceiptItem = (itemData) => {
    return sendRequestJSON('/src/api/shopee/temp_receipt_handler.php?action=add', { item: itemData });
};

export const updateTempReceiptItem = (plu, qty_rec, hrg_beli, price) => {
    const data = { plu, qty_rec, hrg_beli, price };
    return sendRequestJSON('/src/api/shopee/temp_receipt_handler.php?action=update', data);
};

export const deleteTempReceiptItems = (plus) => {
    return sendRequestJSON('/src/api/shopee/temp_receipt_handler.php?action=delete', { plus });
};

export const deleteAllTempReceiptItems = () => {
    return sendRequestJSON('/src/api/shopee/temp_receipt_handler.php?action=delete_all', {});
};

export const saveTempReceipt = (no_lpb) => {
    return sendRequestJSON('/src/api/shopee/temp_receipt_handler.php?action=save', { no_lpb });
};
export const addTempReceiptItemByPlu = (plu, vendor) => {
    return sendRequestJSON('/src/api/shopee/temp_receipt_handler.php?action=add_by_plu', { plu, vendor });
};

export const syncAllProductsToDb = (data) => {
  return sendRequestJSON('/src/api/shopee/sync_all_products_to_db.php', data);
};
export const syncAllProductsToRedis = (data) => {
  return sendRequestJSON('/src/api/shopee/sync_all_products_to_redis.php', data);
};
export const deleteStokOlItem = (plu, kd_store) => {
    return sendRequestJSON('/src/api/shopee/delete_stok_ol.php', { plu, kd_store });
};