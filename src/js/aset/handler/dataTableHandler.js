// dataTableHandler.js dengan grouping by group_aset
import { api } from "../services/api.js";
import getCookie from "../../index/utils/cookies.js";

const qs = (s) => document.querySelector(s);
let state = {
  page: 1,
  per_page: 10,
  total_pages: 1,
};

function getUrlParams() {
  const params = new URLSearchParams(window.location.search);
  return {
    page: parseInt(params.get("page") || "1", 10),
    kd_store: params.get("kd_store") || "",
    status_aset: params.get("status_aset") || "",
    search: params.get("search") || "",
    group_aset: params.get("group_aset") || "",
    tanggal_beli_from: params.get("tanggal_beli_from") || "",
    tanggal_beli_to: params.get("tanggal_beli_to") || "",
    tanggal_perbaikan_from: params.get("tanggal_perbaikan_from") || "",
    tanggal_perbaikan_to: params.get("tanggal_perbaikan_to") || "",
    tanggal_rusak_from: params.get("tanggal_rusak_from") || "",
    tanggal_rusak_to: params.get("tanggal_rusak_to") || "",
    tanggal_mutasi_from: params.get("tanggal_mutasi_from") || "",
    tanggal_mutasi_to: params.get("tanggal_mutasi_to") || "",
  };
}

function build_pagination_url(newPage) {
  const params = new URLSearchParams(window.location.search);
  params.set("page", newPage);
  return "?" + params.toString();
}
function debounce(fn, wait = 300) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), wait);
  };
}

function generateCloudinaryThumbnail(originalUrl) {
  if (!originalUrl || !originalUrl.includes("/upload/")) {
    return originalUrl;
  }
  const transformations = "w_160,h_160,c_thumb,q_auto:good,f_auto";
  return originalUrl.replace("/upload/", `/upload/${transformations}/`);
}

