import getCookie from "./../index/utils/cookies.js";
export const areaCabang = async (selectId) => {
  try {
    const token = getCookie("token");
    const select = document.getElementById(selectId);
    const response = await fetch("/src/api/cabang/get_kode_area", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    const data = await response.json();

    if (response.status === 200) {
      // Hapus semua option sebelumnya
      select.innerHTML = "";

      const allOption = document.createElement("option");
      allOption.textContent = "Pusat";
      allOption.value = "all";
      select.appendChild(allOption);

      const groupingCabang = data.data.reduce((cab, area) => {
        if (!cab[area.group_cabang]) cab[area.group_cabang] = [];
        cab[area.group_cabang].push(area);
        return cab;
      }, {});

      Object.entries(groupingCabang).forEach(([areaName, cabangs]) => {
        const option = document.createElement("option");
        option.textContent = areaName; // Tampilkan nama area
        // Gabungkan semua nama_cabang jadi satu string, pisahkan koma
        option.value = cabangs.map((i) => i.store).join(",");
        select.appendChild(option);
      });
    } else if (response.status === 204) {
      console.log(data.message);
    }
  } catch (error) {
    console.log(error);
  }
};

export default { areaCabang };
