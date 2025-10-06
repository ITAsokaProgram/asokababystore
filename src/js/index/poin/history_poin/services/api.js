import { getCookie } from "../../../utils/cookies.js";
const API_POINT = "/src/api/customer/get_poin_customer";
const API_HISTORY = "/src/api/member/member_poin_pubs";

export const fetchUserPoints = async () => {
  try {
    const token = getCookie("token");
    const response = await fetch(API_POINT, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    const data = await response.json();

    if (response.status === 200) {
      console.log("user points", data);
      return data.data;
    } else {
      console.log(data.message);
    }
  } catch (error) {
    console.log(error);
  }
}

export const fetchHistoryPoints = async () => {
    try {
        const token = getCookie("token");
        const response = await fetch(API_HISTORY, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
        },
        });
        const data = await response.json();
    
        if (response.status === 200) {
        console.log("history points", data);
        return data.data;
        } else {
        console.log(data.message);
        }
    } catch (error) {
        console.log(error);
    }
}