// MODIFIKASI: fetchData() sekarang membaca parameter URL
async function fetchData() {
  const url = new URL(
    "/src/api/aset/get_data_aset.php",
    window.location.origin
  );

  // Ambil parameter dari URL, bukan dari 'state' global
  const params = getUrlParams();

  url.searchParams.set("page", params.page);
  url.searchParams.set("per_page", state.per_page); // per_page tetap dari state
  if (params.search) url.searchParams.set("search", params.search);
  if (params.kd_store) url.searchParams.set("kd_store", params.kd_store);
  if (params.group_aset) url.searchParams.set("group_aset", params.group_aset);
  if (params.status_aset)
    url.searchParams.set("status_aset", params.status_aset);
  if (params.tanggal_beli_from)
    url.searchParams.set("tanggal_beli_from", params.tanggal_beli_from);
  if (params.tanggal_beli_to)
    url.searchParams.set("tanggal_beli_to", params.tanggal_beli_to);
  if (params.tanggal_perbaikan_from)
    url.searchParams.set(
      "tanggal_perbaikan_from",
      params.tanggal_perbaikan_from
    );
  if (params.tanggal_perbaikan_to)
    url.searchParams.set("tanggal_perbaikan_to", params.tanggal_perbaikan_to);
  if (params.tanggal_rusak_from)
    url.searchParams.set("tanggal_rusak_from", params.tanggal_rusak_from);
  if (params.tanggal_rusak_to)
    url.searchParams.set("tanggal_rusak_to", params.tanggal_rusak_to);
  if (params.tanggal_mutasi_from)
    url.searchParams.set("tanggal_mutasi_from", params.tanggal_mutasi_from);
  if (params.tanggal_mutasi_to)
    url.searchParams.set("tanggal_mutasi_to", params.tanggal_mutasi_to);

  const res = await fetch(url.toString(), { method: "GET" });
  if (!res.ok) throw new Error("Network response was not ok");
  return res.json();
}
function renderRows(items) {
  const tbody = document.getElementById("tbody");
  if (!tbody) return;
  tbody.innerHTML = "";

  // Dapatkan parameter URL saat ini
  const params = getUrlParams();
  const currentKdStore = params.kd_store;
  const currentPage = params.page;

  // Tentukan colspan berdasarkan filter kd_store
  const colspan = currentKdStore === "" ? 20 : 19;

  const startIndex = (currentPage - 1) * state.per_page;

  let currentGroup = null;
  let groupIndex = 1;

  if (items.length === 0) {
    tbody.innerHTML = `
            <tr>
                <td colspan="${colspan}" class="text-center p-8 text-gray-500">
                    <i class="fas fa-inbox fa-lg mb-2"></i>
                    <p>Tidak ada data ditemukan untuk filter ini.</p>
                </td>
            </tr>
        `;
    return;
  }

  items.forEach((it, idx) => {
    const group = it.group_aset || "Tanpa Group";

    if (group !== currentGroup) {
      currentGroup = group;
      const groupHeaderRow = document.createElement("tr");
      groupHeaderRow.className = "group-header-row";
      groupHeaderRow.innerHTML = `
                <td colspan="${colspan}" class="px-4 py-2">
                  Group Aset: <span class="font-bold">${escapeHtml(
                    group
                  )}</span>
                </td>
            `;
      tbody.appendChild(groupHeaderRow);
      groupIndex = 1;
    }

    const tr = document.createElement("tr");
    tr.className = "table-row";
    const statusHtml = renderStatusBadge(it.status || "");
    const thumbnailUrl = generateCloudinaryThumbnail(it.image_url);

    const imageCell =
      it.image_url && it.image_url.trim()
        ? `<a href="#" class="image-link" data-url="${escapeHtml(
            it.image_url
          )}">
                <img src="${escapeHtml(
                  thumbnailUrl
                )}" alt="Image" class="inline-block w-20 h-20 object-cover rounded" loading="lazy" />
              </a>`
        : `<span class="text-gray-500 text-xs">No image</span>`;

    // Tambahkan sel cabang secara kondisional
    const cabangCell =
      currentKdStore === ""
        ? `<td class="truncate">${escapeHtml(it.nm_alias || "")}</td>`
        : "";

    tr.innerHTML = `
            <td class="text-center">${groupIndex}</td>
            <td class="truncate">${escapeHtml(it.no_seri || "")}</td>
            <td class="truncate">${escapeHtml(it.nama_barang || "")}</td>
            <td class="truncate">${escapeHtml(it.group_aset || "")}</td>
            <td class="truncate">${escapeHtml(it.merk || "")}</td>
            ${cabangCell}
            <td class="truncate">${escapeHtml(
              (it.tanggal_rusak || "").split(" ")[0]
            )}</td>
            <td class="truncate">${escapeHtml(
              (it.tanggal_perbaikan || "").split(" ")[0]
            )}</td>
            <td class="truncate">${escapeHtml(
              (it.tanggal_ganti || "").split(" ")[0]
            )}</td>
            <td class="truncate">${
              it.harga_beli
                ? parseFloat(it.harga_beli).toLocaleString("id-ID", {
                    style: "currency",
                    currency: "IDR",
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                  })
                : "-"
            }</td>
            <td class="truncate">${escapeHtml(it.nama_toko || "")}</td>
            <td class="truncate">${escapeHtml(
              (it.tanggal_beli || "").split(" ")[0]
            )}</td>
            <td class="truncate">${escapeHtml(it.mutasi_dari || "")}</td>
            <td class="truncate">${escapeHtml(it.mutasi_untuk || "")}</td>
            <td class="truncate">${escapeHtml(
              (it.tanggal_mutasi || "").split(" ")[0]
            )}</td>
            <td>${statusHtml}</td>
            <td class="truncate">${imageCell}</td>
            
             <td class="text-wrap" style="max-width: 200px;">${escapeHtml(
               it.keterangan || ""
             )}</td>
            <td>
                <div class="flex gap-1">
                    <button data-id="${
                      it.idhistory_aset
                    }" class="btn-edit px-2 py-1 bg-indigo-600 text-white rounded text-xs">
                        <i class="fa-solid fa-edit"></i>
                    </button>
                    <button data-id="${
                      it.idhistory_aset
                    }" class="btn-delete px-2 py-1 bg-red-600 text-white rounded text-xs">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                    <button data-id="${
                      it.idhistory_aset
                    }" class="btn-history px-2 py-1 bg-blue-600 text-white rounded text-xs" title="Lihat Riwayat">
                        <i class="fa-solid fa-book"></i>
                    </button>
                </div>
            </td>
        `;
    tbody.appendChild(tr);
    groupIndex++;
  });

  // Attach event listeners
  tbody.querySelectorAll(".btn-edit").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.getAttribute("data-id");
      await openEditModal(id);
    });
  });

  tbody.querySelectorAll(".btn-delete").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.getAttribute("data-id");
      try {
        const { value, isConfirmed } = await Swal.fire({
          title: "Konfirmasi Hapus",
          html: "Ketik <strong>HAPUS</strong> untuk menghapus aset ini.",
          icon: "warning",
          input: "text",
          inputPlaceholder: "Ketik HAPUS untuk konfirmasi",
          showCancelButton: true,
          confirmButtonText: "Hapus",
          preConfirm: (val) => {
            if (String(val).trim() !== "HAPUS") {
              Swal.showValidationMessage(
                "Ketik HAPUS untuk mengonfirmasi penghapusan."
              );
            }
            return val;
          },
        });
        if (!isConfirmed) return;
        if (String(value).trim() !== "HAPUS") {
          await Swal.fire({
            icon: "error",
            title: "Dibatalkan",
            text: "Konfirmasi tidak valid.",
          });
          return;
        }

        const token = getCookie("admin_token");
        await api.deleteDataAset(token, id);
        await Swal.fire({
          icon: "success",
          title: "Terhapus",
          text: "Aset berhasil dihapus.",
        });
        // Render ulang halaman saat ini
        window.renderAsetTable({ resetPage: false });
      } catch (err) {
        await Swal.fire({
          icon: "error",
          title: "Gagal",
          text: err.message || "Terjadi kesalahan",
        });
      }
    });
  });

  tbody.querySelectorAll(".btn-history").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.getAttribute("data-id");
      await openHistoryModal(id);
    });
  });

  tbody.querySelectorAll(".image-link").forEach((a) => {
    a.addEventListener("click", (ev) => {
      ev.preventDefault();
      const url = a.getAttribute("data-url");
      if (url) showImagePreview(url);
    });
  });
}

