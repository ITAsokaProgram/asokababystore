export const renderTableUserInternal = (data, offset = 0) => {
  const tableBody = document.querySelector("tbody");
  tableBody.innerHTML = "";

  if (data.length === 0) {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td colspan="5" class="px-6 py-8 text-center text-gray-500">
        <div class="flex flex-col items-center">
          <i class="fas fa-search text-4xl mb-2 text-gray-300"></i>
          <p class="text-lg font-medium">Tidak ada data ditemukan</p>
          <p class="text-sm">Coba ubah kata kunci pencarian Anda</p>
        </div>
      </td>
    `;
    tableBody.appendChild(row);
    return;
  }

  // Tampilkan ke dalam tabel
  data.forEach((userGroup, index) => {
    const row = document.createElement("tr");
    row.className =
      "hover:bg-gray-50 transition-colors duration-200 border-b border-gray-100";

    // Determine position badge color
    let positionBadgeClass = "bg-blue-100 text-blue-800";
    if (userGroup.hak === "Manajer") {
      positionBadgeClass = "bg-purple-100 text-purple-800";
    } else if (userGroup.hak === "IT") {
      positionBadgeClass = "bg-green-100 text-green-800";
    } else if (userGroup.hak === "Admin") {
      positionBadgeClass = "bg-orange-100 text-orange-800";
    }

    let kodeCabangClass = "bg-blue-100 text-blue-800";
    if (userGroup.kode_cabang === "Pusat") {
      kodeCabangClass = "bg-pink-100 text-pink-800";
    } else{
      kodeCabangClass = "bg-green-100 text-green-800";
    } 

    row.innerHTML = `
      <td class="px-6 py-4 text-sm font-medium text-gray-900">${
        offset + index + 1
      }</td>
      <td class="px-6 py-4">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-gradient-to-r from-pink-400 to-rose-400 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
            ${userGroup.nama.charAt(0).toUpperCase()}
          </div>
          <div>
            <div class="text-sm font-semibold text-gray-900">${
              userGroup.nama
            }</div>
            <div class="text-xs text-gray-500">ID: ${userGroup.kode}</div>
          </div>
        </div>
      </td>
      <td class="px-6 py-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${positionBadgeClass}">
          ${userGroup.hak}
        </span>
      </td>
      <td class="px-6 py-4">
        <div class="max-w-[300px]">
          <div class="text-xs text-gray-600 mb-1">Akses Menu:</div>
          <div class="flex flex-wrap gap-1 menu-list" data-kode="${userGroup.kode}">
            ${(() => {
              const menus = (userGroup.menu_code || "").split(",").map(m => m.trim()).filter(Boolean);
              if (menus.length <= 5) {
                return menus.map(menu => `<span class=\"inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-md\">${menu}</span>`).join("");
              } else {
                const firstFive = menus.slice(0, 5).map(menu => `<span class=\"inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-md\">${menu}</span>`).join("");
                return `${firstFive}<button class=\"inline-block px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded-md cursor-pointer show-more-menu\" data-all='${menus.map(m => m.replace(/'/g, "&#39;")).join(",")}' data-kode='${userGroup.kode}'>...</button>`;
              }
            })()}
          </div>
        </div>
      </td>
      <td class="px-6 py-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-md font-medium ${kodeCabangClass}">
          ${userGroup.kode_cabang === "Pusat" ? "Pusat" : userGroup.kode_cabang}
        </span>
      </td>
      <td class="px-6 py-4">
        <div class="flex items-center space-x-2">
          <button
            class="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium shadow transition duration-200 edit flex items-center"
            data-kode="${userGroup.kode}"
            title="Edit User"
          >
            <i class="fas fa-edit mr-1"></i>
            Edit
          </button>
          <button
            class="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-medium shadow transition duration-200 delete flex items-center"
            data-kode="${userGroup.kode}"
            title="Hapus User"
          >
            <i class="fas fa-trash mr-1"></i>
            Hapus
          </button>
          <button
            class="px-3 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium shadow transition duration-200 reset flex items-center"
            data-kode="${userGroup.kode}"
            title="Reset Password"
          >
            <i class="fa-solid fa-unlock mr-1"></i>
            Reset
          </button>
        </div>
      </td>
    `;
    tableBody.appendChild(row);
  });

  // Fungsi untuk attach event listener ke tombol show-more-menu dan Tutup
  function attachShowMoreMenuListeners() {
    document.querySelectorAll('.show-more-menu').forEach(btn => {
      btn.onclick = function(e) {
        const kode = btn.getAttribute('data-kode');
        const container = document.querySelector(`.menu-list[data-kode='${kode}']`);
        const allMenus = btn.getAttribute('data-all').split(',');
        if (!btn.classList.contains('expanded')) {
          // Expand
          container.innerHTML = allMenus.map(menu => `<span class=\"inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-md\">${menu}</span>`).join('') + `<button class=\"inline-block px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded-md cursor-pointer show-more-menu expanded\" data-all='${allMenus.map(m => m.replace(/'/g, "&#39;")).join(",")}' data-kode='${kode}'>Tutup</button>`;
        } else {
          // Collapse
          const firstFive = allMenus.slice(0, 5).map(menu => `<span class=\"inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-md\">${menu}</span>`).join('');
          container.innerHTML = `${firstFive}<button class=\"inline-block px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded-md cursor-pointer show-more-menu\" data-all='${allMenus.map(m => m.replace(/'/g, "&#39;")).join(",")}' data-kode='${kode}'>...</button>`;
        }
        // Pasang ulang event listener setelah isi diubah
        attachShowMoreMenuListeners();
      };
    });
  }

  setTimeout(() => {
    attachShowMoreMenuListeners();
  }, 0);
};

export default { renderTableUserInternal };
