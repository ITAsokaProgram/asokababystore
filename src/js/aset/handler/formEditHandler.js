import getCookie from "../../index/utils/cookies.js";
import { api } from "../services/api.js";

export const formEditHandler = () => {
  const form = document.getElementById("editAssetForm");
  const imageInput = form.querySelector("#edit_image");
  const imagePreview = form.querySelector("#editImagePreview");
  const previewImg = imagePreview.querySelector("img");
  const hiddenId = form.querySelector("#edit_idhistory_aset");

  // Handle image preview
  imageInput.addEventListener("change", (e) => {
    if (e.target.files && e.target.files[0]) {
      const reader = new FileReader();
      reader.onload = (e) => {
        previewImg.src = e.target.result;
        imagePreview.classList.remove("hidden");
      };
      reader.readAsDataURL(e.target.files[0]);
    }
  });

  // Handle form submission
  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      // Validate required fields
      const requiredFields = [
        "edit_nama_barang",
        "edit_merk",
        "edit_tanggal_beli",
        "edit_harga_beli",
        "edit_nama_toko",
        "edit_kd_store",
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
      const token = getCookie("admin_token");

      if (!token) {
        throw new Error("Sesi login tidak valid");
      }

      Swal.fire({
        title: "Menyimpan...",
        text: "Mohon tunggu sebentar",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      await api.editDataAset(token, formData);
      await Swal.fire({
        title: "Success",
        text: "Data berhasil diupdate",
        icon: "success",
      });

      // Reset form and image preview
      form.reset();
      imagePreview.classList.add("hidden");
      previewImg.src = "";

      // Close modal
      const modal = document.getElementById("editAssetModal");
      if (modal) modal.classList.add("hidden");

      // Refresh table to show updated data
      if (typeof window.renderAsetTable === "function") {
        window.renderAsetTable({ resetPage: false });
      }
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
