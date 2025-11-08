import getCookie from "../../index/utils/cookies.js";

export async function initSelectCabang() {
  const sel = document.getElementById("filterCabang");
  const selStatus = document.getElementById("filterStatus"); // Tetap ambil ini
  if (!sel) return;
  if (!selStatus) return;

  // clear existing options
  sel.innerHTML = "";

  // TAMBAHKAN OPSI "SEMUA CABANG"
  const allOpt = document.createElement("option");
  allOpt.value = ""; // Nilai kosong untuk "semua"
  allOpt.textContent = "Semua Cabang";
  sel.appendChild(allOpt);

  const token = getCookie("admin_token");
  const url = "/src/api/cabang/get_kode.php";
  try {
    const res = await fetch(url, {
      method: "GET",
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });
    if (!res.ok) throw new Error("Gagal memuat data cabang");
    const json = await res.json();
    const items = json.data || [];

    if (!items.length && sel.options.length === 1) {
      // Jika tidak ada item dan hanya ada "Semua Cabang"
      // Hapus "Semua Cabang" dan ganti dengan pesan error
      sel.options[0].textContent = "Gagal memuat cabang";
      sel.options[0].disabled = true;
      return;
    }

    // populate with alias names
    items.forEach((it, idx) => {
      const opt = document.createElement("option");
      opt.value = it.store || it.Kd_Store || it.kd_store || ""; // be flexible
      opt.textContent =
        it.nama_cabang || it.Nm_Alias || it.nm_alias || opt.value;
      sel.appendChild(opt);
    });

    // Set nilai 'selected' dari URL (dibaca oleh PHP di file .php)
    // Kita tidak perlu set default value di sini lagi.

    // HAPUS EVENT LISTENER OTOMATIS
    // sel.addEventListener('change', (e) => {
    //     const v = e.target.value;
    //     if (typeof window.renderAsetTable === 'function') window.renderAsetTable({kd_store: v, page: 1});
    // });

    // HAPUS EVENT LISTENER OTOMATIS
    // selStatus.addEventListener('change', (e) => {
    //     const v = e.target.value;
    //     if (typeof window.renderAsetTable === 'function') window.renderAsetTable({status_aset: v, page: 1});
    // });

    // HAPUS TRIGGER RENDER OTOMATIS
    // const firstVal = sel.options[0].value;
    // sel.value = firstVal;
    // if (typeof window.renderAsetTable === 'function') window.renderAsetTable({kd_store: firstVal, page: 1});
  } catch (err) {
    console.error("initSelectCabang error", err);
    // fallback
    if (sel.options.length === 1) {
      sel.options[0].textContent = "Error Cabang";
      sel.options[0].disabled = true;
    }
  }
}

export default initSelectCabang;