function renderStatusBadge(status) {
  const s = String(status || "").toLowerCase();
  let classes =
    "inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold";
  let label = escapeHtml(status || "");
  switch (s) {
    case "baru":
      classes += " bg-green-100 text-green-800 border border-green-200";
      break;
    case "services":
    case "service":
      classes += " bg-yellow-100 text-yellow-800 border border-yellow-200";
      break;
    case "mutasi":
      classes += " bg-blue-100 text-blue-800 border border-blue-200";
      break;
    case "rusak":
    case "repair":
      classes += " bg-red-100 text-red-800 border border-red-200";
      break;
    default:
      classes += " bg-gray-100 text-gray-800 border border-gray-200";
  }
  return `<span class="${classes}">${label}</span>`;
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function showImagePreview(url) {
  const existing = document.getElementById("imagePreviewOverlay");
  if (existing) existing.remove();

  const overlay = document.createElement("div");
  overlay.id = "imagePreviewOverlay";
  overlay.style.cssText =
    "position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.7);display:flex;align-items:center;justify-content:center;z-index:9999;";

  const img = document.createElement("img");
  img.src = url;
  img.style.cssText =
    "max-width:90%;max-height:90%;box-shadow:0 2px 12px rgba(0,0,0,0.5);border-radius:8px;";
  img.alt = "Preview";

  overlay.appendChild(img);
  overlay.addEventListener("click", () => overlay.remove());

  const onKey = (e) => {
    if (e.key === "Escape") {
      overlay.remove();
      window.removeEventListener("keydown", onKey);
    }
  };
  window.addEventListener("keydown", onKey);

  document.body.appendChild(overlay);
}
async function render() {
  const params = getUrlParams();
  state.page = params.page;

  const filterSubmitButton = document.getElementById("filter-submit-button");
  const tbody = document.getElementById("tbody");

  // Tentukan colspan berdasarkan filter kd_store
  const currentKdStore = params.kd_store;
  const colspan = currentKdStore === "" ? 20 : 19;

  // Tampilkan loading
  if (filterSubmitButton) filterSubmitButton.disabled = true;
  if (tbody)
    tbody.innerHTML = `
        <tr>
            <td colspan="${colspan}" class="text-center p-8">
                <div class="spinner-simple"></div>
                <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
            </td>
        </tr>`;

  try {
    const res = await fetchData();
    if (!res.status) throw new Error(res.message || "Failed to fetch");

    const pagination = {
      current_page: res.data.page,
      total_pages: res.data.total_pages,
      total_rows: res.data.total,
      limit: res.data.per_page,
      offset: (res.data.page - 1) * res.data.per_page,
    };

    renderRows(res.data.items || []);

    const countText = document.getElementById("countText");
    if (countText) countText.textContent = `${res.data.total} Barang`;

    renderPagination(pagination);
  } catch (err) {
    console.error("Render error", err);
    if (tbody)
      tbody.innerHTML = `
            <tr>
                <td colspan="${colspan}" class="text-center p-8 text-red-600">
                    <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
                    <p>Gagal memuat data: ${err.message}</p>
                </td>
            </tr>`;
    renderPagination(null);
  } finally {
    if (filterSubmitButton) {
      filterSubmitButton.disabled = false;
      filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
    }
  }
}

// --- TAMBAHKAN FUNGSI BARU ---
// Fungsi untuk render pagination (diambil dari all_item_handler.js)
function renderPagination(pagination) {
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");

  if (!pagination || !paginationInfo || !paginationLinks) {
    if (paginationInfo) paginationInfo.textContent = "";
    if (paginationLinks) paginationLinks.innerHTML = "";
    return;
  }

  const { current_page, total_pages, total_rows, limit, offset } = pagination;

  if (total_rows === 0) {
    paginationInfo.textContent = "Menampilkan 0 dari 0 data";
    paginationLinks.innerHTML = "";
    return;
  }

  const start_row = offset + 1;
  const end_row = Math.min(offset + limit, total_rows);
  paginationInfo.textContent = `Menampilkan ${start_row} - ${end_row} dari ${total_rows} data`;

  let linksHtml = "";

  // Tombol Previous
  linksHtml += `
        <a href="${
          current_page > 1 ? build_pagination_url(current_page - 1) : "#"
        }" 
           class="pagination-link ${
             current_page === 1 ? "pagination-disabled" : ""
           }">
            <i class="fas fa-chevron-left"></i>
        </a>
    `;

  // Logic untuk menampilkan nomor halaman
  const pages_to_show = [];
  const max_pages_around = 2;
  for (let i = 1; i <= total_pages; i++) {
    if (
      i === 1 ||
      i === total_pages ||
      (i >= current_page - max_pages_around &&
        i <= current_page + max_pages_around)
    ) {
      pages_to_show.push(i);
    }
  }

  let last_page = 0;
  for (const page_num of pages_to_show) {
    if (last_page !== 0 && page_num > last_page + 1) {
      linksHtml += `<span class="pagination-ellipsis">...</span>`;
    }
    linksHtml += `
            <a href="${build_pagination_url(page_num)}" 
               class="pagination-link ${
                 page_num === current_page ? "pagination-active" : ""
               }">
                ${page_num}
            </a>
        `;
    last_page = page_num;
  }

  // Tombol Next
  linksHtml += `
        <a href="${
          current_page < total_pages
            ? build_pagination_url(current_page + 1)
            : "#"
        }" 
           class="pagination-link ${
             current_page === total_pages ? "pagination-disabled" : ""
           }">
            <i class="fas fa-chevron-right"></i>
        </a>
    `;

  paginationLinks.innerHTML = linksHtml;
}

// --- MODIFIKASI FUNGSI attachListeners ---
function attachListeners() {
  // HAPUS SEMUA LISTENER FILTER OTOMATIS
  // (prevBtn, nextBtn, searchInput, date inputs)

  // TAMBAHKAN LISTENER UNTUK FORM SUBMIT
  const filterForm = qs("#filter-form");
  const filterSubmitButton = qs("#filter-submit-button");

  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault(); // Hentikan submit form standar

      // Tampilkan loading di tombol
      if (filterSubmitButton) {
        filterSubmitButton.disabled = true;
        filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
      }

      // Buat parameter URL baru dari form
      const formData = new FormData(filterForm);
      const params = new URLSearchParams(formData);
      params.set("page", "1"); // Selalu reset ke halaman 1 saat filter

      // Update URL di browser
      window.history.pushState(
        {},
        "",
        `${window.location.pathname}?${params.toString()}`
      );

      // Panggil render() untuk memuat data baru
      // render() akan membaca URL yang baru saja kita set
      render();
    });
  }
}
// -----------------------------------------

