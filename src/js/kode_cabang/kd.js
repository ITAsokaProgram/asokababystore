import getCookie from "./../index/utils/cookies.js";
export const kodeCabang = async (selectId) => {
  try {
    const token = getCookie("admin_token");
    const select = document.getElementById(selectId);
    const response = await fetch("/src/api/cabang/get_kode", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    const data = await response.json();
    if (response.status === 200) {
      const allOption = document.createElement("option");
      allOption.textContent = "Semua Cabang";
      if (data.data === "Pusat") {
        allOption.value = "all";
      } else {
        allOption.value = "all";
      }
      select.appendChild(allOption);
      if (data.data !== "Pusat") {
        data.data.forEach((item) => {
          const option = document.createElement("option");
          option.value = item.store;
          option.textContent = item.nama_cabang;
          select.appendChild(option);
        });
      }
    } else if (response.status === 204) {
      console.log(data.message);
    }
  } catch (error) {
    console.log(error);
  }
};
export default { kodeCabang };
