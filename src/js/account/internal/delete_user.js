export const deleteUser = async (kode) => {
  const token = getCookie("token");
  try {
    const response = await fetch(`/src/api/user/delete_user_in`, {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ kode: kode }),
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

export default { deleteUser };
