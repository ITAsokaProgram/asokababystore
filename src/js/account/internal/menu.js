import { menuAccess } from "./fetch.js";

const sidebarMenu = async () => {
  document.addEventListener("DOMContentLoaded", async () => {
    try {
      const allowedMenus = await menuAccess(); // array: ['dashboard', 'user_management', ...]

      // Ambil semua elemen dengan atribut data-menu
      const menuElements = document.querySelectorAll("[data-menu]");

      menuElements.forEach((el) => {
        const code = el.getAttribute("data-menu");

        // Jika menu tidak ada di daftar allowed, hapus dari DOM
        if (!allowedMenus.includes(code)) {
          el.remove(); // ❌ buang dari halaman
        }
      });

    } catch (error) {
      console.error("Gagal mengambil akses menu:", error);
    }
  });
};

sidebarMenu();
