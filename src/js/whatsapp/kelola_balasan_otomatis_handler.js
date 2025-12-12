document.addEventListener("DOMContentLoaded", () => {
  const TEMPLATE_CONTACTS = window.TEMPLATE_DATA
    ? window.TEMPLATE_DATA.contacts
    : {};
  const TEMPLATE_LOCATIONS = window.TEMPLATE_DATA
    ? window.TEMPLATE_DATA.locations
    : {};
  const tableBody = document.getElementById("table-body");
  const filterForm = document.getElementById("filter-form");
  const modalForm = document.getElementById("modal-form");
  const paginationContainer = document.getElementById("pagination-container");
  const btnAddData = document.getElementById("btn-add-data");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const btnCancel = document.getElementById("btn-cancel");
  const formTransaksi = document.getElementById("form-transaksi");
  const btnAddMessage = document.getElementById("btn-add-message");
  const messageContainer = document.getElementById("message-container");
  const API_BASE = "/src/api/whatsapp";
  function createMessageInput(data = { type: "text", content: "" }) {
    const wrapper = document.createElement("div");
    wrapper.className = "relative group animate-fade-in message-item pb-4";
    const currentCount = messageContainer.children.length + 1;
    wrapper.innerHTML = `
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 shadow-sm">
                <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-white bg-gray-500 w-6 h-6 flex items-center justify-center rounded-full select-none msg-number">${currentCount}</span>
                        <select class="type-selector text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded px-2 py-1 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 cursor-pointer">
                            <option value="text" ${
                              data.type === "text" ? "selected" : ""
                            }>📝 Teks Biasa</option>
                            <option value="contact" ${
                              data.type === "contact" ? "selected" : ""
                            }>👤 Kontak</option>
                            <option value="location" ${
                              data.type === "location" ? "selected" : ""
                            }>📍 Lokasi</option>
                            <option value="media" ${
                              data.type === "media" ? "selected" : ""
                            }>🖼️ Gambar/Media</option>
                            <option value="cta_url" ${
                              data.type === "cta_url" ? "selected" : ""
                            }>🔗 Tombol Link</option>
                        </select>
                    </div>
                    <button type="button" class="btn-remove-msg text-gray-400 hover:text-red-500 transition-colors p-1" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="content-area"></div>
            </div>
        `;
    const contentArea = wrapper.querySelector(".content-area");
    const typeSelector = wrapper.querySelector(".type-selector");
    const btnDel = wrapper.querySelector(".btn-remove-msg");
    const renderInputs = (type, content) => {
      contentArea.innerHTML = "";
      let inputs = "";
      if (type === "text") {
        const val = typeof content === "string" ? content : "";
        inputs = `
                <textarea class="input-content input-enhanced w-full px-3 py-2 border border-gray-300 rounded bg-white text-sm focus:outline-none focus:border-green-500" 
                    rows="3" placeholder="Tulis pesan balasan di sini..." required>${val}</textarea>
                <div class="text-right text-[10px] text-gray-400 mt-1">Support emoji & format WA (*bold*, _italic_)</div>
            `;
        contentArea.innerHTML = inputs;
      } else if (type === "contact") {
        const val = content || { name: "", phone: "" };
        let optionsHtml =
          '<option value="">-- Pilih dari Template (Isi Otomatis) --</option>';
        for (const region in TEMPLATE_CONTACTS) {
          optionsHtml += `<optgroup label="${region}">`;
          for (const branch in TEMPLATE_CONTACTS[region]) {
            const phone = TEMPLATE_CONTACTS[region][branch];
            optionsHtml += `<option value="${phone}" data-branch="${branch}">${branch}</option>`;
          }
          optionsHtml += `</optgroup>`;
        }
        inputs = `
                <div class="space-y-3">
                    <div class="bg-green-50 p-2 rounded border border-green-200">
                         <label class="block text-xs font-bold text-green-800 mb-1">Opsi Cepat:</label>
                         <select class="template-selector w-full px-2 py-1.5 border border-green-300 rounded text-sm focus:outline-none bg-white">
                            ${optionsHtml}
                         </select>
                    </div>
                    <div class="grid grid-cols-1 gap-2 border-t pt-2">
                        <label class="text-xs text-gray-400 block -mb-1">Data Manual (Dapat diedit):</label>
                        <input type="text" class="input-contact-name input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                            placeholder="Nama Kontak (Contoh: CS Toko)" value="${
                              val.name || ""
                            }" required>
                        <input type="text" class="input-contact-phone input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                            placeholder="Nomor HP (Contoh: 08123456789)" value="${
                              val.phone || ""
                            }" required>
                    </div>
                </div>
            `;
        contentArea.innerHTML = inputs;
        const tplSelect = contentArea.querySelector(".template-selector");
        const nameInput = contentArea.querySelector(".input-contact-name");
        const phoneInput = contentArea.querySelector(".input-contact-phone");
        if (val.phone) tplSelect.value = val.phone;
        tplSelect.addEventListener("change", (e) => {
          const selectedOption = e.target.options[e.target.selectedIndex];
          const phoneNumber = e.target.value;
          const branchName = selectedOption.dataset.branch;
          if (phoneNumber && branchName) {
            nameInput.value = `Asoka Baby Store ${branchName}`;
            phoneInput.value = phoneNumber;
          }
        });
      } else if (type === "location") {
        const val = content || { lat: "", long: "", name: "", address: "" };
        let optionsHtml =
          '<option value="">-- Pilih Lokasi Cabang (Isi Otomatis) --</option>';
        for (const region in TEMPLATE_LOCATIONS) {
          optionsHtml += `<optgroup label="${region}">`;
          for (const branch in TEMPLATE_LOCATIONS[region]) {
            const locData = JSON.stringify(TEMPLATE_LOCATIONS[region][branch]);
            const safeLocData = locData.replace(/'/g, "&apos;");
            optionsHtml += `<option value='${safeLocData}'>${branch}</option>`;
          }
          optionsHtml += `</optgroup>`;
        }
        inputs = `
                <div class="space-y-3">
                    <div class="bg-blue-50 p-2 rounded border border-blue-200">
                         <label class="block text-xs font-bold text-blue-800 mb-1">Opsi Cepat:</label>
                         <select class="template-selector w-full px-2 py-1.5 border border-blue-300 rounded text-sm focus:outline-none bg-white">
                            ${optionsHtml}
                         </select>
                    </div>
                    <div class="space-y-2 border-t pt-2">
                         <label class="text-xs text-gray-400 block -mb-1">Data Manual (Dapat diedit):</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" class="input-loc-lat input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                                placeholder="Latitude (-6.xxxx)" value="${
                                  val.lat || ""
                                }" required>
                            <input type="text" class="input-loc-long input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                                placeholder="Longitude (106.xxxx)" value="${
                                  val.long || ""
                                }" required>
                        </div>
                        <input type="text" class="input-loc-name input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                            placeholder="Nama Tempat (Contoh: Kantor Cabang)" value="${
                              val.name || ""
                            }" required>
                        <textarea class="input-loc-addr input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                            rows="2" placeholder="Alamat Lengkap" required>${
                              val.address || ""
                            }</textarea>
                    </div>
                </div>
            `;
        contentArea.innerHTML = inputs;
        const tplSelect = contentArea.querySelector(".template-selector");
        const latInput = contentArea.querySelector(".input-loc-lat");
        const longInput = contentArea.querySelector(".input-loc-long");
        const nameInput = contentArea.querySelector(".input-loc-name");
        const addrInput = contentArea.querySelector(".input-loc-addr");
        if (val.lat && val.long) {
          Array.from(tplSelect.options).forEach((option) => {
            if (option.value) {
              try {
                const data = JSON.parse(option.value);
                if (data.latitude == val.lat && data.longitude == val.long) {
                  tplSelect.value = option.value;
                }
              } catch (err) {}
            }
          });
        }
        tplSelect.addEventListener("change", (e) => {
          const selectedValue = e.target.value;
          if (selectedValue) {
            try {
              const data = JSON.parse(selectedValue);
              latInput.value = data.latitude;
              longInput.value = data.longitude;
              nameInput.value = data.name;
              addrInput.value = data.address;
            } catch (err) {
              console.error("Error parsing location data", err);
            }
          }
        });
      } else if (type === "media") {
        const val = content || { url: "", caption: "", media_type: "image" };
        let previewHtml = "";
        let fileName = "";
        if (val.url) {
          fileName = val.url.split("/").pop();
          if (fileName.length > 30)
            fileName = fileName.substring(0, 30) + "...";
          previewHtml = `
                    <div class="mt-2 p-2 bg-gray-100 rounded border flex justify-between items-center text-xs">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <a href="${val.url}" target="_blank" class="text-blue-600 hover:underline truncate" title="Lihat File Asli">
                                ${fileName}
                            </a>
                        </div>
                        <span class="text-gray-400 italic text-[10px]">(Tersimpan)</span>
                    </div>
                `;
        }
        inputs = `
                <div class="space-y-3">
                    <div class="bg-gray-50 border border-gray-200 rounded p-3">
                        <label class="block text-xs font-bold text-gray-600 mb-2">1. Pilih Tipe & File</label>
                        <div class="flex gap-2 mb-2">
                            <select class="input-media-type w-1/3 px-2 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none">
                                <option value="image" ${
                                  val.media_type === "image" ? "selected" : ""
                                }>📷 Foto</option>
                                <option value="document" ${
                                  val.media_type === "document"
                                    ? "selected"
                                    : ""
                                }>📄 Dokumen</option>
                                <option value="video" ${
                                  val.media_type === "video" ? "selected" : ""
                                }>🎥 Video</option>
                            </select>
                            <div class="relative w-2/3">
                                <input type="file" class="input-media-file block w-full text-sm text-slate-500
                                  file:mr-2 file:py-2 file:px-3
                                  file:rounded-md file:border-0
                                  file:text-xs file:font-semibold
                                  file:bg-green-50 file:text-green-700
                                  hover:file:bg-green-100 cursor-pointer
                                " accept="image/*,video/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                            </div>
                        </div>
                        <input type="hidden" class="input-media-url" value="${
                          val.url || ""
                        }">
                        <input type="hidden" class="input-media-filename" value="${
                          val.filename || ""
                        }">
                        <div class="preview-area">${previewHtml}</div>
                    </div>
                    <div>
                         <label class="block text-xs font-bold text-gray-600 mb-1">2. Caption (Opsional)</label>
                         <input type="text" class="input-media-caption input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                            placeholder="Tulis caption..." value="${
                              val.caption || ""
                            }">
                    </div>
                </div>
            `;
        contentArea.innerHTML = inputs;
        const fileInput = contentArea.querySelector(".input-media-file");
        const previewArea = contentArea.querySelector(".preview-area");
        fileInput.addEventListener("change", (e) => {
          if (e.target.files.length > 0) {
            const filenameInput = contentArea.querySelector(
              ".input-media-filename"
            );

            filenameInput.value = e.target.files[0].name;

            previewArea.innerHTML = `
            <div class="mt-2 p-2 bg-yellow-50 rounded border border-yellow-200 flex items-center gap-2 text-xs text-yellow-700">
                <i class="fas fa-arrow-circle-up animate-bounce"></i> 
                <span>File baru: <b>${e.target.files[0].name}</b> (Akan diupload saat disimpan)</span>
            </div>
        `;
          }
        });
      } else if (type === "cta_url") {
        const val = content || { body: "", display_text: "", url: "" };
        inputs = `
                  <div class="space-y-2">
                    <textarea class="input-cta-body input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                        rows="2" placeholder="Pesan Body Utama" required>${
                          val.body || ""
                        }</textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" class="input-cta-text input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                            placeholder="Label Tombol" value="${
                              val.display_text || ""
                            }" required maxlength="20">
                        <input type="url" class="input-cta-url input-enhanced w-full px-3 py-2 border border-gray-300 rounded text-sm" 
                           required value="${
                             val.url || ""
                           }" placeholder="https://website.com">
                    </div>
                </div>
            `;
        contentArea.innerHTML = inputs;
      }
    };
    renderInputs(data.type, data.content);
    typeSelector.addEventListener("change", (e) => {
      renderInputs(e.target.value, null);
    });
    btnDel.addEventListener("click", () => {
      if (messageContainer.children.length > 1) {
        wrapper.remove();
        renumberMessages();
      } else {
        typeSelector.value = "text";
        renderInputs("text", "");
      }
    });
    return wrapper;
  }
  function renumberMessages() {
    Array.from(messageContainer.children).forEach((child, index) => {
      const span = child.querySelector(".msg-number");
      if (span) span.textContent = index + 1;
    });
  }
  btnAddMessage.addEventListener("click", () => {
    messageContainer.appendChild(createMessageInput());
    messageContainer.scrollTop = messageContainer.scrollHeight;
  });
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    return {
      search_keyword: (params.get("search_keyword") || "").trim(),
      page: parseInt(params.get("page") || "1", 10),
    };
  }
  function build_pagination_url(newPage) {
    const params = new URLSearchParams(window.location.search);
    params.set("page", newPage);
    return "?" + params.toString();
  }
  async function loadData() {
    const urlParams = getUrlParams();
    const inputSearch = filterForm.querySelector(
      'input[name="search_keyword"]'
    );
    if (inputSearch) inputSearch.value = urlParams.search_keyword;
    tableBody.innerHTML = `
            <tr><td colspan="5" class="text-center p-8"><div class="spinner-simple"></div><p class="mt-3 text-gray-500">Memuat data...</p></td></tr>`;
    const queryString = new URLSearchParams({
      search_keyword: urlParams.search_keyword,
      page: urlParams.page,
    }).toString();
    try {
      const response = await fetch(
        `${API_BASE}/get_data_balasan_otomatis.php?${queryString}`
      );
      const result = await response.json();
      if (result.error) throw new Error(result.error);
      renderTable(result.data, result.pagination);
      renderPagination(result.pagination);
    } catch (error) {
      console.error(error);
      tableBody.innerHTML = `
            <tr><td colspan="5" class="text-center p-8 text-red-600"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Gagal memuat data: ${error.message}</p></td></tr>`;
      paginationContainer.innerHTML = "";
    }
  }
  function renderTable(data, pagination) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-12 text-gray-500"><p>Tidak ada data ditemukan</p></td></tr>`;
      return;
    }
    const startNumber = (pagination.current_page - 1) * pagination.limit;
    tableBody.innerHTML = data
      .map((row, index) => {
        const statusBadge =
          row.status_aktif == "1"
            ? `<span class="badge-status badge-aktif"><i class="fas fa-check-circle"></i> Aktif</span>`
            : `<span class="badge-status badge-nonaktif"><i class="fas fa-times-circle"></i> Non-Aktif</span>`;
        const messages = row.list_pesan || [];
        let displayBalasan = "<em class='text-gray-400'>Tidak ada pesan</em>";
        let moreCount = 0;
        if (messages.length > 0) {
          const firstMsg = messages[0];
          let preview = "";
          let icon = "";
          if (firstMsg.type === "text") {
            icon = "📝";
            preview = firstMsg.content;
          } else if (firstMsg.type === "contact") {
            icon = "👤";
            preview = firstMsg.content.name;
          } else if (firstMsg.type === "location") {
            icon = "📍";
            preview = firstMsg.content.name;
          } else if (firstMsg.type === "media") {
            icon = "🖼️";
            preview = "Media File";
          } else if (firstMsg.type === "cta_url") {
            icon = "🔗";
            preview = firstMsg.content.display_text;
          }
          if (typeof preview === "string" && preview.length > 50)
            preview = preview.substring(0, 50) + "...";
          displayBalasan = `<span class="mr-1">${icon}</span> ${preview}`;
          moreCount = messages.length - 1;
        }
        const moreBadge =
          moreCount > 0
            ? `<span class="text-xs bg-gray-200 px-1 rounded ml-1">+${moreCount}</span>`
            : "";
        const rowDataString = encodeURIComponent(JSON.stringify(row));
        return `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="text-center font-semibold text-gray-500">${
                  startNumber + index + 1
                }</td>
                <td><span class="font-bold text-gray-800 bg-gray-100 px-2 py-1 rounded text-sm">${
                  row.kata_kunci
                }</span></td>
                <td class="text-gray-600 text-sm"><div class="flex items-center"><span class="line-clamp-1">${displayBalasan}</span>${moreBadge}</div></td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="window.editData('${rowDataString}')" class="rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 w-8 h-8 flex items-center justify-center"><i class="fas fa-pencil-alt"></i></button>
                        <button onclick="window.deleteData('${row.id}', '${
          row.kata_kunci
        }')" class="rounded-lg bg-red-50 text-red-600 hover:bg-red-100 w-8 h-8 flex items-center justify-center"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>
            </tr>`;
      })
      .join("");
  }
  function renderPagination(pagination) {
    if (!pagination || pagination.total_rows === 0) {
      paginationContainer.innerHTML = "";
      return;
    }
    const { current_page, total_pages, total_rows, limit } = pagination;
    let html = `<div class="flex justify-between items-center"><span class="text-sm text-gray-600">Total ${total_rows} data</span><div class="flex gap-1">`;
    if (current_page > 1)
      html += `<a href="${build_pagination_url(
        current_page - 1
      )}" class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">Prev</a>`;
    html += `<span class="px-3 py-1 border rounded bg-blue-50 text-blue-600 font-bold text-sm">${current_page}</span>`;
    if (current_page < total_pages)
      html += `<a href="${build_pagination_url(
        current_page + 1
      )}" class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">Next</a>`;
    html += `</div></div>`;
    paginationContainer.innerHTML = html;
  }
  function openModal(mode, data = null) {
    formTransaksi.reset();
    document.getElementById("form_mode").value = mode;
    messageContainer.innerHTML = "";
    const modalTitle = document.getElementById("modal-title");
    const kataKunciInput = document.getElementById("kata_kunci");
    const idInput = document.getElementById("data_id");
    if (mode === "insert") {
      modalTitle.textContent = "Tambah Keyword Baru";
      modalTitle.nextElementSibling.textContent = "Buat balasan otomatis baru";
      idInput.value = "";
      kataKunciInput.readOnly = false;
      messageContainer.appendChild(createMessageInput());
    } else if (mode === "update" && data) {
      modalTitle.textContent = "Edit Keyword & Pesan";
      modalTitle.nextElementSibling.textContent = "Perbarui informasi balasan";
      idInput.value = data.id;
      kataKunciInput.value = data.kata_kunci;
      document.getElementById("status_aktif").value = data.status_aktif;
      if (data.list_pesan && data.list_pesan.length > 0) {
        data.list_pesan.forEach((msg) => {
          messageContainer.appendChild(createMessageInput(msg));
        });
      } else {
        messageContainer.appendChild(createMessageInput());
      }
      kataKunciInput.readOnly = false;
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
    const btnSave = document.getElementById("btn-save");
    const originalHTML = btnSave.innerHTML;
    btnSave.disabled = true;
    btnSave.innerHTML =
      '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
    try {
      const formData = new FormData(formTransaksi);
      const messageItems = [];
      const itemElements = messageContainer.querySelectorAll(".message-item");
      for (const el of itemElements) {
        const typeSelector = el.querySelector(".type-selector");
        if (!typeSelector) continue;
        const type = typeSelector.value;
        let content = null;
        if (type === "text") {
          content = el.querySelector(".input-content").value;
        } else if (type === "contact") {
          let rawPhone = el.querySelector(".input-contact-phone").value;
          let cleanPhone = rawPhone.replace(/\D/g, "");
          if (cleanPhone.startsWith("0"))
            cleanPhone = "62" + cleanPhone.substring(1);
          else if (!cleanPhone.startsWith("62")) cleanPhone = "62" + cleanPhone;
          content = {
            name: el.querySelector(".input-contact-name").value,
            phone: cleanPhone,
          };
        } else if (type === "location") {
          content = {
            lat: el.querySelector(".input-loc-lat").value,
            long: el.querySelector(".input-loc-long").value,
            name: el.querySelector(".input-loc-name").value,
            address: el.querySelector(".input-loc-addr").value,
          };
        } else if (type === "media") {
          const fileInput = el.querySelector(".input-media-file");
          const urlInput = el.querySelector(".input-media-url");
          let finalUrl = urlInput.value;
          if (fileInput.files.length > 0) {
            btnSave.innerHTML = `<i class="fas fa-cloud-upload-alt fa-fade mr-2"></i> Mengupload (${fileInput.files[0].name})...`;
            const mediaFormData = new FormData();
            mediaFormData.append("file", fileInput.files[0]);
            const uploadRes = await fetch(
              `${API_BASE}/upload_media_helper.php`,
              {
                method: "POST",
                body: mediaFormData,
              }
            );
            const responseText = await uploadRes.text();
            let uploadJson;
            try {
              uploadJson = JSON.parse(responseText);
            } catch (err) {
              console.error("Server Error HTML:", responseText);
              throw new Error(
                "Gagal memproses respon server. Cek Log: logs/upload_media_debug.log"
              );
            }
            if (!uploadRes.ok || !uploadJson.success) {
              throw new Error(
                `Gagal upload: ${uploadJson.message || "Unknown Error"}`
              );
            }
            finalUrl = uploadJson.url;
          }
          if (!finalUrl) {
            throw new Error(
              "Mohon pilih file gambar/media untuk pesan bertipe Media."
            );
          }
          let finalFilename = el.querySelector(".input-media-filename").value;

          if (!finalFilename && finalUrl) {
            finalFilename = finalUrl.split("/").pop();
          }

          content = {
            media_type: el.querySelector(".input-media-type").value,
            url: finalUrl,
            filename: finalFilename,
            caption: el.querySelector(".input-media-caption").value,
          };
        } else if (type === "cta_url") {
          content = {
            body: el.querySelector(".input-cta-body").value,
            display_text: el.querySelector(".input-cta-text").value,
            url: el.querySelector(".input-cta-url").value,
          };
        }
        if (content) {
          messageItems.push({ type: type, content: content });
        }
      }
      const jsonData = {
        mode: formData.get("mode"),
        id: formData.get("id"),
        kata_kunci: formData.get("kata_kunci"),
        status_aktif: formData.get("status_aktif"),
        isi_balasan: messageItems,
      };
      btnSave.innerHTML = '<i class="fas fa-save mr-2"></i> Menyimpan Data...';
      const response = await fetch(`${API_BASE}/save_balasan_otomatis.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(jsonData),
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
        title: "Gagal!",
        text: error.message,
        confirmButtonColor: "#10b981",
      });
    } finally {
      btnSave.disabled = false;
      btnSave.innerHTML = originalHTML;
    }
  });
  window.editData = (encodedData) => {
    const data = JSON.parse(decodeURIComponent(encodedData));
    openModal("update", data);
  };
  window.deleteData = async (id, kataKunci) => {
    const confirm = await Swal.fire({
      title: "Hapus Keyword?",
      html: `Anda yakin ingin menghapus keyword:<br><strong class="text-lg">"${kataKunci}"</strong>?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      confirmButtonText: "Ya, Hapus!",
      cancelButtonText: "Batal",
    });
    if (confirm.isConfirmed) {
      try {
        Swal.showLoading();
        const response = await fetch(
          `${API_BASE}/delete_balasan_otomatis.php`,
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: id }),
          }
        );
        const result = await response.json();
        if (result.success) {
          Swal.fire({
            icon: "success",
            title: "Terhapus!",
            timer: 2000,
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
