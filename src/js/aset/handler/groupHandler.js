const qs = (s) => document.querySelector(s);

// FUNGSI BARU: Ambil data dari API baru
async function fetchGroups() {
  try {
    const res = await fetch("/src/api/aset/get_all_groups.php");
    if (!res.ok) throw new Error("Gagal memuat data group");
    const json = await res.json();
    if (!json.status) throw new Error(json.message);
    return json.data; // API mengembalikan {status: true, data: ["Grup A", "Grup B"]}
  } catch (err) {
    console.error(err);
    return [];
  }
}

// FUNGSI BARU: Isi dropdown
async function populateGroupSelect() {
  const sel = document.getElementById("filter_group_aset");
  if (!sel) return;

  const groups = await fetchGroups();
  if (groups.length === 0 && sel.options.length === 1) {
    sel.options[0].textContent = "Group tidak ditemukan"; // Update 'Semua Group'
    return;
  }

  groups.forEach((group) => {
    const opt = document.createElement("option");
    opt.value = group;
    opt.textContent = group;
    sel.appendChild(opt);
  });

  // Nilai 'selected' sudah di-set oleh inline script di PHP.
}

export function initGroupHandler() {
  // 1. Panggil fungsi untuk mengisi dropdown
  populateGroupSelect();

  // 2. Ambil alih logic tombol Clear
  const clearBtn = qs("#clearFilters");
  if (clearBtn) {
    clearBtn.addEventListener("click", () => {
      // 1. Reset form
      const form = document.getElementById("filter-form");
      if (form) {
        form.reset();
        // Pastikan select 'Cabang', 'Status', dan 'Group' kembali ke default
        const cabSelect = document.getElementById("filterCabang");
        if (cabSelect) cabSelect.value = ""; // Set ke "Semua Cabang"
        const statusSelect = document.getElementById("filterStatus");
        if (statusSelect) statusSelect.value = ""; // Set ke "Semua Status"
        const groupSelect = document.getElementById("filter_group_aset");
        if (groupSelect) groupSelect.value = ""; // Set ke "Semua Group"
      }

      // 2. Hapus parameter dari URL
      window.history.pushState({}, "", window.location.pathname);

      // 3. Render ulang tabel dengan state bersih
      if (typeof window.renderAsetTable === "function") {
        window.renderAsetTable({
          resetPage: true,
          search: "",
          kd_store: "",
          group_aset: "",
          status_aset: "",
          tanggal_beli_from: "",
          tanggal_beli_to: "",
          tanggal_perbaikan_from: "",
          tanggal_perbaikan_to: "",
          tanggal_rusak_from: "",
          tanggal_rusak_to: "",
          tanggal_mutasi_from: "",
          tanggal_mutasi_to: "",
        });
      }
    });
  }
}

export default initGroupHandler;