// Modifikasi window.renderAsetTable
window.renderAsetTable = async function (opts = {}) {
  // Fungsi ini dipanggil oleh tombol 'Clear'
  // Kita harus update 'state' internal sebelum memanggil 'render'
  // agar 'fetchData' membaca state yang benar (terutama untuk 'page')

  if (opts.resetPage) {
    state.page = 1;
  }

  // Panggil render() yang akan membaca URL (atau state yang baru di-clear)
  await render();
};

export function initDataTable() {
  attachListeners();
  // Render tabel saat halaman dimuat, berdasarkan URL
  window.renderAsetTable();
}

// ... (sisa file: openEditModal, openHistoryModal, dll. biarkan apa adanya) ...
async function openEditModal(id) {
  try {
    const res = await fetch(
      `/src/api/aset/get_data_aset.php?page=1&per_page=1&id=${id}`
    );
    const data = await res.json();
    let item = data.data.items?.[0];
    if (!item) throw new Error("Record not found");

    const form = document.getElementById("editAssetForm");
    if (!form) throw new Error("Form not found");

    form.querySelector("#edit_idhistory_aset").value =
      item.idhistory_aset || "";
    const dateFields = [
      "tanggal_beli",
      "tanggal_ganti",
      "tanggal_perbaikan",
      "tanggal_mutasi",
      "tanggal_rusak",
    ];
    [
      "nama_barang",
      "merk",
      "harga_beli",
      "nama_toko",
      "tanggal_beli",
      "tanggal_ganti",
      "tanggal_perbaikan",
      "tanggal_mutasi",
      "tanggal_rusak",
      "group_aset",
      "mutasi_untuk",
      "mutasi_dari",
      "kd_store",
      "status",
      "no_seri",
      "keterangan",
    ].forEach((k) => {
      const el = form.querySelector(`[name="edit_${k}"]`);
      if (!el) return;
      let val = item[k] ?? "";
      if (val && dateFields.includes(k)) {
        val = String(val).split(" ")[0];
      }
      el.value = val;
    });

    const imagePreview = document.getElementById("editImagePreview");
    const previewImg = imagePreview.querySelector("img");
    if (item.image_url) {
      previewImg.src = item.image_url;
      imagePreview.classList.remove("hidden");
    } else {
      imagePreview.classList.add("hidden");
    }

    const modal = document.getElementById("editAssetModal");
    modal.classList.remove("hidden");

    document.querySelectorAll(".close-modal-edit").forEach((btn) => {
      btn.onclick = () => {
        modal.classList.add("hidden");
        imagePreview.classList.add("hidden");
        previewImg.src = "";
      };
    });
  } catch (err) {
    console.error(err);
    alert("Gagal memuat data untuk edit: " + err.message);
  }
}

