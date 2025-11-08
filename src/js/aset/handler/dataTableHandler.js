// dataTableHandler.js dengan grouping by group_aset
import { api } from "../services/api.js";
import getCookie from "../../index/utils/cookies.js";

const qs = (s) => document.querySelector(s);

let state = {
  page: 1,
  per_page: 10,
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
  total_pages: 1,
};

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

async function fetchData() {
  const url = new URL(
    "/src/api/aset/get_data_aset.php",
    window.location.origin
  );
  url.searchParams.set("page", state.page);
  url.searchParams.set("per_page", state.per_page);
  if (state.search) url.searchParams.set("search", state.search);
  if (state.kd_store) url.searchParams.set("kd_store", state.kd_store);
  if (state.group_aset) url.searchParams.set("group_aset", state.group_aset);
  if (state.status_aset) url.searchParams.set("status_aset", state.status_aset);
  if (state.tanggal_beli_from)
    url.searchParams.set("tanggal_beli_from", state.tanggal_beli_from);
  if (state.tanggal_beli_to)
    url.searchParams.set("tanggal_beli_to", state.tanggal_beli_to);
  if (state.tanggal_perbaikan_from)
    url.searchParams.set(
      "tanggal_perbaikan_from",
      state.tanggal_perbaikan_from
    );
  if (state.tanggal_perbaikan_to)
    url.searchParams.set("tanggal_perbaikan_to", state.tanggal_perbaikan_to);
  if (state.tanggal_rusak_from)
    url.searchParams.set("tanggal_rusak_from", state.tanggal_rusak_from);
  if (state.tanggal_rusak_to)
    url.searchParams.set("tanggal_rusak_to", state.tanggal_rusak_to);
  if (state.tanggal_mutasi_from)
    url.searchParams.set("tanggal_mutasi_from", state.tanggal_mutasi_from);
  if (state.tanggal_mutasi_to)
    url.searchParams.set("tanggal_mutasi_to", state.tanggal_mutasi_to);

  const res = await fetch(url.toString(), { method: "GET" });
  if (!res.ok) throw new Error("Network response was not ok");
  return res.json();
}

function renderRows(items) {
  const tbody = document.getElementById("tbody");
  if (!tbody) return;
  tbody.innerHTML = "";
  const startIndex = (state.page - 1) * state.per_page;

  let currentGroup = null;
  let groupIndex = 1;

  items.forEach((it, idx) => {
    const group = it.group_aset || "Tanpa Group";

    // Tambahkan header group jika berbeda dari group sebelumnya
    if (group !== currentGroup) {
      currentGroup = group;
      const groupHeaderRow = document.createElement("tr");
      groupHeaderRow.className = "group-header-row";
      groupHeaderRow.innerHTML = `
        <td colspan="19" class="px-4 py-2">
          Group Aset: <span class="font-bold">${escapeHtml(group)}</span>
        </td>
      `;
      tbody.appendChild(groupHeaderRow);
      groupIndex = 1; // Reset index per group
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

    tr.innerHTML = `
      <td class="text-center">${groupIndex}</td>
      <td class="truncate">${escapeHtml(it.no_seri || "")}</td>
      <td class="truncate">${escapeHtml(it.nama_barang || "")}</td>
      <td class="truncate">${escapeHtml(it.group_aset || "")}</td>
      <td class="truncate">${escapeHtml(it.merk || "")}</td>
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
          ? it.harga_beli.toLocaleString("id-ID", {
              style: "currency",
              currency: "IDR",
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
      <td class="truncate">${escapeHtml(it.nm_alias || "")}</td>
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
        window.renderAsetTable({ resetPage: true });
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
  try {
    const res = await fetchData();
    if (!res.status) throw new Error(res.message || "Failed to fetch");

    renderRows(res.data.items || []);
    state.total_pages = res.data.total_pages || 1;

    const countText = document.getElementById("countText");
    const countText2 = document.getElementById("countText2");
    const pageText = document.getElementById("pageText");
    if (countText) countText.textContent = `${res.data.total} Barang`;
    if (countText2)
      countText2.textContent = `Menampilkan ${Math.min(
        (state.page - 1) * state.per_page + 1,
        res.data.total
      )} - ${Math.min(state.page * state.per_page, res.data.total)} dari ${
        res.data.total
      } produk`;
    if (pageText)
      pageText.textContent = `Hal ${res.data.page} dari ${res.data.total_pages}`;

    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    if (prevBtn) prevBtn.disabled = state.page <= 1;
    if (nextBtn) nextBtn.disabled = state.page >= (res.data.total_pages || 1);
  } catch (err) {
    console.error("Render error", err);
  }
}

function attachListeners() {
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const searchInput = document.getElementById("filterSearch");

  if (prevBtn)
    prevBtn.addEventListener("click", () => {
      if (state.page > 1) {
        state.page--;
        render();
      }
    });

  if (nextBtn)
    nextBtn.addEventListener("click", () => {
      state.page++;
      render();
    });

  if (searchInput) {
    const deb = debounce((e) => {
      state.search = e.target.value.trim();
      state.page = 1;
      render();
    }, 300);
    searchInput.addEventListener("input", deb);
  }

  const dateChange = (key, value) => {
    state[key] = value;
    state.page = 1;
    render();
  };

  [
    "filter_tanggal_beli_from",
    "filter_tanggal_beli_to",
    "filter_tanggal_perbaikan_from",
    "filter_tanggal_perbaikan_to",
    "filter_tanggal_rusak_from",
    "filter_tanggal_rusak_to",
    "filter_tanggal_mutasi_from",
    "filter_tanggal_mutasi_to",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) {
      const key = id.replace("filter_", "");
      el.addEventListener("change", (e) => dateChange(key, e.target.value));
    }
  });
}

window.renderAsetTable = async function (opts = {}) {
  Object.keys(opts).forEach((k) => {
    if (opts[k] !== undefined) state[k] = opts[k];
  });
  if (opts.page === undefined && opts.resetPage) state.page = 1;
  await render();
};

export function initDataTable() {
  attachListeners();
  window.renderAsetTable();
}

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
            <td class="px-3 py-2 align-top border-t">${escapeHtml(user)}</td>
            <td class="px-3 py-2 align-top border-t">${escapeHtml(tanggal)}</td>
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
          <td class="px-3 py-2 align-top border-t">${escapeHtml(user)}</td>
          <td class="px-3 py-2 align-top border-t">${escapeHtml(tanggal)}</td>
          <td class="px-3 py-2 align-top border-t">-</td>
          <td class="px-3 py-2 align-top border-t" colspan="2">${escapeHtml(
            kegiatan
          )}</td>
        `;
        tbody.appendChild(tr);
      }
    });

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
