import { deleteUser } from "./delete_user.js";
import {
  displayEditUserModal,
  resetPassword,
  bulkGrantAccess,
} from "./edit_user.js";
import { getUser, getUserEdit, setOtorisasiUser } from "./fetch.js";
import { paginationUserInternal } from "./pagination.js";
import { renderTableUserInternal } from "./table.js";
import { initSearch, getFilteredData } from "./search.js";
import { areaCabang } from "./../../kode_cabang/cabang_area.js";
import getCookie from "../../index/utils/cookies.js";
export const init = async () => {
  const token = getCookie("admin_token");
  const response = await getUser();
  initSearch(response.data);
  window.renderTableUserInternal = renderTableUserInternal;
  updateStatistics(response.data);
  paginationUserInternal(1, 10, response.data, renderTableUserInternal);
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
      option.textContent = `${
        user.kode_cabang === "Pusat" ? "Pusat" : user.nama_cabang
      }`;
      selectCabang.appendChild(option);
      await areaCabang("editCabang");
      modal();
    } catch (error) {
      console.error("Error fetching user data:", error);
    }
  });
  const btnGrantAll = document.getElementById("btnGrantAll");
  if (btnGrantAll) {
    btnGrantAll.addEventListener("click", async () => {
      Swal.fire({
        title: "Konfirmasi Akses Massal",
        html: `Anda akan memberikan <b>SELURUH AKSES</b> (Kecuali Dashboard Sales Graph)<br>kepada <b>SEMUA USER</b> yang aktif.<br><br>Proses ini tidak bisa dibatalkan!`,
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
            html: "Mohon tunggu sebentar, sedang mengupdate database.",
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
              window.location.reload();
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
        title: "Hapus User?",
        text: "Apakah Anda yakin ingin menghapus user ini?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Hapus",
        cancelButtonText: "Batal",
      }).then(async (result) => {
        if (result.isConfirmed) {
          const response = await deleteUser(parseInt(kode));
          if (response.status === "success") {
            Swal.fire("Berhasil", "User berhasil dihapus", "success").then(
              () => {
                window.location.reload();
              }
            );
          } else {
            Swal.fire("Gagal", response.message, "error");
          }
        }
      });
    } catch (error) {
      console.error("Error deleting user:", error);
    }
  });
  document.addEventListener("click", async (e) => {
    const button = e.target.closest(".reset");
    if (!button) return;
    const id = button.getAttribute("data-kode");
    const pass = document.getElementById("newPassword");
    const confirmPass = document.getElementById("confirmPassword");
    const modal = document.getElementById("resetPassword");
    const btnSendReset = document.getElementById("save-password");
    modal.classList.remove("hidden");
    confirmPass.addEventListener("input", () => {
      const showErrorPass = document.getElementById("error-password");
      if (confirmPass.value !== pass.value) {
        showErrorPass.classList.remove("hidden");
      } else {
        showErrorPass.classList.add("hidden");
      }
    });
    btnSendReset.addEventListener("click", async (e) => {
      const data = {
        id: id,
        pass: pass.value,
        confirmPass: confirmPass.value,
      };
      await resetPassword(data);
    });
  });
  document.getElementById("close-reset").addEventListener("click", (e) => {
    const modal = document.getElementById("resetPassword");
    modal.classList.add("hidden");
  });
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
    const today = new Date().toISOString().split("T")[0];
    document.getElementById("oto_tanggal").value = today;
    if (modalOto) modalOto.classList.remove("hidden");
  });
  if (formOto) {
    formOto.addEventListener("submit", async (e) => {
      e.preventDefault();
      const data = {
        kode_user: document.getElementById("oto_kode_user").value,
        tanggal: document.getElementById("oto_tanggal").value,
        password: document.getElementById("oto_password").value,
      };
      const success = await setOtorisasiUser(data);
      if (success) {
        closeModalOto();
      }
    });
  }
  [closeOtoBtn, cancelOtoBtn].forEach((el) => {
    if (el) el.addEventListener("click", closeModalOto);
  });
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
const updateStatistics = (userData) => {
  const totalUsers = userData.length;
  const activeUsers = userData.filter(
    (user) => user.status !== "inactive"
  ).length;
  const managers = userData.filter((user) => user.hak === "Manajer").length;
  document.getElementById("totalUsers").textContent = totalUsers;
  document.getElementById("activeUsers").textContent = activeUsers;
  document.getElementById("managers").textContent = managers;
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
