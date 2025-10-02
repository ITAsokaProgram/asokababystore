import getCookie from "../utils/cookies.js";

const token = getCookie('token');

export const getReviewConversation = async (reviewId) => {
    try {
        
        const response = await fetch(`/src/api/customer/get_review_conversation.php?review_id=${reviewId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        return await response.json();
    } catch (error) {
        console.error('Error getting conversation:', error);
        return { success: false, message: 'Gagal menghubungi server.' };
    }
}


export const sendReviewMessage = async (reviewId, pesan) => {
    try {
        
        const response = await fetch('/src/api/review/send_customer_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ review_id: reviewId, pesan })
        });
        return await response.json();
    } catch (error) {
        console.error('Error sending message:', error);
        throw error;
    }
}