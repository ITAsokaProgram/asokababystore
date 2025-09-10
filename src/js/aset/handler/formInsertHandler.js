import getCookie from "../../index/utils/cookies.js";
import { api } from "../services/api.js";

export const formInsertHandler = () => {
  const form = document.getElementById("assetForm");
  const imageInput = document.getElementById("image");
  const imagePreview = document.getElementById("imagePreview");
  const previewImg = imagePreview.querySelector("img");
  const hiddenId = document.getElementById('idhistory_aset');

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
      const requiredFields = ['nama_barang', 'merk', 'harga_beli', 'nama_toko', 'kd_store'];
      const missingFields = [];
      
      requiredFields.forEach(field => {
        if (!form[field].value.trim()) {
          missingFields.push(field);
        }
      });

      if (missingFields.length > 0) {
        throw new Error(`Field berikut harus diisi: ${missingFields.join(', ')}`);
      }

      const formData = new FormData(form);
      const token = getCookie("token");

      if (!token) {
        throw new Error('Sesi login tidak valid');
      }

      // Decide insert or edit based on hidden id
      if (hiddenId && hiddenId.value) {
        // edit
        await api.editDataAset(token, formData);
        await Swal.fire({ title: 'Success', text: 'Data berhasil diupdate', icon: 'success' });
        // Reset form and close modal
        form.reset();
        imagePreview.classList.add('hidden');
        const modal = document.getElementById('addAssetModal');
        if (modal) modal.classList.add('hidden');
      } else {
        // insert
        await api.insertDataAset(token, formData);
      }
      
    } catch (error) {
      console.error('Form submission error:', error);
      await Swal.fire({
        title: "Error!",
        text: error.message,
        icon: "error"
      });
    }
  });
};
