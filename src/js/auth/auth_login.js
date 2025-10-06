import { closeLoadingScreen, showLoadingScreen } from "../validation_ui/loading_screen_login.js";

export const pubsLogin = async (email, password) => {
  try {
    showLoadingScreen();
    const response = await fetch('/src/auth/login_pubs', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email: email, pass: password }),
    });

    const data = await response.json();
    if (response.ok && data.User.status === 'success') {
      // Tampilkan notifikasi sukses jika Swal tersedia
      await Swal.fire({
        icon: 'success',
        title: 'Login Berhasil',
        text: `Selamat datang kembali!`,
        timer: 1000,
        showConfirmButton: false,
        background: '#fff',
        customClass: {
          popup: 'rounded-xl shadow-xl border-2 border-pink-100',
          title: 'text-pink-600 font-bold',
          content: 'text-gray-600'
        }
      }).then(()=>{
        closeLoadingScreen()
        window.location.href = '/customer/home'
      })
      return data;
    } else {
      console.error('Login failed:', data);
      closeLoadingScreen()
      // Tampilkan notifikasi error jika Swal tersedia
      await Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: data.message || 'Terjadi kesalahan saat login. Data tidak valid.',
        confirmButtonColor: '#f87171',
        background: '#fff',
        customClass: {
          popup: 'rounded-xl shadow-xl border-2 border-pink-100',
          title: 'text-pink-600 font-bold',
          content: 'text-gray-600'
        }
      });
      throw new Error(data.message || 'Login failed');
    }

  } catch (error) {
    closeLoadingScreen();
    throw error;
  }
};

export const pubsLoginByPhone = async (phone) => {
  try {
    const response = await fetch('/src/auth/login_pubs_phone', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ phone: phone }),
    });

    const data = await response.json();
    if (response.ok && data.status === 'success') {
      await Swal.fire({
        icon: 'success',
        title: 'Login Berhasil',
        text: `Selamat datang kembali!`,
        timer: 1000,
        showConfirmButton: false,
        background: '#fff',
        customClass: {
          popup: 'rounded-xl shadow-xl border-2 border-pink-100',
          title: 'text-pink-600 font-bold',
          content: 'text-gray-600'
        }
      });
      return data;
    } else {
      console.error('Login by phone failed:', data);
      // Tampilkan notifikasi error jika Swal tersedia
      await Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: data.message || 'Terjadi kesalahan saat login. Data tidak valid.',
        confirmButtonColor: '#f87171',
        background: '#fff',
        customClass: {
          popup: 'rounded-xl shadow-xl border-2 border-pink-100',
          title: 'text-pink-600 font-bold',
          content: 'text-gray-600'
        }
      });
      throw new Error(data.message || 'Login by phone failed');
    }

  } catch (error) {
    throw error;
  }
}

export default { pubsLogin, pubsLoginByPhone };
