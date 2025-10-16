import getCookie from "../index/utils/cookies.js";

export const getReviewData = async (page = 1, limit = 10, rating = 'all', status = 'all') => { 
  try {
    const token = getCookie('admin_token');
    
    
    const url = `/src/api/customer/review_laporan_in?page=${page}&limit=${limit}&rating=${rating}&status=${status}`; 
    
    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token}`,
      },
    });

    if (response.status === 200) {
      const data = await response.json();
      return data;
    } else if (response.status === 204) {
      return { data: [], pagination: { total_records: 0, total_pages: 1, current_page: 1 } };
    } else if (response.status === 500) {
      throw new Error("Server Error");
    }
  } catch (error) {
    console.error("Error fetching review data:", error);
    throw error;
  }
}



export const submitReviewHandling = async (reviewData) => {
  try {
    const token = getCookie('admin_token');
    
    const response = await fetch('/src/api/customer/save_review_detail.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(reviewData)
    });

    const result = await response.json();
    
    if (response.ok && result.success) {
      return result;
    } else {
      
      const errorMessage = `${result.message || 'Gagal menyimpan data.'} (Detail: ${result.error_detail || 'Tidak ada detail.'})`;
      throw new Error(errorMessage);
    }
  } catch (error) {
    console.error('Error submitting review handling:', error);
    throw error;
  }
}

export const getReviewDetail = async (reviewId) => {
    try {
        const token = getCookie('admin_token');
        
        
        const response = await fetch(`/src/api/customer/get_review_detail.php?review_id=${reviewId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
                
            }
        });

        const result = await response.json();

        
        return result;

    } catch (error) {
        console.error('Error getting review detail:', error);
        
        return { success: false, message: 'Gagal menghubungi server.', error_detail: error.message };
    }
}
export const getReviewConversation = async (reviewId) => {
    try {
        const token = getCookie('admin_token');
        
        const response = await fetch(`/src/api/customer/get_review_conversation.php?review_id=${reviewId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const result = await response.json();
        return result;

    } catch (error) {
        console.error('Error getting conversation:', error);
        return { success: false, message: 'Gagal mengambil percakapan', error_detail: error.message };
    }
}

export const sendReviewMessage = async (reviewId, pesan) => {
    try {
        const token = getCookie('admin_token');
        
        const response = await fetch('/src/api/customer/send_review_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ review_id: reviewId, pesan })
        });

        const result = await response.json();
        return result;

    } catch (error) {
        console.error('Error sending message:', error);
        throw error;
    }
}

export default { getReviewData, submitReviewHandling, getReviewDetail, getReviewConversation, sendReviewMessage };

