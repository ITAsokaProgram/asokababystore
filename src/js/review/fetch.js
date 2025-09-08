import getCookie from "../index/utils/cookies.js";

export const getReviewData = async () => {
  try {
    const token = getCookie('token');
    const response = await fetch(`/src/api/customer/review_laporan_in`, {
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
      return { data: [] }; 
    } else if (response.status === 500) {
      throw new Error("Server Error");
    }
  } catch (error) {
    console.error("Error fetching review data:", error);
    throw error;
  }
}

export default {getReviewData};