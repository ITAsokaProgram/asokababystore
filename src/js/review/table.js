export const renderTableReview = (data, offset) => {
  const tableBody = document.querySelector("tbody");
  const tableLoading = document.getElementById("tableLoading");
  const table = document.querySelector("table");

  if (tableLoading) tableLoading.classList.remove("hidden");
  if (table) table.classList.add("hidden");

  setTimeout(() => {
    if (tableLoading) tableLoading.classList.add("hidden");
    if (table) table.classList.remove("hidden");

    tableBody.innerHTML = "";

    data.forEach((item, index) => {
      const row = document.createElement("tr");
      row.className = "hover:bg-gray-50 transition-all duration-200 border-b border-gray-100 hover:-translate-y-0.5 hover:shadow-sm";
      
      let actionButton = '';
      
      if (!item.sudah_terpecahkan) {
        actionButton = `
          <button 
            onclick="openIssueHandlingModal(${item.id}, ${JSON.stringify({
              nama: item.nama,
              handphone: item.hp,
              rating: item.rating,
              tanggal: new Date(item.tanggal).toLocaleDateString("id-ID"),
              komentar: item.komentar,
              no_bon: item.no_bon,
              cabang: item.cabang,
              nama_kasir: item.nama_kasir
            }).replace(/"/g, '&quot;')})"
            class="inline-flex items-center px-2 py-1 bg-gradient-to-r from-orange-500 to-yellow-500 text-white text-xs font-medium rounded-lg hover:from-orange-600 hover:to-yellow-600 transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-1"
          >
            <i class="fas fa-tools mr-1"></i>
            Tangani
          </button>
        `;
      } else if (item.detail_review_id != null) {
        if (item.detail_status === 'resolved') {
          actionButton = `
            <button 
              onclick="viewHandlingDetail(${item.id})"
              class="inline-flex items-center px-3 py-2 bg-green-100 text-green-800 text-xs font-medium rounded-lg hover:bg-green-200 transition-all duration-200"
            >
              <i class="fas fa-check-circle mr-1"></i>
              Tertangani
            </button>
          `;
        } else {
          actionButton = `
            <button 
              onclick="editIssueHandling(${item.id})"
              class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-1"
            >
              <i class="fas fa-edit mr-1"></i>
              Edit
            </button>
          `;
        }
      }
      const unreadBadge = item.unread_count > 0
        ? `<span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white ring-2 ring-white">${item.unread_count}</span>`
        : '';
      // Tombol Chat - selalu muncul
      const chatButton = `
        <button 
          onclick="openChatModal(${item.id}, ${JSON.stringify({
            nama: item.nama,
            handphone: item.hp,
            rating: item.rating,
            tanggal: new Date(item.tanggal).toLocaleDateString("id-ID"),
            komentar: item.komentar
          }).replace(/"/g, '&quot;')})"
          class="inline-flex items-center px-2 py-1 bg-gradient-to-r from-blue-500 to-indigo-500 text-white text-xs font-medium rounded-lg hover:from-blue-600 hover:to-indigo-600 transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-1"
          title="Chat dengan Customer"
        >
          <i class="fas fa-comments mr-1"></i>
          Chat
          ${unreadBadge}
        </button>
      `;

      row.innerHTML = `
        <td class="px-4 py-3 text-sm text-gray-900 font-medium transition-all duration-200">${offset + index + 1}</td>
        <td class="px-4 py-3 text-sm text-gray-700 transition-all duration-200">${item.hp}</td>
        <td class="px-4 py-3 text-sm text-gray-900 font-medium transition-all duration-200 truncate">${item.nama}</td>
        <td class="px-4 py-3 text-sm text-gray-700 max-w-[150px] cursor-pointer komentar-cell hover:bg-yellow-50/30 hover:rounded-lg transition-all duration-200" data-komentar='${JSON.stringify({
          nama: item.nama,
          handphone: item.hp,
          rating: item.rating,
          tanggal: new Date(item.tanggal).toLocaleDateString("id-ID"),
          komentar: item.komentar,
          no_bon: item.no_bon,
          cabang: item.cabang,
          nama_kasir: item.nama_kasir
        })}'>
          <div class="truncate whitespace-nowrap hover:text-gray-900 transition-colors duration-200">
            ${item.komentar ?? ""}
          </div>
        </td>
        <td class="px-4 py-3 text-sm transition-all duration-200">
          <div class="flex items-center space-x-1">
            ${renderStars(item.rating)}
            <span class="text-xs text-gray-500 ml-1">(${item.rating})</span>
          </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-700 transition-all duration-200">
          <div class="flex items-center space-x-2">
            <i class="fas fa-calendar text-gray-400 text-xs"></i>
            <span>${new Date(item.tanggal).toLocaleDateString("id-ID")}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-700 font-mono transition-all duration-200 truncate">${item.no_bon}</td>
        <td class="px-4 py-3 text-sm text-gray-700 font-mono transition-all duration-200 truncate">${item.kategori}</td>
        <td class="px-4 py-3 text-sm transition-all duration-200">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-store mr-1"></i>
            ${item.cabang}
          </span>
        </td>
        <td class="px-4 py-3 text-sm text-gray-700 transition-all duration-200">${item.nama_kasir}</td>
        ${item.enpoint_foto === null || item.enpoint_foto === "" ? `
          <td class="px-4 py-3 text-sm transition-all duration-200">
            <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-lg">
              <i class="fas fa-image text-gray-400"></i>
            </div>
          </td>
        ` : `
          <td class="px-4 py-3 text-sm transition-all duration-200">
            <img src="/src/api/customer/serve_image_review_in?path=${item.enpoint_foto}" alt="Foto" class="w-12 h-12 object-cover rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:scale-105 hover:-translate-y-1" data-zoomable="true">
          </td>
        `}
        <td class="px-4 py-3 text-sm transition-all duration-200 relative">
          <div class="flex items-center space-x-2">
            ${item.detail_review_id != null ? chatButton : ""}
            ${actionButton}
          </div>
        </td>
      `;
        
      tableBody.appendChild(row);
    });

    document.querySelectorAll(".komentar-cell").forEach((cell) => {
      cell.addEventListener("click", function () {
        const komentarData = JSON.parse(this.getAttribute('data-komentar'));
        showKomentarModal(komentarData);
      });
    });

    document.querySelectorAll('[data-zoomable="true"]').forEach((img) => {
      img.addEventListener('click', function() {
        const modal = document.getElementById('zoomModal');
        const modalImg = document.getElementById('zoomImage');
        if (modal && modalImg) {
          modalImg.src = this.src;
          modal.classList.remove('hidden');
        }
      });
    });
  }, 300);
};


