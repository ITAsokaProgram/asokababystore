// src/js/user_supplier/login_handler.js
document.getElementById("loginSupplierForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // Disable button loading state
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
  
    try {
      let response = await fetch("/src/api/user_supplier/post_login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          email: email,
          password: password
        })
      });
  
      let data = await response.json();
  
      if (data.status === "success") {
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true
        });
        
        Toast.fire({
          icon: "success",
          title: `Selamat Datang, ${data.user.nama}`
        });
  
        // Simpan token di localStorage juga (opsional, jika app butuh akses JS ke token)
        localStorage.setItem("supplier_token", data.token);
        localStorage.setItem("supplier_data", JSON.stringify(data.user));
  
        // Redirect setelah delay
        setTimeout(() => {
          // Ganti url ini sesuai halaman dashboard supplier Anda
          window.location.href = "/supplier_dashboard"; 
        }, 1000);
  
      } else {
        throw new Error(data.message || "Login gagal");
      }
  
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Gagal Masuk",
        text: error.message,
        confirmButtonColor: "#3b82f6" // Blue color
      });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
  });