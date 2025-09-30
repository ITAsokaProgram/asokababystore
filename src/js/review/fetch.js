import getCookie from "../index/utils/cookies.js";

export const getReviewData = async (page = 1, limit = 10, rating = 'all') => {
  try {
    const token = getCookie('token');
    
    const url = `/src/api/customer/review_laporan_in?page=${page}&limit=${limit}&rating=${rating}`;
    
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

export default { getReviewData };