import { deleteUser } from "./delete_user.js";
import {
  displayEditUserModal,
  resetPassword,
  bulkGrantAccess,
} from "./edit_user.js";
import { getUser, getUserEdit, setOtorisasiUser, getOtorisasiTipe } from "./fetch.js";
import { paginationUserInternal } from "./pagination.js";
import { renderTableUserInternal } from "./table.js";
import { areaCabang } from "./../../kode_cabang/cabang_area.js";
import getCookie from "../../index/utils/cookies.js";

const getInitialState = () => {
  const params = new URLSearchParams(window.location.search);
  return {
    page: parseInt(params.get("page")) || 1,
    limit: parseInt(params.get("limit")) || 10,
    search: params.get("search") || "",
  };
};
let currentState = getInitialState();
const updateUrlState = () => {
  const params = new URLSearchParams();
  params.set("page", currentState.page);
  params.set("limit", currentState.limit);
  if (currentState.search) {
    params.set("search", currentState.search);
  }
  const newUrl = `${window.location.pathname}?${params.toString()}`;
  window.history.pushState({ path: newUrl }, "", newUrl);
};
const loadOtoTipe = async () => {
  const selectTipe = document.getElementById("oto_tipe");
  if (!selectTipe) return;

  try {
    const response = await getOtorisasiTipe();
    if (response.success) {
      selectTipe.innerHTML = response.data.map(item =>
        `<option value="${item.tipe}">${item.label}</option>`
      ).join('');
    }
  } catch (error) {
    console.error("Gagal load tipe otorisasi", error);
  }
};
export const init = async () => {
  const token = getCookie("admin_token");
  if (!token) {
    window.location.href = "/in_login";
    return;
  }
  const searchInput = document.getElementById("searchInput");
  if (searchInput && currentState.search) {
    searchInput.value = currentState.search;
  }

  await loadData();
  await loadOtoTipe();
  setupSearchListener();
  setupGlobalEventListeners();
  window.renderTableUserInternal = renderTableUserInternal;
  window.addEventListener("popstate", async () => {
    currentState = getInitialState();
    if (searchInput) searchInput.value = currentState.search;
    await loadData();
  });
};
const loadData = async () => {
  const tableBody = document.querySelector("tbody");
  const paginationContainer = document.getElementById("paginationContainer");
  tableBody.innerHTML = `
    <tr>
        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
            <div class="flex flex-col items-center">
                <i class="fas fa-spinner fa-spin text-pink-500 text-2xl mb-2"></i>
                <p>Memuat data...</p>
            </div>
        </td>
    </tr>`;
  try {
    const response = await getUser(
      currentState.page,
      currentState.search,
      currentState.limit
    );
    if (response && response.data) {
      const offset = (currentState.page - 1) * currentState.limit;
      renderTableUserInternal(response.data, offset);
      if (response.pagination) {
        paginationUserInternal(response.pagination, handlePageChange);
        updateTotalCount(response.pagination.total_records);
      }
    } else {
      tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">Tidak ada data ditemukan.</td></tr>`;
      if (paginationContainer) paginationContainer.innerHTML = "";
    }
  } catch (error) {
    console.error("Load Data Error:", error);
    tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-red-500">Gagal memuat data dari server.</td></tr>`;
  }
};
const handlePageChange = (newPage) => {
  currentState.page = newPage;
  updateUrlState();
  loadData();
};
const updateTotalCount = (total) => {
  const totalEl = document.getElementById("totalUsers");
  if (totalEl) totalEl.textContent = total;
};
const setupSearchListener = () => {
  const searchInput = document.getElementById("searchInput");
  const resetBtn = document.querySelector(".fa-undo")?.parentElement;
  let debounceTimer;
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        currentState.search = e.target.value.trim();
        currentState.page = 1;
        updateUrlState();
        loadData();
      }, 500);
    });
  }
  if (resetBtn) {
    resetBtn.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      currentState.search = "";
      currentState.page = 1;
      updateUrlState();
      loadData();
    });
  }
};
const setupGlobalEventListeners = () => {
  document.addEventListener("click", async (e) => {
    const button = e.target.closest(".edit");
    if (!button) return;
    const kode = button.getAttribute("data-kode");
    try {
      const response = await getUserEdit(kode);
      const user = response.data;
      displayEditUserModal(user);
      const selectCabang = document.getElementById("editCabang");
      selectCabang.innerHTML = "";
      const option = document.createElement("option");
      option.value = user.kode_cabang;
      option.textContent = `${user.kode_cabang === "Pusat" ? "Pusat" : user.nama_cabang
        }`;
      selectCabang.appendChild(option);
      await areaCabang("editCabang");
      modal();
    } catch (error) {
      console.error("Error fetching user data:", error);
      Swal.fire("Error", "Gagal mengambil data user", "error");
    }
  });
  const btnGrantAll = document.getElementById("btnGrantAll");
  if (btnGrantAll) {
    btnGrantAll.addEventListener("click", async () => {
      Swal.fire({
        title: "Konfirmasi Akses Massal",
        html: `Anda akan memberikan <b>SELURUH AKSES</b> (Kecuali sales graph)<br>kepada <b>SEMUA USER</b> yang aktif.<br><br>Proses ini tidak bisa dibatalkan!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#7c3aed",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya!",
        cancelButtonText: "Batal",
      }).then(async (result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Sedang Memproses...",
            html: "Mohon tunggu sebentar...",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });
          try {
            const res = await bulkGrantAccess();
            Swal.fire({
              icon: "success",
              title: "Selesai!",
              text: res.message,
            }).then(() => {
              loadData();
            });
          } catch (error) {
            console.error("Bulk Error:", error);
            Swal.fire({
              icon: "error",
              title: "Gagal",
              text: error.message || "Terjadi kesalahan server",
            });
          }
        }
      });
    });
  }
  document.addEventListener("click", async (e) => {
    const button = e.target.closest(".delete");
    if (!button) return;
    const kode = button.getAttribute("data-kode");
    try {
      Swal.fire({
        title: "Nonaktifkan User?",
        text: "User akan hilang dari daftar ini.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Nonaktifkan",
        cancelButtonText: "Batal",
        confirmButtonColor: "#d33",
      }).then(async (result) => {
        if (result.isConfirmed) {
          const response = await deleteUser(parseInt(kode));
          if (response.status === "success") {
            Swal.fire(
              "Berhasil",
              "User berhasil dinonaktifkan",
              "success"
            ).then(() => {
              loadData();
            });
          } else {
            Swal.fire("Gagal", response.message, "error");
          }
        }
      });
    } catch (error) {
      console.error("Error deleting user:", error);
      Swal.fire("Gagal", "Terjadi kesalahan server", "error");
    }
  });
  document.addEventListener("click", async (e) => {
    const button = e.target.closest(".reset");
    if (!button) return;
    const id = button.getAttribute("data-kode");
    const pass = document.getElementById("newPassword");
    const confirmPass = document.getElementById("confirmPassword");
    const modalReset = document.getElementById("resetPassword");
    const btnSendReset = document.getElementById("save-password");
    pass.value = "";
    confirmPass.value = "";
    modalReset.classList.remove("hidden");
    const handleInput = () => {
      const showErrorPass = document.getElementById("error-password");
      if (confirmPass.value !== pass.value) {
        showErrorPass.classList.remove("hidden");
      } else {
        showErrorPass.classList.add("hidden");
      }
    };
    confirmPass.oninput = handleInput;
    pass.oninput = handleInput;
    btnSendReset.onclick = async () => {
      if (pass.value !== confirmPass.value) {
        Swal.fire("Error", "Password tidak sama", "error");
        return;
      }
      if (!pass.value) {
        Swal.fire("Error", "Password tidak boleh kosong", "error");
        return;
      }
      const data = {
        id: id,
        pass: pass.value,
        confirmPass: confirmPass.value,
      };
      await resetPassword(data);
      modalReset.classList.add("hidden");
    };
  });
  document.getElementById("close-reset").addEventListener("click", () => {
    document.getElementById("resetPassword").classList.add("hidden");
  });

  // LOGIC OTORISASI (YANG DIUBAH)
  const modalOto = document.getElementById("modalOtorisasi");
  const formOto = document.getElementById("formOtorisasi");
  const closeOtoBtn = document.getElementById("close-otorisasi");
  const cancelOtoBtn = document.getElementById("btn-cancel-otorisasi");

  const closeModalOto = () => {
    if (modalOto) modalOto.classList.add("hidden");
    if (formOto) formOto.reset();
  };

  document.addEventListener("click", (e) => {
    const button = e.target.closest(".otorisasi");
    if (!button) return;
    const kode = button.getAttribute("data-kode");
    const nama = button.getAttribute("data-nama");
    document.getElementById("oto_kode_user").value = kode;
    document.getElementById("oto_nama_user").textContent = nama;

    // TIDAK LAGI MENGISI TANGGAL OTOMATIS
    if (modalOto) modalOto.classList.remove("hidden");
  });


  formOto.addEventListener("submit", async (e) => {
    e.preventDefault();
    const data = {
      kode_user: document.getElementById("oto_kode_user").value,
      tipe: document.getElementById("oto_tipe").value,
      password: document.getElementById("oto_password").value,
    };
    const success = await setOtorisasiUser(data);
    if (success) closeModalOto();
  });

  if (closeOtoBtn) closeOtoBtn.addEventListener("click", closeModalOto);
  if (cancelOtoBtn) cancelOtoBtn.addEventListener("click", closeModalOto);

  document
    .getElementById("closeEditModal")
    .addEventListener("click", closeModal);
  document
    .getElementById("cancelEditUser")
    .addEventListener("click", closeModal);
  document.addEventListener("click", (e) => {
    if (e.target.closest(".quick-action")) {
      const action = e.target.closest(".quick-action").dataset.action;
      handleQuickAction(action);
    }
  });
};
const modal = () => {
  document.getElementById("editUserModal").classList.remove("hidden");
};
const closeModal = () => {
  document.getElementById("editUserModal").classList.add("hidden");
};
const handleQuickAction = (action) => {
  const checkboxes = document.querySelectorAll(
    '#editMenus input[type="checkbox"]'
  );
  switch (action) {
    case "select-all":
      checkboxes.forEach((checkbox) => (checkbox.checked = true));
      break;
    case "clear-all":
      checkboxes.forEach((checkbox) => (checkbox.checked = false));
      break;
  }
};
init();
