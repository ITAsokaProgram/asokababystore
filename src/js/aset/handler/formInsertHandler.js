import getCookie from "../../index/utils/cookies.js";
import { api } from "../services/api.js";

export const formInsertHandler = () => {
 const form = document.getElementById("assetForm");
  const imageInput = form.querySelector("#image");
  const imagePreview = form.querySelector("#imagePreview");
  const previewImg = imagePreview.querySelector("img");

  imagePreview.classList.add("hidden");
  imageInput.addEventListener("change", (e) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];
      const MAX_SIZE_MB = 10; 

      if (file.size > MAX_SIZE_MB * 1024 * 1024) {
        Swal.fire({
          icon: 'error',
          title: 'Ukuran File Terlalu Besar',
          text: `Ukuran file maksimal adalah ${MAX_SIZE_MB} MB.`,
        });
        e.target.value = ''; 
        imagePreview.classList.add("hidden");
        return; 
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        previewImg.src = e.target.result;
        imagePreview.classList.remove("hidden");
      };
      reader.readAsDataURL(file);
    }
  });

  // Handle form submission
  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      // Validate required fields
      const requiredFields = [
        "nama_barang",
        "merk",
        "tanggal_beli",
        "harga_beli",
        "nama_toko",
        "kd_store",
      ];
      const missingFields = [];

      requiredFields.forEach((field) => {
        if (!form[field].value.trim()) {
          missingFields.push(field);
        }
      });

      if (missingFields.length > 0) {
        throw new Error(
          `Field berikut harus diisi: ${missingFields.join(", ")}`
        );
      }

      const formData = new FormData(form);
      const token = getCookie("token");

      if (!token) {
        throw new Error("Sesi login tidak valid");
      }

      // Reset form and close modal
      const modal = document.getElementById("addAssetModal");
      Swal.fire({
        title: "Menyimpan...",
        text: "Mohon tunggu sebentar",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });
      await api.insertDataAset(token, formData);
      if (modal) modal.classList.add("hidden");
      form.reset();
      imagePreview.classList.add("hidden");
      previewImg.src = "";
      // imagePreview.classList.add("hidden");
    } catch (error) {
      console.error("Form submission error:", error);
      await Swal.fire({
        title: "Error!",
        text: error.message,
        icon: "error",
      });
    }
  });
};
