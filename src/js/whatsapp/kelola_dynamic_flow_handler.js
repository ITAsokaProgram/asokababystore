document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("table-body");
  const filterForm = document.getElementById("filter-form");
  const modalForm = document.getElementById("modal-form");
  const paginationContainer = document.getElementById("pagination-container");
  const btnAddData = document.getElementById("btn-add-data");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const btnCancel = document.getElementById("btn-cancel");
  const formTransaksi = document.getElementById("form-transaksi");
  const btnAddStep = document.getElementById("btn-add-step");
  const stepsContainer = document.getElementById("steps-container");
  const btnSave = document.getElementById("btn-save");
  const btnApplyTemplate = document.getElementById("btn-apply-template");
  const templateSelect = document.getElementById("template-select");
  const templateArea = document.getElementById("template-area");
  const API_BASE = "/src/api/whatsapp";
  const TEMPLATES = {
    promo_lokasi: {
      keyword: "VCR:PROMOSGM10K",
      deskripsi: "Promo SGM Eksplor 900G",
      pesan_habis: "Maaf Bunda, kuota promo SGM ini sudah habis.",
      pesan_sudah_klaim:
        "Bunda sudah pernah mengklaim voucher SGM ini sebelumnya.",
      steps: [
        {
          tipe_respon: "button",
          key_penyimpanan: "konfirmasi_minat",
          isi_pesan: {
            header: "Promo SGM Eksplor",
            body: "Bunda berhak mendapatkan potongan Rp 10.000 untuk pembelian SGM Eksplor 900G. Apakah Bunda ingin melanjutkan klaim?",
            footer: "Promo Terbatas",
            buttons: [
              { id: "ya", title: "Ya, Mau" },
              { id: "tidak", title: "Tidak" },
            ],
          },
        },
        {
          tipe_respon: "location_request",
          key_penyimpanan: "lokasi_user",
          isi_pesan: {
            body: "Baik Bunda. Untuk menentukan lokasi penukaran voucher, mohon *Share Location* (Berbagi Lokasi) Bunda saat ini ya.",
            calc_nearest: true,
          },
        },
        {
          tipe_respon: "list",
          key_penyimpanan: "cabang_terpilih",
          isi_pesan: {
            header: "Cabang Terdekat",
            body: "Berikut adalah hasil pencarian cabang terdekat dari lokasi Bunda. Silakan pilih salah satu untuk melanjutkan:",
            footer: "Asoka Baby Store",
            btn_text: "Pilih Cabang",
            sections: [],
          },
        },
        {
          tipe_respon: "generated_qr",
          isi_pesan: {
            qr_data: "VCR-SGM-{{cabang_terpilih}}",
            caption:
              " *Voucher Berhasil Dibuat* \n\nSilakan tunjukkan QR Code ini ke kasir.\n\n*Cabang:* {{cabang_terpilih}}\n*Potongan:* Rp 10.000\n\n_Voucher berlaku 1x24 jam_",
          },
        },
      ],
    },
    registrasi: {
      keyword: "REG:MEMBER",
      deskripsi: "Flow Pendaftaran Member Sederhana",
      pesan_habis: "",
      pesan_sudah_klaim: "",
      steps: [
        {
          tipe_respon: "save_input",
          key_penyimpanan: "nama_lengkap",
          isi_pesan: {
            body: "Halo Kak! Untuk mendaftar member, silakan ketik *Nama Lengkap* Kakak:",
          },
        },
        {
          tipe_respon: "save_input",
          key_penyimpanan: "no_hp",
          isi_pesan: {
            body: "Terima kasih {{nama_lengkap}}. Sekarang mohon ketik *Nomor WhatsApp* aktif:",
          },
        },
        {
          tipe_respon: "text",
          isi_pesan: {
            body: "Terima kasih! Pendaftaran berhasil.\nData:\nNama: {{nama_lengkap}}\nNo HP: {{no_hp}}\n\nAdmin kami akan segera menghubungi.",
          },
        },
      ],
    },
  };
  function createStepInput(
    data = { tipe_respon: "text", isi_pesan: "", key_penyimpanan: "" }
  ) {
    const wrapper = document.createElement("div");
    wrapper.className = "relative group animate-fade-in step-item pb-2";
    const currentCount = stepsContainer.children.length + 1;
    let contentObj = data.isi_pesan;
    if (
      typeof contentObj === "string" &&
      ["button", "list", "location_request"].includes(data.tipe_respon)
    ) {
      try {
        contentObj = JSON.parse(contentObj);
      } catch (e) {
        contentObj = {};
      }
    }
    if (!contentObj) contentObj = {};
    wrapper.innerHTML = `
            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200 shadow-sm transition-all hover:shadow-md">
                <div class="flex justify-between items-center mb-3 pb-2 border-b border-indigo-200">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-white bg-indigo-500 w-6 h-6 flex items-center justify-center rounded-full select-none step-number">${currentCount}</span>
                        <span class="text-xs font-bold text-gray-600">Tipe Respon:</span>
                        <select class="type-selector text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded px-2 py-1 focus:outline-none focus:border-indigo-500 cursor-pointer">
                            <option value="text" ${
                              data.tipe_respon === "text" ? "selected" : ""
                            }>📝 Kirim Teks</option>
                            <option value="save_input" ${
                              data.tipe_respon === "save_input"
                                ? "selected"
                                : ""
                            }>💾 Simpan Input User</option>
                            <option value="button" ${
                              data.tipe_respon === "button" ? "selected" : ""
                            }>🔘 Tombol Interaktif</option>
                            <option value="list" ${
                              data.tipe_respon === "list" ? "selected" : ""
                            }>📜 Menu Daftar (List)</option>
                            <option value="location_request" ${
                              data.tipe_respon === "location_request"
                                ? "selected"
                                : ""
                            }>📍 Minta Lokasi</option>
                            <option value="cta_url" ${
                              data.tipe_respon === "cta_url" ? "selected" : ""
                            }>🔗 CTA Link Button</option>
                            <option value="media" ${
                              data.tipe_respon === "media" ? "selected" : ""
                            }>🖼️ Kirim Gambar/Media</option>
                            <option value="generated_qr" ${
                              data.tipe_respon === "generated_qr"
                                ? "selected"
                                : ""
                            }>🔳 Generate QR Code</option>
                        </select>
                    </div>
                    <div class="flex gap-1">
                        <button type="button" class="btn-move-up w-7 h-7 flex items-center justify-center rounded hover:bg-indigo-100 text-gray-400 hover:text-indigo-600 transition-colors" title="Geser Naik"><i class="fas fa-arrow-up"></i></button>
                        <button type="button" class="btn-move-down w-7 h-7 flex items-center justify-center rounded hover:bg-indigo-100 text-gray-400 hover:text-indigo-600 transition-colors" title="Geser Turun"><i class="fas fa-arrow-down"></i></button>
                        <div class="w-[1px] h-6 bg-indigo-200 mx-1"></div>
                        <button type="button" class="btn-remove-step w-7 h-7 flex items-center justify-center rounded hover:bg-red-100 text-gray-400 hover:text-red-500 transition-colors" title="Hapus Step"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
                <div class="storage-key-area mb-3 ${
                  ["save_input", "location_request", "list", "button"].includes(
                    data.tipe_respon
                  )
                    ? ""
                    : "hidden"
                }">
                      <label class="block text-[10px] font-bold text-indigo-700 mb-1">
                        <i class="fas fa-database mr-1"></i> Simpan jawaban user ke variabel:
                      </label>
                      <input type="text" class="input-storage-key input-enhanced w-full px-3 py-1.5 border border-indigo-300 bg-white rounded text-sm placeholder-indigo-300 focus:outline-none focus:border-indigo-500" 
                        placeholder="Contoh: cabang_terpilih (tanpa spasi)" value="${
                          data.key_penyimpanan || ""
                        }">
                      <p class="text-[10px] text-gray-400 mt-0.5">Nanti bisa dipanggil di pesan lain dengan format {{nama_variabel}}</p>
                </div>
                <div class="content-area"></div>
            </div>
        `;
    const contentArea = wrapper.querySelector(".content-area");
    const typeSelector = wrapper.querySelector(".type-selector");
    const storageArea = wrapper.querySelector(".storage-key-area");
    const renderContentInputs = (type, val) => {
      contentArea.innerHTML = "";
      let html = "";
      const getHeaderInputHtml = (hType, hContent) => {
        const uniqueId = Math.random().toString(36).substr(2, 9);
        if (hType === "text") {
          return `<input type="text" class="input-header-content w-full px-3 py-2 border border-gray-300 rounded text-sm mt-2" placeholder="Isi Header Text (Bold)" value="${hContent}">`;
        } else if (hType === "image" || hType === "video") {
          let fileStatusHtml = "";
          if (hContent && hContent.startsWith("http")) {
            const fileName = hContent.split("/").pop();
            fileStatusHtml = `
                    <div class="mt-2 text-xs text-green-700 bg-green-50 p-2 rounded border border-green-200 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> 
                        <span>Terpasang: <a href="${hContent}" target="_blank" class="font-bold hover:underline truncate max-w-[200px]">${fileName}</a></span>
                    </div>`;
          }
          return `
                <div class="mt-2 header-media-wrapper p-3 bg-gray-50 rounded border border-gray-200">
                    <div class="flex gap-4 mb-2 text-xs font-bold text-gray-700">
                        <label class="flex items-center gap-2 cursor-pointer hover:text-pink-600">
                            <input type="radio" name="source_${uniqueId}" value="upload" class="radio-source text-pink-600 focus:ring-pink-500" checked> 
                            <i class="fas fa-upload"></i> Upload File
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-pink-600">
                            <input type="radio" name="source_${uniqueId}" value="url" class="radio-source text-pink-600 focus:ring-pink-500"> 
                            <i class="fas fa-link"></i> Input URL
                        </label>
                    </div>
                    <div class="input-container-file">
                        <input type="file" class="input-media-file w-full text-sm text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 cursor-pointer" accept="${hType}/*">
                        <p class="text-[10px] text-gray-400 mt-1">*Maksimal 5MB. Format: jpg, png, mp4.</p>
                    </div>
                    <div class="input-container-url hidden">
                        <input type="url" class="input-media-url-manual w-full px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-400 focus:border-pink-500" 
                        placeholder="https:" 
                        value="${
                          hContent && hContent.startsWith("http")
                            ? hContent
                            : ""
                        }">
                    </div>
                    <input type="hidden" class="input-header-content" value="${hContent}">
                    <div class="file-preview-area">${fileStatusHtml}</div>
                </div>`;
        }
        return "";
      };
      if (
        type === "text" ||
        type === "save_input" ||
        type === "location_request"
      ) {
        const bodyVal =
          typeof val === "object" && val.body
            ? val.body
            : typeof val === "string"
            ? val
            : "";
        html = `
                  <label class="block text-xs font-bold text-gray-600 mb-1 form-label-required">Isi Pesan / Pertanyaan:</label>
                  <textarea class="input-body w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-indigo-500" 
                      rows="3" placeholder="Masukkan teks pesan..." required>${bodyVal}</textarea>
             `;
        if (type === "location_request") {
          const isNearest = val && val.calc_nearest;
          html += `
                      <div class="mt-2 flex items-start gap-2 bg-blue-50 p-2 rounded border border-blue-200">
                          <input type="checkbox" class="input-calc-nearest mt-1 w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" id="calc_${Date.now()}" ${
            isNearest ? "checked" : ""
          }>
                          <label for="calc_${Date.now()}" class="text-xs text-blue-900 cursor-pointer">
                              <b>Aktifkan Pencarian Cabang Terdekat</b><br>
                              Jika dicentang, sistem akan menghitung jarak user ke semua cabang.<br>
                              <i>Step selanjutnya HARUS tipe "List" agar hasil cabang bisa ditampilkan otomatis.</i>
                          </label>
                      </div>
                  `;
        } else {
          html += `
              <div class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                  <i class="fas fa-info-circle"></i> Gunakan {{variable}} untuk menyisipkan data.
              </div>`;
        }
      } else if (type === "button") {
        const body = val.body || "";
        const footer = val.footer || "";
        const header = val.header || "";
        const buttons = val.buttons || [];
        let btnsHtml = buttons.map((b) => `${b.id}:${b.title}`).join("\n");
        html = `
                  <div class="grid grid-cols-1 gap-2">
                      <input type="text" class="input-header w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Header (Opsional / Bold)" value="${header}">
                      <div>
                          <label class="block text-xs font-bold text-gray-600 mb-1 form-label-required">Body Pesan:</label>
                          <textarea class="input-body w-full px-3 py-2 border border-gray-300 rounded text-sm" rows="2" placeholder="Body Pesan (Wajib)" required>${body}</textarea>
                      </div>
                      <input type="text" class="input-footer w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Footer (Opsional)" value="${footer}">
                      <div class="mt-1 bg-gray-50 p-2 rounded border border-gray-200">
                          <label class="block text-xs font-bold text-gray-600 mb-1 form-label-required">Daftar Tombol (Maks 3):</label>
                          <textarea class="input-buttons-raw w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono focus:bg-white transition-colors" rows="3" 
                              placeholder="ya:Ya, Mau\ntidak:Tidak" required>${btnsHtml}</textarea>
                          <p class="text-[10px] text-gray-400 mt-1">Format per baris: <b>ID_UNIQUE:Label Tombol</b></p>
                      </div>
                  </div>
              `;
      } else if (type === "generated_qr") {
        const qrData = val.qr_data || "";
        const caption = val.caption || "";
        html = `
                  <div class="grid grid-cols-1 gap-3">
                      <div>
                          <label class="block text-xs font-bold text-gray-600 mb-1 form-label-required">Data QR Code:</label>
                          <textarea class="input-qr-data w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono" rows="2" placeholder="Teks atau URL yang akan di-generate jadi QR" required>${qrData}</textarea>
                          <p class="text-[10px] text-gray-400 mt-1">Contoh: VOUCHER-{{nama_variabel}}</p>
                      </div>
                      <div>
                          <label class="block text-xs font-bold text-gray-600 mb-1">Caption:</label>
                          <textarea class="input-caption w-full px-3 py-2 border border-gray-300 rounded text-sm" rows="3">${caption}</textarea>
                      </div>
                  </div>
              `;
      } else if (type === "media") {
        const url = val.url || "";
        const filename = val.filename || "";
        const caption = val.caption || "";
        const medType = val.type || "image";
        let fileStatusHtml = "";
        if (url) {
          fileStatusHtml = `
                      <div class="mt-2 text-xs text-green-700 bg-green-50 p-2 rounded border border-green-200 flex items-center gap-2">
                          <i class="fas fa-check-circle"></i> 
                          <span>File tersimpan: <a href="${url}" target="_blank" class="font-bold hover:underline" title="${url}">${
            filename || "Lihat File"
          }</a></span>
                      </div>`;
        }
        html = `
                  <div class="space-y-3">
                      <div class="flex gap-2">
                          <div class="w-1/3">
                              <label class="block text-xs font-bold text-gray-600 mb-1">Tipe Media</label>
                              <select class="input-media-type w-full text-sm border border-gray-300 rounded px-2 py-2 bg-white focus:outline-none focus:border-indigo-500">
                                  <option value="image" ${
                                    medType === "image" ? "selected" : ""
                                  }>📷 Foto</option>
                                  <option value="document" ${
                                    medType === "document" ? "selected" : ""
                                  }>📄 Dokumen</option>
                                  <option value="video" ${
                                    medType === "video" ? "selected" : ""
                                  }>🎥 Video</option>
                              </select>
                          </div>
                          <div class="w-2/3">
                              <label class="block text-xs font-bold text-gray-600 mb-1">Upload File</label>
                              <input type="file" class="input-media-file w-full text-sm text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                          </div>
                      </div>
                      <input type="hidden" class="input-media-url" value="${url}">
                      <input type="hidden" class="input-media-filename" value="${filename}">
                      <div>
                          <label class="block text-xs font-bold text-gray-600 mb-1">Caption</label>
                          <input type="text" class="input-caption w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Keterangan gambar/file..." value="${caption}">
                      </div>
                      <div class="file-preview-area">${fileStatusHtml}</div>
                  </div>
               `;
      } else if (type === "list") {
        const header = val.header || "";
        const body = val.body || "";
        const footer = val.footer || "";
        const btnText = val.btn_text || "Menu";
        const sectionsJson = val.sections
          ? JSON.stringify(val.sections, null, 2)
          : "[]";
        html = `
                  <div class="grid grid-cols-1 gap-2">
                      <input type="text" class="input-header w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Header List" value="${header}">
                      <div>
                          <label class="block text-xs font-bold text-gray-600 mb-1 form-label-required">Body List (Wajib):</label>
                          <textarea class="input-body w-full px-3 py-2 border border-gray-300 rounded text-sm" rows="2" placeholder="Body List" required>${body}</textarea>
                      </div>
                      <div class="flex gap-2">
                          <input type="text" class="input-footer w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Footer" value="${footer}">
                          <div class="w-1/3">
                              <label class="block text-[10px] font-bold text-gray-600 mb-1 form-label-required">Tombol:</label>
                              <input type="text" class="input-btn-text w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Label Tombol" value="${btnText}" required>
                          </div>
                      </div>
                      <div>
                          <label class="block text-xs font-bold text-gray-600 mb-1">Konfigurasi Sections (Format JSON):</label>
                           <div class="mb-2 text-xs bg-blue-100 text-blue-800 p-2 rounded border border-blue-200">
                              <i class="fas fa-info-circle"></i> 
                              Jika Step sebelumnya adalah <b>Minta Lokasi (Cabang Terdekat)</b>, biarkan Sections ini kosong <code>[]</code>. Sistem akan mengisinya otomatis.
                          </div>
                          <textarea class="input-sections-json w-full px-3 py-2 border border-gray-300 rounded text-xs font-mono bg-gray-50 focus:bg-white" rows="5">${sectionsJson}</textarea>
                      </div>
                  </div>
              `;
      } else if (type === "cta_url") {
        const headerType = val.header_type || "none";
        const headerContent = val.header_content || "";
        const body = val.body || "";
        const footer = val.footer || "";
        const displayText = val.display_text || "Lihat Detail";
        const url = val.url || "";
        html = `
          <div class="space-y-3 bg-white p-2 rounded">
              <div class="border-b pb-3 border-gray-100">
                  <div class="flex items-center justify-between mb-1">
                      <label class="block text-xs font-bold text-gray-600">Header (Opsional)</label>
                      <select class="input-header-type text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:border-pink-500">
                          <option value="none" ${
                            headerType === "none" ? "selected" : ""
                          }>Tanpa Header</option>
                          <option value="text" ${
                            headerType === "text" ? "selected" : ""
                          }>Teks (Bold)</option>
                          <option value="image" ${
                            headerType === "image" ? "selected" : ""
                          }>Gambar (Image)</option>
                          <option value="video" ${
                            headerType === "video" ? "selected" : ""
                          }>Video</option>
                      </select>
                  </div>
                  <div class="header-content-area">${getHeaderInputHtml(
                    headerType,
                    headerContent
                  )}</div>
              </div>
              <div>
                  <label class="block text-xs font-bold text-gray-600 mb-1 form-label-required">Body Pesan:</label>
                  <textarea class="input-body w-full px-3 py-2 border border-gray-300 rounded text-sm focus:border-pink-500" rows="2" placeholder="Isi pesan utama..." required>${body}</textarea>
              </div>
              <div>
                  <input type="text" class="input-footer w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Footer (Opsional)" value="${footer}">
              </div>
              <div class="bg-pink-50 p-3 rounded border border-pink-100">
                  <label class="block text-xs font-bold text-pink-700 mb-2"><i class="fas fa-link"></i> Konfigurasi Tombol Link</label>
                  <div class="grid grid-cols-2 gap-3">
                      <div>
                          <label class="block text-[10px] text-gray-500 mb-1">Label Tombol (Max 20 char)</label>
                          <input type="text" class="input-display-text w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Contoh: Buka Web" value="${displayText}" maxlength="20" required>
                      </div>
                      <div>
                          <label class="block text-[10px] text-gray-500 mb-1">URL Tujuan (https:)</label>
                          <input type="url" class="input-url w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                          placeholder="https:" 
                          value="${url}">
                      </div>
                  </div>
              </div>
          </div>
      `;
      }
      contentArea.innerHTML = html;
      if (type === "cta_url") {
        const headerSelect = contentArea.querySelector(".input-header-type");
        const headerArea = contentArea.querySelector(".header-content-area");
        const setupMediaListeners = () => {
          const wrapper = headerArea.querySelector(".header-media-wrapper");
          if (!wrapper) return;
          const radioSources = wrapper.querySelectorAll(".radio-source");
          const containerFile = wrapper.querySelector(".input-container-file");
          const containerUrl = wrapper.querySelector(".input-container-url");
          const fileInput = wrapper.querySelector(".input-media-file");
          const urlInput = wrapper.querySelector(".input-media-url-manual");
          const previewArea = wrapper.querySelector(".file-preview-area");
          const hiddenContent = wrapper.querySelector(".input-header-content");
          radioSources.forEach((radio) => {
            radio.addEventListener("change", (e) => {
              previewArea.innerHTML = "";
              hiddenContent.value = "";
              if (e.target.value === "upload") {
                containerFile.classList.remove("hidden");
                containerUrl.classList.add("hidden");
              } else {
                containerFile.classList.add("hidden");
                containerUrl.classList.remove("hidden");
              }
            });
          });
          if (fileInput) {
            fileInput.addEventListener("change", (e) => {
              if (e.target.files.length > 0) {
                const file = e.target.files[0];
                hiddenContent.value = "pending_upload";
                previewArea.innerHTML = `
                            <div class="mt-2 text-xs text-yellow-700 bg-yellow-50 p-2 rounded border border-yellow-200 flex items-center gap-2">
                                <i class="fas fa-arrow-circle-up animate-bounce"></i> 
                                <span>Akan diupload: <b>${file.name}</b> (${(
                  file.size / 1024
                ).toFixed(1)} KB)</span>
                            </div>`;
              }
            });
          }
          if (urlInput) {
            urlInput.addEventListener("input", (e) => {
              const val = e.target.value;
              hiddenContent.value = val;
              if (val.startsWith("http")) {
                previewArea.innerHTML = `
                            <div class="mt-2 text-xs text-blue-700 bg-blue-50 p-2 rounded border border-blue-200 flex items-center gap-2">
                                <i class="fas fa-link"></i> 
                                <span>Menggunakan URL External</span>
                            </div>`;
              }
            });
          }
        };
        headerSelect.addEventListener("change", (e) => {
          const newType = e.target.value;
          const newHtml = getHeaderInputHtml(newType, "");
          headerArea.innerHTML = newHtml;
          if (newType === "image" || newType === "video") {
            setupMediaListeners();
          }
        });
        const currentType = headerSelect.value;
        if (currentType === "image" || currentType === "video") {
          setupMediaListeners();
        }
      }
      if (type === "media") {
        const fInput = contentArea.querySelector(".input-media-file");
        const prevArea = contentArea.querySelector(".file-preview-area");
        const fNameInput = contentArea.querySelector(".input-media-filename");
        fInput.addEventListener("change", (e) => {
          if (e.target.files.length > 0) {
            const file = e.target.files[0];
            fNameInput.value = file.name;
            prevArea.innerHTML = `
                          <div class="mt-2 text-xs text-yellow-700 bg-yellow-50 p-2 rounded border border-yellow-200 flex items-center gap-2">
                              <i class="fas fa-arrow-circle-up animate-bounce"></i> 
                              <span>Akan diupload: <b>${file.name}</b> (${(
              file.size / 1024
            ).toFixed(1)} KB)</span>
                          </div>`;
          }
        });
      }
    };
    renderContentInputs(data.tipe_respon, contentObj);
    typeSelector.addEventListener("change", (e) => {
      const newType = e.target.value;
      if (
        ["save_input", "location_request", "list", "button"].includes(newType)
      ) {
        storageArea.classList.remove("hidden");
      } else {
        storageArea.classList.add("hidden");
      }
      renderContentInputs(newType, {});
    });
    wrapper.querySelector(".btn-remove-step").addEventListener("click", () => {
      if (stepsContainer.children.length > 1) {
        wrapper.classList.add("opacity-0", "transform", "scale-95");
        setTimeout(() => {
          wrapper.remove();
          renumberSteps();
        }, 200);
      } else {
        typeSelector.value = "text";
        renderContentInputs("text", "");
        storageArea.classList.add("hidden");
      }
    });
    wrapper.querySelector(".btn-move-up").addEventListener("click", () => {
      if (wrapper.previousElementSibling) {
        wrapper.parentNode.insertBefore(
          wrapper,
          wrapper.previousElementSibling
        );
        renumberSteps();
      }
    });
    wrapper.querySelector(".btn-move-down").addEventListener("click", () => {
      if (wrapper.nextElementSibling) {
        wrapper.parentNode.insertBefore(wrapper.nextElementSibling, wrapper);
        renumberSteps();
      }
    });
    return wrapper;
  }
  function renumberSteps() {
    Array.from(stepsContainer.children).forEach((child, index) => {
      const span = child.querySelector(".step-number");
      if (span) span.textContent = index + 1;
    });
  }
  btnAddStep.addEventListener("click", () => {
    const newStep = createStepInput();
    stepsContainer.appendChild(newStep);
    newStep.scrollIntoView({ behavior: "smooth", block: "center" });
  });
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    return {
      search_keyword: (params.get("search_keyword") || "").trim(),
      page: parseInt(params.get("page") || "1", 10),
    };
  }
  async function loadData() {
    const urlParams = getUrlParams();
    const inputSearch = filterForm.querySelector(
      'input[name="search_keyword"]'
    );
    if (inputSearch) inputSearch.value = urlParams.search_keyword;
    tableBody.innerHTML = `
            <tr><td colspan="7" class="text-center p-8">
                <div class="spinner-simple"></div>
                <p class="mt-3 text-gray-500">Memuat data...</p>
            </td></tr>`;
    const queryString = new URLSearchParams({
      search_keyword: urlParams.search_keyword,
      page: urlParams.page,
    }).toString();
    try {
      const response = await fetch(
        `${API_BASE}/get_data_dynamic_flow.php?${queryString}`
      );
      const result = await response.json();
      if (result.error) throw new Error(result.error);
      renderTable(result.data);
    } catch (error) {
      console.error(error);
      tableBody.innerHTML = `
                <tr><td colspan="7" class="text-center p-8 text-red-600">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Gagal memuat data: ${error.message}</p>
                </td></tr>`;
    }
  }
  function renderTable(data) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="7" class="text-center p-12 text-gray-500"><p>Tidak ada data flow ditemukan</p></td></tr>`;
      return;
    }
    tableBody.innerHTML = data
      .map((row, index) => {
        const statusBadge =
          row.status_aktif == 1
            ? `<span class="badge-status badge-aktif"><i class="fas fa-check-circle"></i> Aktif</span>`
            : `<span class="badge-status badge-nonaktif"><i class="fas fa-times-circle"></i> Non-Aktif</span>`;
        let limitInfo = "";
        if (row.max_global_usage > 0)
          limitInfo += `<div><span class="text-[10px] bg-gray-100 px-1 rounded text-gray-600">Global: ${row.current_global_usage}/${row.max_global_usage}</span></div>`;
        if (row.expired_at) {
          const expDate = new Date(row.expired_at);
          limitInfo += `<div><span class="text-[10px] text-red-500"><i class="far fa-clock"></i> ${expDate.toLocaleDateString()}</span></div>`;
        }
        if (!limitInfo) limitInfo = "<span class='text-gray-400'>-</span>";
        const rowDataString = encodeURIComponent(JSON.stringify(row));
        return `
                <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100">
                    <td class="text-center font-semibold text-gray-500 py-3">${
                      index + 1
                    }</td>
                    <td>
                        <div class="font-bold text-indigo-700">${
                          row.keyword
                        }</div>
                    </td>
                    <td class="text-sm text-gray-600 truncate max-w-[200px]">${
                      row.deskripsi || "-"
                    }</td>
                    <td class="text-center font-bold text-gray-700">${
                      row.total_steps || 0
                    }</td>
                    <td class="">${limitInfo}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="window.editFlow('${rowDataString}')" class="rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 w-8 h-8 flex items-center justify-center transition-colors" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                            <button onclick="window.deleteFlow('${row.id}', '${
          row.keyword
        }')" class="rounded-lg bg-red-50 text-red-600 hover:bg-red-100 w-8 h-8 flex items-center justify-center transition-colors" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>
                </tr>`;
      })
      .join("");
  }
  if (btnApplyTemplate) {
    btnApplyTemplate.addEventListener("click", () => {
      const selectedKey = templateSelect.value;
      if (!selectedKey) {
        Swal.fire(
          "Pilih Template",
          "Silakan pilih salah satu template terlebih dahulu.",
          "info"
        );
        return;
      }
      const data = TEMPLATES[selectedKey];
      if (!data) return;
      Swal.fire({
        title: "Terapkan Template?",
        text: "Data form saat ini akan diganti dengan data template.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Terapkan",
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById("keyword").value = data.keyword;
          document.getElementById("deskripsi").value = data.deskripsi;
          document.getElementById("pesan_habis").value = data.pesan_habis || "";
          document.getElementById("pesan_sudah_klaim").value =
            data.pesan_sudah_klaim || "";
          stepsContainer.innerHTML = "";
          data.steps.forEach((stepData) => {
            const stepEl = createStepInput(stepData);
            stepsContainer.appendChild(stepEl);
          });
          const toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
          });
          toast.fire({
            icon: "success",
            title: "Template diterapkan",
          });
        }
      });
    });
  }
  function openModal(mode, data = null) {
    formTransaksi.reset();
    document.getElementById("form_mode").value = mode;
    stepsContainer.innerHTML = "";
    const modalTitle = document.getElementById("modal-title");
    const idInput = document.getElementById("data_id");
    if (templateSelect) templateSelect.value = "";
    if (mode === "insert") {
      modalTitle.textContent = "Buat Dynamic Flow Baru";
      modalTitle.nextElementSibling.textContent =
        "Konfigurasi langkah percakapan baru";
      idInput.value = "";
      document.getElementById("status_aktif").value = "1";
      if (templateArea) templateArea.classList.remove("hidden");
      stepsContainer.appendChild(createStepInput());
    } else if (mode === "update" && data) {
      modalTitle.textContent = "Edit Dynamic Flow";
      modalTitle.nextElementSibling.textContent = "Perbarui konfigurasi flow";
      if (templateArea) templateArea.classList.add("hidden");
      idInput.value = data.id;
      document.getElementById("keyword").value = data.keyword;
      document.getElementById("deskripsi").value = data.deskripsi;
      if (data.expired_at) {
        document.getElementById("expired_at").value = data.expired_at
          .replace(" ", "T")
          .substring(0, 16);
      }
      document.getElementById("max_global_usage").value = data.max_global_usage;
      document.getElementById("max_user_usage").value = data.max_user_usage;
      document.getElementById("status_aktif").value = data.status_aktif;
      document.getElementById("pesan_habis").value = data.pesan_habis;
      document.getElementById("pesan_sudah_klaim").value =
        data.pesan_sudah_klaim;
      if (data.steps && data.steps.length > 0) {
        data.steps.forEach((step) => {
          stepsContainer.appendChild(createStepInput(step));
        });
      } else {
        stepsContainer.appendChild(createStepInput());
      }
    }
    modalForm.classList.remove("hidden");
  }
  function closeModal() {
    modalForm.classList.add("hidden");
  }
  btnAddData.addEventListener("click", () => openModal("insert"));
  [btnCloseModal, btnCancel].forEach((el) =>
    el.addEventListener("click", closeModal)
  );
  document
    .getElementById("modal-backdrop")
    .addEventListener("click", closeModal);
  filterForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(filterForm);
    const searchVal = formData.get("search_keyword").toString().trim();
    const params = new URLSearchParams();
    if (searchVal) params.set("search_keyword", searchVal);
    params.set("page", "1");
    window.history.pushState({}, "", `?${params.toString()}`);
    loadData();
  });
  formTransaksi.addEventListener("submit", async (e) => {
    e.preventDefault();
    const originalHTML = btnSave.innerHTML;
    btnSave.disabled = true;
    btnSave.innerHTML =
      '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
    try {
      const formData = new FormData(formTransaksi);
      const stepsData = [];
      const stepElements = stepsContainer.querySelectorAll(".step-item");
      for (const el of stepElements) {
        const typeSelector = el.querySelector(".type-selector");
        if (!typeSelector) continue;
        const type = typeSelector.value;
        const storageKeyInput = el.querySelector(".input-storage-key");
        const storageKey = storageKeyInput ? storageKeyInput.value.trim() : "";
        let content = {};
        if (type === "text" || type === "save_input") {
          content = { body: el.querySelector(".input-body").value };
        } else if (type === "location_request") {
          const calcNearest =
            el.querySelector(".input-calc-nearest")?.checked || false;
          content = {
            body: el.querySelector(".input-body").value,
            calc_nearest: calcNearest,
          };
        } else if (type === "button") {
          const rawBtns = el.querySelector(".input-buttons-raw").value;
          const buttonsArr = rawBtns
            .split("\n")
            .map((line) => {
              const parts = line.split(":");
              if (parts.length >= 2) {
                return {
                  id: parts[0].trim(),
                  title: parts.slice(1).join(":").trim(),
                };
              }
              return null;
            })
            .filter((x) => x !== null);
          content = {
            header: el.querySelector(".input-header").value,
            body: el.querySelector(".input-body").value,
            footer: el.querySelector(".input-footer").value,
            buttons: buttonsArr,
          };
        } else if (type === "generated_qr") {
          content = {
            qr_data: el.querySelector(".input-qr-data").value,
            caption: el.querySelector(".input-caption").value,
          };
        } else if (type === "list") {
          let sections = [];
          try {
            sections = JSON.parse(
              el.querySelector(".input-sections-json").value
            );
          } catch (err) {
            throw new Error("Format JSON pada Section List tidak valid.");
          }
          content = {
            header: el.querySelector(".input-header").value,
            body: el.querySelector(".input-body").value,
            footer: el.querySelector(".input-footer").value,
            btn_text: el.querySelector(".input-btn-text").value,
            sections: sections,
          };
        } else if (type === "cta_url") {
          const headerTypeEl = el.querySelector(".input-header-type");
          const headerType = headerTypeEl ? headerTypeEl.value : "none";
          let headerContent = "";
          if (headerType === "text") {
            const headerContentEl = el.querySelector(".input-header-content");
            headerContent = headerContentEl ? headerContentEl.value : "";
          } else if (headerType === "image" || headerType === "video") {
            const wrapper = el.querySelector(".header-media-wrapper");
            if (wrapper) {
              const activeSourceEl = wrapper.querySelector(
                ".radio-source:checked"
              );
              const activeSource = activeSourceEl
                ? activeSourceEl.value
                : "upload";
              const hiddenInput = el.querySelector(".input-header-content");
              const oldContent = hiddenInput ? hiddenInput.value : "";
              if (activeSource === "upload") {
                const fileInput = el.querySelector(".input-media-file");
                if (fileInput && fileInput.files.length > 0) {
                  btnSave.innerHTML = `<i class="fas fa-cloud-upload-alt fa-fade mr-2"></i> Uploading Header...`;
                  const mediaFormData = new FormData();
                  mediaFormData.append("file", fileInput.files[0]);
                  const uploadRes = await fetch(
                    `${API_BASE}/upload_media_helper.php`,
                    {
                      method: "POST",
                      body: mediaFormData,
                    }
                  );
                  const uploadJson = await uploadRes.json();
                  if (!uploadJson.success)
                    throw new Error(`Gagal upload: ${uploadJson.message}`);
                  headerContent = uploadJson.url;
                } else {
                  headerContent = oldContent;
                }
              } else {
                const urlInput = el.querySelector(".input-media-url-manual");
                headerContent = urlInput ? urlInput.value.trim() : "";
              }
              if (!headerContent) {
                throw new Error(
                  "Header Image/Video wajib diisi (Upload file atau Input URL)."
                );
              }
            } else {
              console.warn("Media wrapper tidak ditemukan, header di-skip");
            }
          }
          const bodyEl = el.querySelector(".input-body");
          const footerEl = el.querySelector(".input-footer");
          const displayTextEl = el.querySelector(".input-display-text");
          const urlEl = el.querySelector(".input-url");
          content = {
            header_type: headerType,
            header_content: headerContent,
            body: bodyEl ? bodyEl.value : "",
            footer: footerEl ? footerEl.value : "",
            display_text: displayTextEl ? displayTextEl.value : "Lihat Detail",
            url: urlEl ? urlEl.value : "",
          };
        } else if (type === "media") {
          const fileInput = el.querySelector(".input-media-file");
          const urlInput = el.querySelector(".input-media-url");
          const filenameInput = el.querySelector(".input-media-filename");
          const medType = el.querySelector(".input-media-type").value;
          const caption = el.querySelector(".input-caption").value;
          let finalUrl = urlInput.value;
          let finalFilename = filenameInput.value;
          if (fileInput.files.length > 0) {
            btnSave.innerHTML = `<i class="fas fa-cloud-upload-alt fa-fade mr-2"></i> Uploading ${fileInput.files[0].name}...`;
            const mediaFormData = new FormData();
            mediaFormData.append("file", fileInput.files[0]);
            const uploadRes = await fetch(
              `${API_BASE}/upload_media_helper.php`,
              {
                method: "POST",
                body: mediaFormData,
              }
            );
            const uploadJson = await uploadRes.json();
            if (!uploadJson.success) {
              throw new Error(`Gagal upload media: ${uploadJson.message}`);
            }
            finalUrl = uploadJson.url;
            if (!finalFilename) finalFilename = fileInput.files[0].name;
          }
          if (!finalUrl) {
            throw new Error("Step Media wajib memiliki file atau URL.");
          }
          content = {
            type: medType,
            url: finalUrl,
            filename: finalFilename,
            caption: caption,
          };
        }
        stepsData.push({
          tipe_respon: type,
          key_penyimpanan: storageKey,
          isi_pesan: content,
        });
      }
      if (stepsData.length === 0)
        throw new Error("Minimal harus ada 1 langkah (step).");
      const payload = {
        mode: formData.get("mode"),
        id: formData.get("id"),
        keyword: formData.get("keyword"),
        deskripsi: formData.get("deskripsi"),
        expired_at: formData.get("expired_at"),
        max_global_usage: formData.get("max_global_usage"),
        max_user_usage: formData.get("max_user_usage"),
        status_aktif: formData.get("status_aktif"),
        pesan_habis: formData.get("pesan_habis"),
        pesan_sudah_klaim: formData.get("pesan_sudah_klaim"),
        steps: stepsData,
      };
      const response = await fetch(`${API_BASE}/save_dynamic_flow.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const result = await response.json();
      if (result.success) {
        Swal.fire({
          icon: "success",
          title: "Berhasil!",
          text: result.message,
          timer: 2000,
          showConfirmButton: false,
        });
        closeModal();
        loadData();
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Gagal Menyimpan",
        text: error.message,
        confirmButtonColor: "#ef4444",
      });
    } finally {
      btnSave.disabled = false;
      btnSave.innerHTML = originalHTML;
    }
  });
  window.editFlow = (encodedData) => {
    try {
      const data = JSON.parse(decodeURIComponent(encodedData));
      openModal("update", data);
    } catch (e) {
      console.error("Parse Error", e);
      Swal.fire("Error", "Gagal membaca data flow", "error");
    }
  };
  window.deleteFlow = async (id, keyword) => {
    const confirm = await Swal.fire({
      title: "Hapus Flow?",
      html: `Anda yakin ingin menghapus flow: <br><strong class="text-lg text-indigo-700">"${keyword}"</strong>?<br><span class="text-xs text-red-500">Semua langkah (steps) dan data sesi akan ikut terhapus.</span>`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      confirmButtonText: "Ya, Hapus!",
      cancelButtonText: "Batal",
    });
    if (confirm.isConfirmed) {
      try {
        Swal.showLoading();
        const response = await fetch(`${API_BASE}/delete_dynamic_flow.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: id }),
        });
        const result = await response.json();
        if (result.success) {
          Swal.fire({
            icon: "success",
            title: "Terhapus!",
            timer: 1500,
            showConfirmButton: false,
          });
          loadData();
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        Swal.fire({ icon: "error", title: "Gagal!", text: error.message });
      }
    }
  };
  loadData();
});
