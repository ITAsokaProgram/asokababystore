import getCookie from "../../index/utils/cookies.js";
export const editUser = async (kode, formData) => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch(`/src/api/user/edit_user_in?kode=${kode}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(formData),
    });
    if (response.status === 200) {
      const data = await response.json();
      return data;
    } else if (response.status === 401) {
      Swal.fire({
        icon: "error",
        title: "Sesi Berakhir",
        text: "Silahkan Login Kembali",
        confirmButtonText: "Login",
      }).then(() => {
        window.location.href = "/in_login";
      });
    } else if (response.status === 500) {
      throw new Error("Server Error");
    }
  } catch (error) {
    throw error;
  }
};
export const resetPassword = async (data) => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/user/reset_password_user_in", {
      method: "PUT",
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });
    if (response.status === 200) {
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "Berhasil ganti password",
      }).then(() => {
        location.reload();
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: `Gagal ganti password`,
      });
    }
  } catch (error) {
    console.log("Error in", error);
    Swal.fire({
      icon: "error",
      title: "Gagal",
      text: "Server Error, jika error terus hub IT Pusat",
    });
  }
};
export const displayEditUserModal = (userData) => {
  const form = document.querySelector("form");
  form.querySelector("#editUserId").value = userData.kode;
  form.querySelector("#editNama").value = userData.nama;
  form.querySelector("#editUsername").value = userData.inisial;
  form.querySelector("#editPosition").value = userData.hak;
  form.querySelector("#editCabang").value = userData.kode_cabang;
  const domCheckboxes = form.querySelectorAll(
    "#editMenus input[type='checkbox']"
  );
  domCheckboxes.forEach((cb) => (cb.checked = false));
  const visibleMenuValues = Array.from(domCheckboxes).map((cb) => cb.value);
  const currentUserMenus = Array.isArray(userData.menu_code)
    ? userData.menu_code
    : [];
  const hiddenMenus = currentUserMenus.filter(
    (menuCode) => !visibleMenuValues.includes(menuCode)
  );
  currentUserMenus.forEach((menu) => {
    const checkbox = form.querySelector(`#editMenus input[value="${menu}"]`);
    if (checkbox) checkbox.checked = true;
  });
  form.onsubmit = async (e) => {
    e.preventDefault();
    const id_user = form.querySelector("#editUserId").value;
    const name = form.querySelector("#editNama").value;
    const username = form.querySelector("#editUsername").value;
    const position = form.querySelector("#editPosition").value;
    const kode_cabang = form.querySelector("#editCabang").value;
    const selectedVisibleMenus = [];
    const checkboxes = form.querySelectorAll(
      '#editMenus input[type="checkbox"]'
    );
    checkboxes.forEach((cb) => {
      if (cb.checked) selectedVisibleMenus.push(cb.value);
    });
    const finalMenus = [...selectedVisibleMenus, ...hiddenMenus];
    const payload = {
      id_user,
      name,
      username,
      position,
      menus: finalMenus,
      kode_cabang,
    };
    try {
      await editUser(id_user, payload);
      Swal.fire("Berhasil", "User berhasil diperbarui", "success").then(() => {
        window.location.reload();
      });
    } catch (error) {
      console.error("Error updating user:", error);
      Swal.fire("Gagal", "Terjadi kesalahan saat memperbarui user", "error");
    }
  };
};
export const bulkGrantAccess = async () => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/user/bulk_access_all.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });

    const result = await response.json();

    if (response.status === 200) {
      return { status: "success", message: result.details };
    } else {
      throw new Error(result.message || "Gagal memproses permintaan");
    }
  } catch (error) {
    throw error;
  }
};

export default { displayEditUserModal, resetPassword, bulkGrantAccess };