async function openHistoryModal(id) {
  try {
    const token = getCookie("admin_token");
    const url = new URL(
      "/src/api/aset/get_history_log.php",
      window.location.origin
    );
    url.searchParams.set("idhistory_aset", id);
    const res = await fetch(url.toString(), {
      method: "GET",
      headers: { Authorization: "Bearer " + token },
    });
    const json = await res.json();
    if (!json.status) throw new Error(json.message || "Gagal memuat riwayat");
    const items = json.data.items || [];

    const tbody = document.getElementById("historyLogBody");
    if (!tbody) throw new Error("Modal body not found");
    tbody.innerHTML = "";

    if (items.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center p-4 text-gray-500">Belum ada riwayat perubahan.</td></tr>`;
      // Tetap tampilkan modal
    } else {
      items.forEach((it) => {
        const user = it.nama || "";
        const tanggal = it.tanggal || "";
        let kegiatan = it.kegiatan || "";
        let parsed = null;
        try {
          parsed = JSON.parse(kegiatan);
        } catch (e) {
          parsed = null;
        }

        if (Array.isArray(parsed)) {
          parsed.forEach((ch) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                            <td class="px-3 py-2 align-top border-t">${escapeHtml(
                              user
                            )}</td>
                            <td class="px-3 py-2 align-top border-t">${escapeHtml(
                              tanggal
                            )}</td>
                            <td class="px-3 py-2 align-top border-t">${escapeHtml(
                              ch.field || ""
                            )}</td>
                            <td class="px-3 py-2 align-top border-t">${escapeHtml(
                              ch.old ?? ""
                            )}</td>
                            <td class="px-3 py-2 align-top border-t">${escapeHtml(
                              ch.new ?? ""
                            )}</td>
                        `;
            tbody.appendChild(tr);
          });
        } else {
          const tr = document.createElement("tr");
          tr.innerHTML = `
                        <td class="px-3 py-2 align-top border-t">${escapeHtml(
                          user
                        )}</td>
                        <td class="px-3 py-2 align-top border-t">${escapeHtml(
                          tanggal
                        )}</td>
                        <td class="px-3 py-2 align-top border-t">${
                          kegiatan.startsWith("Membuat aset baru")
                            ? "Info"
                            : "-"
                        }</td>
                        <td class="px-3 py-2 align-top border-t" colspan="2">${escapeHtml(
                          kegiatan
                        )}</td>
                    `;
          tbody.appendChild(tr);
        }
      });
    }

    const modal = document.getElementById("historyLogModal");
    if (modal) modal.classList.remove("hidden");
  } catch (err) {
    console.error(err);
    await Swal.fire({
      icon: "error",
      title: "Gagal",
      text: err.message || "Terjadi kesalahan",
    });
  }
}

document.addEventListener("click", (ev) => {
  const closeBtn = ev.target.closest?.("#closeHistoryModal");
  if (closeBtn) {
    const modal = document.getElementById("historyLogModal");
    if (modal) modal.classList.add("hidden");
  }
});
