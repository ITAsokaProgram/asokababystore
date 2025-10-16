import getCookie from "../index/utils/cookies.js";

const token = getCookie('customer_token');

const handleResponse = async (response) => {
    if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: 'Terjadi kesalahan' }));
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }
    return response.json();
};

export const submitContactUs = async (formData) => {
    try {
        const response = await fetch('/src/api/customer/contact_us/submit_contact_us.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(formData)
        });
        return handleResponse(response);
    } catch (error) {
        console.error('Error submitting contact form:', error);
        throw error;
    }
};

export const getContactHistory = async () => {
    try {
        const response = await fetch('/src/api/customer/contact_us/get_contact_us_history.php', {
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}` }
        });
        return handleResponse(response);
    } catch (error) {
        console.error('Error fetching contact history:', error);
        throw error;
    }
};

export const getConversation = async (contactUsId) => {
    try {
        const response = await fetch(`/src/api/customer/contact_us/get_contact_us_conversation.php?contact_us_id=${contactUsId}`, {
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}` }
        });
        return handleResponse(response);
    } catch (error) {
        console.error('Error fetching conversation:', error);
        throw error;
    }
};

export const sendMessage = async (contactUsId, pesan) => {
    try {
        const response = await fetch('/src/api/customer/contact_us/send_contact_us_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ contact_us_id: contactUsId, pesan: pesan })
        });
        return handleResponse(response);
    } catch (error) {
        console.error('Error sending message:', error);
        throw error;
    }
};