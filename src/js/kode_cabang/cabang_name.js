import getCookie from "./../index/utils/cookies.js";
export const cabangName = async (selectId) => {
  try {
    const token = getCookie("token");
    const select = document.getElementById(selectId);
    const response = await fetch("/src/api/cabang/get_kode_cabang_pubs", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    const data = await response.json();

    if (response.status === 200) {
      // Filter hanya cabang dengan nama 'Asoka Baby Store'
      const keywords = ["asoka baby store", "asoka veteran" , "asoka air"];

      const filteredData = data.data.filter(
        (item) =>
          item.nama_cabang &&
          keywords.some((keyword) =>
            item.nama_cabang.toLowerCase().includes(keyword)
          )
      );

      if (filteredData.length > 0) {
        filteredData.forEach((item) => {
          const option = document.createElement("option");
          option.value = item.store;
          option.textContent = item.nama_cabang;
          select.appendChild(option);
        });
      } else {
        // Jika tidak ada, tampilkan info
        const option = document.createElement("option");
        option.textContent = "Cabang tidak ditemukan";
        option.value = "";
        select.appendChild(option);
      }
    } else {
      console.log(data.message);
    }
  } catch (error) {
    console.log(error);
  }
};

export default { cabangName };
