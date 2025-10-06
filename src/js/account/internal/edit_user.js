import getCookie from "../../index/utils/cookies.js";

export const editUser = async (kode, formData) => {
  const token = getCookie("token");
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
  const token = getCookie("token");
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

  // Isi input field
  form.querySelector("#editUserId").value = userData.kode;
  form.querySelector("#editNama").value = userData.nama;
  form.querySelector("#editUsername").value = userData.inisial;
  form.querySelector("#editPosition").value = userData.hak;
  form.querySelector("#editCabang").value = userData.kode_cabang;

  // Reset semua checkbox
  const checkboxes = form.querySelectorAll("#editMenus input[type='checkbox']");
  checkboxes.forEach((cb) => (cb.checked = false));

  // Centang akses menu yang sesuai response
  if (Array.isArray(userData.menu_code)) {
    userData.menu_code.forEach((menu) => {
      const checkbox = form.querySelector(`#editMenus input[value="${menu}"]`);
      if (checkbox) checkbox.checked = true;
    });
  }

  form.onsubmit = async (e) => {
    e.preventDefault();

    // Ambil nilai dari form manual
    const id_user = form.querySelector("#editUserId").value;
    const name = form.querySelector("#editNama").value;
    const username = form.querySelector("#editUsername").value;
    const position = form.querySelector("#editPosition").value;
    const kode_cabang = form.querySelector("#editCabang").value;

    // Ambil semua menu yang dicentang
    const menus = [];
    const checkboxes = form.querySelectorAll(
      '#editMenus input[type="checkbox"]'
    );
    checkboxes.forEach((cb) => {
      if (cb.checked) menus.push(cb.value);
    });

    // Kirim sebagai JSON object
    const payload = {
      id_user,
      name,
      username,
      position,
      menus,
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

export default { displayEditUserModal, resetPassword };
