import el from "../services/dom.js";
import { sendData } from "../services/api.js";
import { api } from "../services/api.js";
export const eventHandler = {
  init: function () {
    this.bindEvents();
    this.uploadImage();
    this.changeImgBtn();
    this.saveData();
    this.bindCropperEvents();
  },
  bindEvents: function () {
    // bind with proper context
    el.btnAdd.addEventListener("click", this.openModal.bind(this));
    el.closeModal.addEventListener("click", this.closeModal.bind(this));
    el.cancelBtn.addEventListener("click", this.closeModal.bind(this));
    // close when clicking outside modal content
    if (el.modal)
      el.modal.addEventListener("click", this.outsideCloseModal.bind(this));
  },
  openModal: function (product = {}) {
    if (el.nameInput) el.nameInput.value = product?.nama_produk || "";
    if (el.descriptionInput)
      el.descriptionInput.value = product?.deskripsi || "";
    if (el.priceInput) el.priceInput.value = product?.harga_jual || 0;
    if (el.stockInput) el.stockInput.value = product?.qty || 0;
    if (el.categoryInput) el.categoryInput.value = product?.kategori || "";
    if (el.cabang) el.cabang.value = product?.cabang || "";
    if (el.barcode) el.barcode.value = product?.barcode || "";
    if (el.plu) el.plu.value = product?.plu || "";

    // Reset image/cropper state
    this.resetCropperState();
    this.resetUploadButton();

    // Handle existing image for edit mode
    if (product?.image_url) {
      const croppedPreview = document.getElementById("croppedPreview");
      croppedPreview.src = product.image_url;
      document.getElementById("croppedResult").classList.remove("hidden");
      document.getElementById("uploadBtn").style.display = "none";
    }

    if (el.imageInput) el.imageInput.value = ""; // Reset file input
    if (el.modalTitle)
      el.modalTitle.textContent = product?.id ? "Edit Produk" : "Tambah Produk";

    if (el.modal) {
      el.modal.classList.remove("hidden");
      el.modal.classList.add("flex");
    }

    setTimeout(() => {
      if (el.barcode) el.barcode.focus();
    }, 100);
  },

  closeModal: function () {
    if (el.modal) el.modal.classList.add("hidden");
    if (el.productId) el.productId.value = "";

    // Reset cropper state when closing modal
    this.resetCropperState();
    this.resetUploadButton();
  },
  outsideCloseModal: function (event) {
    if (event.target === el.modal) {
      this.closeModal();
    }
  },
  uploadImage: function () {
    if (el.imageInput)
      el.imageInput.addEventListener(
        "change",
        this.handleImageUpload.bind(this)
      );
  },
  handleImageUpload: function (event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
      Swal.fire("Ukuran gambar terlalu besar", "Maksimal 2MB", "warning");
      return;
    }

    // Reset cropper state
    this.resetCropperState();

    const url = URL.createObjectURL(file);
    el.preview.src = url;

    // Show cropper container and hide upload button
    document.getElementById("cropperContainer").classList.remove("hidden");
    document.getElementById("uploadBtn").style.display = "none";

    // Initialize cropper
    this.initializeCropper();
  },

  initializeCropper: function () {
    // Destroy existing cropper if any
    if (this.cropper) {
      this.cropper.destroy();
    }

    // Initialize new cropper
    this.cropper = new Cropper(el.preview, {
      aspectRatio: 1,
      viewMode: 0,
      dragMode: 'move', // Default drag mode to move image
      autoCropArea: 1,
      responsive: true,
      restore: false,
      guides: true,
      center: true,
      highlight: false,
      cropBoxMovable: true,
      cropBoxResizable: true,
      toggleDragModeOnDblclick: true, // Double click to toggle between move/crop
      zoomable: true,
      zoomOnTouch: true,
      zoomOnWheel: true,
      wheelZoomRatio: 0.1,
      movable: true,
      rotatable: false, // Disable rotation for simplicity
      scalable: true,
      background: true,
      checkOrientation: true,
      modal: true,
    });
  },

  bindCropperEvents: function () {
    // Crop button
    const cropBtn = document.getElementById("cropBtn");
    const resetCropBtn = document.getElementById("resetCropBtn");
    const cancelCropBtn = document.getElementById("cancelCropBtn");
    const editCropBtn = document.getElementById("editCropBtn");
    const removeCropBtn = document.getElementById("removeCropBtn");
    const toggleDragMode = document.getElementById("toggleDragMode");

    if (cropBtn) {
      cropBtn.addEventListener("click", this.cropImage.bind(this));
    }

    if (resetCropBtn) {
      resetCropBtn.addEventListener("click", this.resetCrop.bind(this));
    }

    if (cancelCropBtn) {
      cancelCropBtn.addEventListener("click", this.cancelCrop.bind(this));
    }

    if (editCropBtn) {
      editCropBtn.addEventListener("click", this.editCrop.bind(this));
    }

    if (removeCropBtn) {
      removeCropBtn.addEventListener("click", this.removeCrop.bind(this));
    }

    if (toggleDragMode) {
      toggleDragMode.addEventListener("click", this.toggleDragMode.bind(this));
    }
  },

  cropImage: function () {
    if (!this.cropper) return;

    const canvas = this.cropper.getCroppedCanvas({
      width: 400,
      height: 400,
      minWidth: 256,
      minHeight: 256,
      maxWidth: 800,
      maxHeight: 800,
      fillColor: "#fff",
      imageSmoothingEnabled: true,
      imageSmoothingQuality: "high",
    });

    // Convert canvas to blob
    canvas.toBlob(
      (blob) => {
        if (blob) {
          // Create new File object from blob
          const croppedFile = new File([blob], "cropped-image.jpg", {
            type: "image/jpeg",
            lastModified: Date.now(),
          });

          // Update file input with cropped file
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(croppedFile);
          el.imageInput.files = dataTransfer.files;

          // Show cropped result
          const croppedPreview = document.getElementById("croppedPreview");
          croppedPreview.src = canvas.toDataURL("image/jpeg", 0.9);

          // Hide cropper, show result
          document.getElementById("cropperContainer").classList.add("hidden");
          document.getElementById("croppedResult").classList.remove("hidden");

          // Destroy cropper
          if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
          }
        }
      },
      "image/jpeg",
      0.9
    );
  },

  toggleDragMode: function () {
    if (!this.cropper) return;

    const currentMode = this.cropper.getDragMode();
    const toggleBtn = document.getElementById("toggleDragMode");
    const dragModeText = document.getElementById("dragModeText");

    if (currentMode === "move") {
      // Switch to crop mode
      this.cropper.setDragMode("crop");
      toggleBtn.className = "px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2 text-sm";
      toggleBtn.innerHTML = '<i class="fa-solid fa-crop"></i><span id="dragModeText">Mode: Crop</span>';
    } else {
      // Switch to move mode
      this.cropper.setDragMode("move");
      toggleBtn.className = "px-3 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors flex items-center gap-2 text-sm";
      toggleBtn.innerHTML = '<i class="fa-solid fa-arrows-alt"></i><span id="dragModeText">Mode: Geser</span>';
    }
  },

  resetCrop: function () {
    if (this.cropper) {
      this.cropper.reset();
    }
  },

  cancelCrop: function () {
    this.resetCropperState();
    this.resetUploadButton();
  },

  editCrop: function () {
    // Show cropper again
    document.getElementById("croppedResult").classList.add("hidden");
    document.getElementById("cropperContainer").classList.remove("hidden");

    // Reinitialize cropper with current image
    this.initializeCropper();
  },

  removeCrop: function () {
    this.resetCropperState();
    this.resetUploadButton();

    // Clear file input
    el.imageInput.value = "";
  },

  resetCropperState: function () {
    // Hide all crop-related elements
    document.getElementById("cropperContainer").classList.add("hidden");
    document.getElementById("croppedResult").classList.add("hidden");

    // Destroy cropper if exists
    if (this.cropper) {
      this.cropper.destroy();
      this.cropper = null;
    }
  },

  resetUploadButton: function () {
    document.getElementById("uploadBtn").style.display = "block";
    if (el.uploadBtn) {
      el.uploadBtn.innerHTML = `
        <i class="fa-solid fa-cloud-upload-alt text-3xl mb-2"></i>
        <div class="text-sm">Klik untuk pilih gambar</div>
      `;
    }
  },

  changeImgBtn: function () {
    el.uploadBtn.addEventListener("click", function () {
      el.imageInput.click();
    });
  },
  saveData: function () {
    el.productForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      // If productId is set -> update, else insert
      if (el.productId && el.productId.value) {
        await api.updateData();
      } else {
        await sendData();
      }
    });
  },

  editData: async function (id) {
    try {
      const resp = await api.getProductDetail(id);
      if (resp.success) {
        const product = resp.data;
        // set product id for edit mode
        if (el.productId) el.productId.value = product.id;
        this.openModal(product);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: resp.error || "Gagal mengambil data",
        });
      }
    } catch (error) {
      console.error("Error fetching product for edit:", error);
      Swal.fire({ icon: "error", title: "Error", text: "Terjadi kesalahan" });
    }
  },

  deleteData: async function deleteData(id) {
    const confirmed = await Swal.fire({
      title: "Konfirmasi Hapus",
      text: "Apakah Anda yakin ingin menghapus produk ini?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Hapus",
      cancelButtonText: "Batal",
    });

    if (confirmed.isConfirmed) {
      try {
        const resp = await api.deleteProduct(id);
        if (resp.success) {
          Swal.fire({
            title: "Berhasil",
            text: "Produk berhasil dihapus",
            icon: "success",
          });
          // Refresh or update the product list
        } else {
          Swal.fire({
            title: "Gagal",
            text: resp.error || "Terjadi kesalahan",
            icon: "error",
          });
        }
      } catch (error) {
        console.error("Error deleting product:", error);
        Swal.fire({
          title: "Error",
          text: "Terjadi kesalahan",
          icon: "error",
        });
      }
    }
  },
};
