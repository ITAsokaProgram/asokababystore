import { getCookie } from "../index/utils/cookies.js";
const fetchRewards = async (pageSize = 10, offset = 0) => {
  try {
    const token = getCookie("admin_token");
    const url = `/src/api/rewards/get_rewards?pageSize=${pageSize}&offset=${offset}`;
    const response = await fetch(url, {
      method: "GET",
      headers: {
        Authorization: "Bearer " + token,
        "Content-Type": "application/json",
      },
    });
    if (response.ok) {
      const result = await response.json();
      if (result.success) {
        return {
          data: result.data,
          total: result.total,
          offset: offset,
          pageSize: pageSize,
        };
      } else {
        return {
          data: [],
          total: 0,
          offset: offset,
          pageSize: pageSize,
        };
      }
    } else {
      return {
        data: [],
        total: 0,
        offset: offset,
        pageSize: pageSize,
      };
    }
  } catch (error) {
    console.error("Error:", error);
    return {
      data: [],
      total: 0,
      offset: offset,
      pageSize: pageSize,
    };
  }
};

const fetchCount = async () => {
  try {
    const token = getCookie("admin_token");
    const url = `/src/api/rewards/get_count`;
    const response = await fetch(url, {
      method: "GET",
      headers: {
        Authorization: "Bearer " + token,
        "Content-Type": "application/json",
      },
    });
    if (response.ok) {
      const result = await response.json();
      return result.total;
    } else {
      return 0;
    }
  } catch (error) {
    console.error("Error:", error);
    return 0;
  }
};

const fetchRewardById = async (id) => {
  try {
    const token = getCookie("admin_token");
    const url = `/src/api/rewards/get_reward_by_id?id=${id}`;
    const response = await fetch(url, {
      method: "GET",
      headers: {
        Authorization: "Bearer " + token,
        "Content-Type": "application/json",
      },
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    return {
      success: result.success,
      data: result.data || null,
      message: result.message 
    };
    
  } catch (error) {
    console.error('Error in fetchRewardById:', error);
    return {
      success: false,
      data: null,
      message: error.message || 'Gagal memuat data hadiah.'
    };
  }
};

const updateReward = async (id, fd) => {
  try {
    const token = getCookie("admin_token");
    const url = `/src/api/rewards/update_reward`;
    const response = await fetch(url, {
      method: "POST",
      headers: {
        Authorization: "Bearer " + token,
      },
      body: fd
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    return {
      success: result.success,
      data: result.data || null,
      message: result.message 
    };
    
  } catch (error) {
    console.error('Error in updateReward:', error);
    return {
      success: false,
      data: null,
      message: error.message || 'Gagal memuat data hadiah.'
    };
  }
};

const deleteReward = async (id) => {
    try {
        const token = getCookie("admin_token");
        const url = `/src/api/rewards/delete_reward?id=${id}`;
        const response = await fetch(url, {
            method: "DELETE",
            headers: {
                Authorization: "Bearer " + token,
                "Content-Type": "application/json",
            },
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        return {
            success: result.success,
            message: result.message 
        };
        
    } catch (error) {
        console.error('Error in deleteReward:', error);
        return {
            success: false,
            message: error.message || 'Gagal memuat data hadiah.'
        };
    }
}

export { fetchRewards, fetchRewardById, updateReward, deleteReward, fetchCount };
