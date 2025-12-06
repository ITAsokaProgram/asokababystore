import { sendRequestJSON } from "../utils/api_helpers.js";

document.addEventListener("DOMContentLoaded", () => {
  const dropZone = document.getElementById("drop-zone");
  const fileInput = document.getElementById("file_excel");
  const fileNameDisplay = document.getElementById("file-name-display");
  const fileNameText = document.getElementById("file-name-text");
  const form = document.getElementById("formImport");
  const resultContainer = document.getElementById("result-container");
  const resultContent = document.getElementById("result-content");
  const storeSelect = document.getElementById("kode_store");

  // --- Drag and Drop Logic ---
  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, preventDefaults, false);
  });

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  ["dragenter", "dragover"].forEach((eventName) => {
    dropZone.addEventListener(eventName, () =>
      dropZone.classList.add("bg-indigo-100", "border-indigo-400")
    );
  });

  ["dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, () =>
      dropZone.classList.remove("bg-indigo-100", "border-indigo-400")
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

  // --- Form Submit Logic ---
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!storeSelect.value) {
      Swal.fire("Perhatian", "Pilih Cabang/Store terlebih dahulu", "warning");
      return;
    }

    if (!fileInput.files.length) {
      Swal.fire("Perhatian", "Pilih file Excel terlebih dahulu", "warning");
      return;
    }

    const formData = new FormData();
    formData.append("file_excel", fileInput.files[0]);
    formData.append("kode_store", storeSelect.value);

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

      // PERUBAHAN URL API KE KELUARAN
      const response = await fetch(
        "/src/api/coretax/process_import_keluaran.php",
        {
          method: "POST",
          headers: {
            Authorization: `Bearer ${token}`,
          },
          body: formData,
        }
      );

      const result = await response.json();

      if (result.success) {
        let msgHtml = result.message.replace(/\n/g, "<br>");

        let swalConfig = {
          title: "Selesai!",
          html: msgHtml,
          icon: "success",
          allowOutsideClick: false,
        };

        if (result.has_duplicates && result.duplicate_file) {
          swalConfig.showCancelButton = true;
          swalConfig.confirmButtonText = "Tutup";
          swalConfig.cancelButtonText =
            '<i class="fa-solid fa-file-excel"></i> Download Data Duplikat';
          swalConfig.cancelButtonColor = "#d33";
          swalConfig.reverseButtons = true;
        }

        Swal.fire(swalConfig).then((action) => {
          if (
            action.dismiss === Swal.DismissReason.cancel &&
            result.duplicate_file
          ) {
            downloadBase64File(
              result.duplicate_file,
              "Data_Import_Keluaran_Duplikat.xlsx"
            );
          }
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
      Swal.fire({
        title: "Error",
        html: error.message.replace(/\n/g, "<br>"),
        icon: "error",
      });
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  });

  function downloadBase64File(base64String, fileName) {
    const link = document.createElement("a");
    link.href =
      "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," +
      base64String;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
});
