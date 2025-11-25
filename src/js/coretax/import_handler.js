import { sendRequestJSON } from "../utils/api_helpers.js";
document.addEventListener("DOMContentLoaded", () => {
  console.log("TEST");
  const dropZone = document.getElementById("drop-zone");
  const fileInput = document.getElementById("file_excel");
  const fileNameDisplay = document.getElementById("file-name-display");
  const fileNameText = document.getElementById("file-name-text");
  const form = document.getElementById("formImport");
  const resultContainer = document.getElementById("result-container");
  const resultContent = document.getElementById("result-content");
  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, preventDefaults, false);
  });
  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }
  ["dragenter", "dragover"].forEach((eventName) => {
    dropZone.addEventListener(eventName, () =>
      dropZone.classList.add("bg-pink-100", "border-pink-400")
    );
  });
  ["dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, () =>
      dropZone.classList.remove("bg-pink-100", "border-pink-400")
    );
  });
  dropZone.addEventListener("drop", (e) => {
    const dt = e.dataTransfer;
    const files = dt.files;
    fileInput.files = files;
    updateFileName(files[0]);
  });
  fileInput.addEventListener("change", function () {
    updateFileName(this.files[0]);
  });
  function updateFileName(file) {
    if (file) {
      fileNameDisplay.classList.remove("hidden");
      fileNameText.textContent = `${file.name} (${(file.size / 1024).toFixed(
        1
      )} KB)`;
    }
  }
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!fileInput.files.length) {
      Swal.fire("Perhatian", "Pilih file Excel terlebih dahulu", "warning");
      return;
    }
    const formData = new FormData();
    formData.append("file_excel", fileInput.files[0]);
    const btn = document.getElementById("btn-submit");
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
      '<i class="fa-solid fa-spinner fa-spin"></i> Memproses Data...';
    resultContainer.classList.add("hidden");
    try {
      const token = document.cookie.match(
        "(^|;)\\s*admin_token\\s*=\\s*([^;]+)"
      )?.[2];
      const response = await fetch("/src/api/coretax/process_import.php", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
        },
        body: formData,
      });
      const result = await response.json();
      if (result.success) {
        Swal.fire({
          title: "Selesai!",
          text: result.message,
          icon: "success",
        });
        if (result.logs && result.logs.length > 0) {
          resultContent.innerHTML = result.logs.join("<br>");
          resultContainer.classList.remove("hidden");
        }
        form.reset();
        fileNameDisplay.classList.add("hidden");
      } else {
        throw new Error(result.message || "Gagal memproses data");
      }
    } catch (error) {
      console.error(error);
      Swal.fire("Error", error.message, "error");
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  });
});
