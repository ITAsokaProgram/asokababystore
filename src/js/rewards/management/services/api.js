// services/api.js
function getCookie(name) {
  const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([$?*|{}\(\)\[\]\\\/\+^])/g,'\\$1') + '=([^;]*)'));
  return m ? decodeURIComponent(m[1]) : null;
}
export const api = {


  async getRewardTrade(options = {}) {
    const { limit = 10, offset = 0, status = "" } = options;
    const token = getCookie("admin_token");
    const params = new URLSearchParams({
      limit: limit.toString(),
      offset: offset.toString(),
    });

    if (status) params.append("status", status);

    const response = await fetch(
      `/src/api/rewards/management/get_reward_trade?${params}`,
      {
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${token}`,
        },
        credentials: "include",
        cache: "no-store",
      }
    );
    if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
    const metaData = await response.json();
    return {
      status: metaData.status,
      message: metaData.message,
      data: metaData.data,
      meta: metaData.meta,
    };
  },

  async getStatusCard() {
    const token = getCookie("admin_token");
    const response = await fetch(`/src/api/rewards/management/get_status_card`, {
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
      },
      credentials: "include",
      cache: "no-store",
    });
    if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
    const res = await response.json();
    return {
      status: res.status,
      message: res.message,
      data: res.data,
    };
  },

  async getDataByFilter(filterOptions = {}) {
    const token = getCookie("admin_token");
    const { 
      start, 
      end, 
      kd_store = '', 
      limit = 10, 
      offset = 0 
    } = filterOptions;
    
    const response = await fetch(`/src/api/rewards/management/get_data_by_filter`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      credentials: 'include',
      cache: 'no-store',
      body: JSON.stringify({
        start,
        end,
        kd_store,
        limit,
        offset
      })
    });
    
    if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
    const metaData = await response.json();
    return {
      status: metaData.status,
      message: metaData.message,
      data: metaData.data,
      meta: metaData.meta
    };
  },

  async getDataCabang() {
    try{
      const token = getCookie("admin_token");
      const response = await fetch('/src/api/cabang/get_kode_cabang_pubs', {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        credentials: 'include',
        cache: 'no-store',
      });
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
      const res = await response.json();
      return {
        status: res.status,
        message: res.message,
        data: res.data,
      };
    } catch (error) {
      console.error("Error fetching cabang data:", error);
      throw error;
    }
  }

};
