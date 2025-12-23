class CabangHandler {
  constructor() {
    
  }

  async getCabang() {
    const response = await fetch("/src/api/cabang/get_kode", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    });
    if (!response.ok) {
      console.error("Network Problem Or Server Problem");
    }
    const data = await response.json();
    return data.data;
  }

  async selectCabang() {
    const getCabang = await this.getCabang();
    const select = document.getElementById("filterCabang");
    select.innerHTML = '<option value="">Semua Cabang</option>';
    getCabang.forEach((item) => {
      const options = document.createElement("option");
      options.value = item.store;
      options.textContent = item.nama_cabang;
      select.appendChild(options);
    });
  }
}

export default CabangHandler;
