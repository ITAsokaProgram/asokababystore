import { deleteUser } from "./delete_user.js";
import { displayEditUserModal, resetPassword } from "./edit_user.js";
import { getUser, getUserEdit } from "./fetch.js";
import { paginationUserInternal } from "./pagination.js";
import { renderTableUserInternal } from "./table.js";
import { initSearch, getFilteredData } from "./search.js";
import { areaCabang } from "./../../kode_cabang/cabang_area.js";

export const init = async () => {
  const token = getCookie("token");
  const response = await getUser();
  let passwordConfirm;
  // Initialize search functionality
  initSearch(response.data);

  // Make renderTableUserInternal globally available for search
  window.renderTableUserInternal = renderTableUserInternal;

  // Update statistics
  updateStatistics(response.data);

  paginationUserInternal(1, 10, response.data, renderTableUserInternal);

  document.addEventListener("click", async (e) => {
    const button = e.target.closest(".edit");
    if (!button) return;

    const kode = button.getAttribute("data-kode");

    try {
      const response = await getUserEdit(kode);
      const user = response.data; // atau langsung response kalau tanpa `.data`

      // Tampilkan form modal
      displayEditUserModal(user);

      // Isi select cabang 1 nilai
      const selectCabang = document.getElementById("editCabang");
      selectCabang.innerHTML = ""; // kosongkan dulu
      const option = document.createElement("option");
      option.value = user.kode_cabang;
      option.textContent = `${
        user.kode_cabang === "Pusat" ? "Pusat" : user.nama_cabang
      }`;
      selectCabang.appendChild(option);
      await areaCabang("editCabang");
      // Tampilkan modal
      modal();
    } catch (error) {
      console.error("Error fetching user data:", error);
    }
  });
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
  document
    .getElementById("closeEditModal")
    .addEventListener("click", closeModal);
  document
    .getElementById("cancelEditUser")
    .addEventListener("click", closeModal);

  // Quick action buttons functionality
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