function showKomentarModal(data) {
  let modal = document.getElementById('komentarModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'komentarModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden';
    modal.innerHTML = `
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Detail Komentar</h3>
          <button onclick="closeKomentarModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4" id="komentarModalContent"></div>
        <div class="flex justify-end p-4 border-t border-gray-200">
          <button onclick="closeKomentarModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
            Tutup
          </button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  }

  const content = document.getElementById('komentarModalContent');
  content.innerHTML = `
    <div class="space-y-3">
      <div class="flex items-center space-x-2">
        <i class="fas fa-user text-blue-500"></i>
        <span class="font-medium text-gray-700">Nama:</span>
        <span class="text-gray-900">${data.nama}</span>
      </div>
      <div class="flex items-center space-x-2">
        <i class="fas fa-phone text-green-500"></i>
        <span class="font-medium text-gray-700">Handphone:</span>
        <span class="text-gray-900">${data.handphone}</span>
      </div>
      <div class="flex items-center space-x-2">
        <i class="fas fa-star text-yellow-500"></i>
        <span class="font-medium text-gray-700">Rating:</span>
        <div class="flex items-center space-x-1">
          ${renderStars(data.rating)}
          <span class="text-sm text-gray-500 ml-1">(${data.rating})</span>
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <i class="fas fa-calendar text-purple-500"></i>
        <span class="font-medium text-gray-700">Tanggal:</span>
        <span class="text-gray-900">${data.tanggal}</span>
      </div>
      <div class="space-y-2">
        <div class="flex items-center space-x-2">
          <i class="fas fa-comment text-orange-500"></i>
          <span class="font-medium text-gray-700">Komentar:</span>
        </div>
        <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-orange-400">
          <p class="text-gray-800 whitespace-pre-wrap">${data.komentar || 'Tidak ada komentar'}</p>
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <i class="fas fa-receipt text-indigo-500"></i>
        <span class="font-medium text-gray-700">No. Bon:</span>
        <span class="text-gray-900 font-mono">${data.no_bon}</span>
      </div>
      <div class="flex items-center space-x-2">
        <i class="fas fa-store text-red-500"></i>
        <span class="font-medium text-gray-700">Cabang:</span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${data.cabang}</span>
      </div>
      <div class="flex items-center space-x-2">
        <i class="fas fa-user-tie text-teal-500"></i>
        <span class="font-medium text-gray-700">Nama Kasir:</span>
        <span class="text-gray-900">${data.nama_kasir}</span>
      </div>
    </div>
  `;

  modal.classList.remove('hidden');
  setTimeout(() => {
    modal.querySelector('.bg-white').classList.remove('scale-95');
    modal.querySelector('.bg-white').classList.add('scale-100');
  }, 10);
}

window.closeKomentarModal = function() {
  const modal = document.getElementById('komentarModal');
  if (modal) {
    modal.querySelector('.bg-white').classList.remove('scale-100');
    modal.querySelector('.bg-white').classList.add('scale-95');
    setTimeout(() => {
      modal.classList.add('hidden');
    }, 300);
  }
}

function renderStars(rating) {
  if (!rating) return "";
  const rounded = Math.round(rating);
  let stars = "";

  for (let i = 1; i <= 5; i++) {
    stars += `<i class="fa-solid fa-star ${i <= rounded ? 'text-yellow-400' : 'text-gray-300'}"></i>`;
  }

  return stars;
}

export default { renderTableReview };