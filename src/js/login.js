document.getElementById("login").addEventListener("submit", async (e) => {
  e.preventDefault();
  try {
    let response = await fetch("/src/auth/post_data_user.php", {
      method: "POST",
      headers: {
        "Authorization": "Bearer " + localStorage.getItem("token"),
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        name: document.getElementById("name").value,
        pass: document.getElementById("pass").value
      })
    });

    let text = await response.text();  // Dapatkan respons dalam teks
    let data = JSON.parse(text);  // Coba parsing JSON

    if (data.status === "success") {
      const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 1000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer;
          toast.onmouseleave = Swal.resumeTimer;
        }
      });
      Toast.fire({
        icon: "success",
        title: "Berhasil, Silahkan Tunggu"
      });
      localStorage.setItem("token", data.token);
      // Redirect jika login berhasil
      if(data.user.username !== "shopee") {
        setTimeout(() => {
          if(data.user.username === "shopee") {
            window.location.href = "/src/fitur/shopee/produk_shopee";
          }
          window.location.href = "/in_beranda";
        }, 200); // Tambah delay 200ms setelah toast selesai
      }else {
          setTimeout(() => {
              window.location.href = "/src/fitur/shopee/produk_shopee";
          }, 200); // Tambah delay 200ms setelah toast selesai
      }
    } else {
      const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 1000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer;
          toast.onmouseleave = Swal.resumeTimer;
        }
      });
      Toast.fire({
        icon: "error",
        title: "Login Gagal, Isi Data Atau Data Tidak Cocok"
      });
    }
  } catch (error) {
    console.error("Error:", error);
  }
});

