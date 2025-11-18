import { paginationCard } from "./table.js";
import { getTransDashboard } from "./fetch.js";
export const cardContainer = async (data) => {
  const container = document.getElementById("cabang-container");
  const jumlahMember = await getTransDashboard();
  data.forEach((item) => {
    const card = document.createElement("div");
    card.className =
      "bg-white rounded-xl p-4 shadow border border-gray-200 transition hover:bg-green-50 cursor-pointer";
    card.onclick = () => {
      window.location.href = `/src/fitur/transaction/detail_transaksi_cabang?cabang=${item.kd_store}`;
    };
    card.dataset.store = item.kd_store;
    card.innerHTML = `
      <div class="flex justify-between text-gray-600 mb-1">
        <span><i class="fa-solid fa-store text-green-600 mr-1"></i>${
          item.cabang
        }(${
      jumlahMember.data.jumlah_member_per_cabang.find(
        (m) => m.cabang === item.cabang
      )?.jumlah_member ?? 0
    })</span>
        <span class="text-gray-400 text-xs">Kemarin</span>
      </div>
      <div class="flex justify-between font-bold text-sm">
        <span>Total: <span class="text-green-600">${
          item.total_transaksi
        }</span></span>
        <span>Member: <span class="text-blue-600">${item.member}</span></span>
        <span>Non: <span class="text-red-500">${item.non_member}</span></span>
      </div>
    `;

    container.appendChild(card);
  });
};

export const cardContainerAll = async (data) => {
  const container = document.getElementById("cabang-container-all");
  data.forEach((item) => {
    const card = document.createElement("div");
    card.className =
      "bg-white rounded-xl p-4 shadow border border-gray-200 transition hover:bg-green-50 cursor-pointer";
    card.onclick = () => {
      window.location.href = `/src/fitur/transaction/detail_transaksi_cabang?cabang=all`;
    };
    card.innerHTML = `
      <div class="flex justify-between text-gray-600 mb-1">
        <span><i class="fa-solid fa-store text-green-600 mr-1"></i>Semua Cabang</span>
        <span class="text-gray-400 text-xs">Kemarin</span>
      </div>
      <div class="flex justify-between font-bold text-sm">
        <span>Total: <span class="text-green-600">${item.total_transaksi}</span></span>
        <span>Member: <span class="text-blue-600">${item.member}</span></span>
        <span>Non: <span class="text-red-500">${item.non_member}</span></span>
      </div>
    `;

    container.appendChild(card);
  });
};

export const detailCabang = (data) => {
  const ringkasan = data.total_trans[0];
  const transaksi = data.belanja;
  const topBarangMember = data.top_10_member;
  const topBarangNon = data.top_10_non;

  const transaksiMember = transaksi.filter(
    (trx) => trx.status_member === "Member"
  );
  const transaksiNonMember = transaksi.filter(
    (trx) => trx.status_member === "Non Member"
  );

  const ringkasanCardHTML = `
    <div class="mb-8">
      <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
          <i class="fas fa-building text-white text-2xl"></i>
        </div>
        <div>
          <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">${ringkasan.cabang}</h2>
          <p class="text-gray-600 text-lg">Ringkasan Transaksi Cabang</p>
          <p class="text-sm text-gray-500 font-medium mt-1">Data per: Kemarin</p>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 shadow-lg border border-green-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-shopping-cart text-white text-lg"></i>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-green-600">Total Transaksi</div>
              <div class="text-3xl font-bold text-green-700">${ringkasan.total_transaksi}</div>
            </div>
          </div>
          <div class="text-xs text-green-600 font-medium">Semua transaksi cabang</div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 shadow-lg border border-blue-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-user-check text-white text-lg"></i>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-blue-600">Member</div>
              <div class="text-3xl font-bold text-blue-700">${ringkasan.member}</div>
            </div>
          </div>
          <div class="text-xs text-blue-600 font-medium">Transaksi member aktif</div>
        </div>
        
        <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-2xl p-6 shadow-lg border border-red-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-user-xmark text-white text-lg"></i>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-red-600">Non Member</div>
              <div class="text-3xl font-bold text-red-700">${ringkasan.non_member}</div>
            </div>
          </div>
          <div class="text-xs text-red-600 font-medium">Transaksi non member</div>
        </div>
      </div>
    </div>
  `;

  const topBarangHTML = `
    <div class="space-y-8">
      <div class="text-center mb-8">
        <h3 class="text-2xl font-bold text-gray-800 mb-2">Produk Terlaris</h3>
        <p class="text-gray-600">Produk dengan penjualan tertinggi berdasarkan kategori member</p>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-8 shadow-xl border border-blue-200">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <i class="fas fa-user-check text-white text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-blue-800">Top Barang Member</h3>
              <p class="text-blue-600 text-xs">Produk favorit member</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            ${topBarangMember
              .map(
                (b, index) => `
                <div class="group bg-white rounded-2xl p-5 shadow-md border border-blue-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:border-blue-300">
                  <div class="flex items-start justify-between mb-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                      ${index + 1}
                    </div>
                    <div class="text-xs text-blue-600 font-medium bg-blue-100 px-2 py-1 rounded-full">
                      Member
                    </div>
                  </div>
                  <div class="font-medium text-gray-800 truncate mb-2 text-sm" title="${
                    b.barang
                  }">${b.barang}</div>
                  <div class="flex items-center gap-2 text-xs text-gray-600">
                    <i class="fas fa-shopping-bag text-blue-500"></i>
                    <span>Total dibeli: <span class="font-semibold text-blue-700">${
                      b.total_qty
                    }x</span></span>
                  </div>
                </div>
              `
              )
              .join("")}
          </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-3xl p-8 shadow-xl border border-red-200">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
              <i class="fas fa-user-xmark text-white text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-red-800">Top Barang Non Member</h3>
              <p class="text-red-600 text-xs">Produk favorit non member</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            ${topBarangNon
              .map(
                (b, index) => `
                <div class="group bg-white rounded-2xl p-5 shadow-md border border-red-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:border-red-300">
                  <div class="flex items-start justify-between mb-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-red-500 to-pink-600 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                      ${index + 1}
                    </div>
                    <div class="text-xs text-red-600 font-medium bg-red-100 px-2 py-1 rounded-full">
                      Non Member
                    </div>
                  </div>
                  <div class="font-medium text-gray-800 truncate mb-2 text-sm" title="${
                    b.barang
                  }">${b.barang}</div>
                  <div class="flex items-center gap-2 text-xs text-gray-600">
                    <i class="fas fa-shopping-bag text-red-500"></i>
                    <span>Total dibeli: <span class="font-semibold text-red-700">${
                      b.total_qty
                    }x</span></span>
                  </div>
                </div>
              `
              )
              .join("")}
          </div>
        </div>
      </div>
    </div>
  `;

  // Callback render tabel member
  const renderMemberTable = (paginatedData, offset) => {
    const rows = paginatedData
      .map(
        (trx, index) => `
    <tr class="text-sm text-gray-700 border-b hover:bg-blue-50 transition-colors duration-200">
      <td class="px-2 py-3 text-center font-medium w-12">${
        offset + index + 1
      }</td>
      <td class="px-2 py-3 w-32">
        <div class="truncate text-xs" title="${trx.nama_cust ?? "-"}">${
          trx.nama_cust ?? "-"
        }</div>
      </td>
      <td class="px-2 py-3 w-24">
        <div class="truncate font-mono text-xs" title="${trx.no_bon}">${
          trx.no_bon
        }</div>
      </td>
      <td class="px-2 py-3 text-center w-24 text-xs">${new Date(
        trx.tgl_trans
      ).toLocaleDateString("Id-ID")}</td>
      <td class="px-2 py-3 text-center font-semibold text-xs w-28">Rp ${Number(
        trx.total_belanja
      ).toLocaleString()}</td>
      <td class="px-2 py-3 w-28">
        <div class="truncate text-xs" title="${trx.nama_kasir}">${
          trx.nama_kasir
        }</div>
      </td>
    </tr>
  `
      )
      .join("");

    document.getElementById("member-table").innerHTML = `
      <div class="overflow-x-auto bg-white rounded-xl shadow-lg border border-blue-200">
        <table class="w-full table-fixed text-sm">
          <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs uppercase tracking-wide">
            <tr>
              <th class="px-2 py-3 text-center font-bold w-12">No</th>
              <th class="px-2 py-3 text-left font-bold w-32">Customer</th>
              <th class="px-2 py-3 text-left font-bold w-24">No Bon</th>
              <th class="px-2 py-3 text-center font-bold w-24">Tanggal</th>
              <th class="px-2 py-3 text-center font-bold w-28">Total</th>
              <th class="px-2 py-3 text-left font-bold w-28">Kasir</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">${rows}</tbody>
        </table>
      </div>`;
  };

  // Callback render tabel non-member
  const renderNonMemberTable = (paginatedData, offset) => {
    const rows = paginatedData
      .map(
        (trx, index) => `
    <tr class="text-sm text-gray-700 border-b hover:bg-red-50 transition-colors duration-200">
      <td class="px-2 py-3 text-center font-medium w-12">${
        offset + index + 1
      }</td>
      <td class="px-2 py-3 w-32">
        <div class="truncate text-xs" title="${trx.nama_cust ?? "-"}">${
          trx.nama_cust ?? "-"
        }</div>
      </td>
      <td class="px-2 py-3 w-24">
        <div class="truncate font-mono text-xs" title="${trx.no_bon}">${
          trx.no_bon
        }</div>
      </td>
      <td class="px-2 py-3 text-center w-24 text-xs">${new Date(
        trx.tgl_trans
      ).toLocaleDateString("Id-ID")}</td>
      <td class="px-2 py-3 text-center font-semibold text-xs w-28">Rp ${Number(
        trx.total_belanja
      ).toLocaleString()}</td>
      <td class="px-2 py-3 w-28">
        <div class="truncate text-xs" title="${trx.nama_kasir}">${
          trx.nama_kasir
        }</div>
      </td>
    </tr>
  `
      )
      .join("");

    document.getElementById("nonmember-table").innerHTML = `
      <div class="overflow-x-auto bg-white rounded-xl shadow-lg border border-red-200">
        <table class="w-full table-fixed text-sm">
          <thead class="bg-gradient-to-r from-red-500 to-red-600 text-white text-xs uppercase tracking-wide">
            <tr>
              <th class="px-2 py-3 text-center font-bold w-12">No</th>
              <th class="px-2 py-3 text-left font-bold w-32">Customer</th>
              <th class="px-2 py-3 text-left font-bold w-24">No Bon</th>
              <th class="px-2 py-3 text-center font-bold w-24">Tanggal</th>
              <th class="px-2 py-3 text-center font-bold w-28">Total</th>
              <th class="px-2 py-3 text-left font-bold w-28">Kasir</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">${rows}</tbody>
        </table>
      </div>`;
  };

  // Inject content utama
  document.getElementById("detail-content").innerHTML = `
    ${ringkasanCardHTML}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
      <div id="member-section">
        <h3 class="text-lg font-semibold mb-2 text-blue-700">Transaksi Member</h3>
        <div id="member-table"></div>
        <div id="paginationContainerMember" class="mt-2"></div>
        <div id="viewDataMember" class="text-sm text-gray-500 mt-1"></div>
      </div>
      <div id="nonmember-section">
        <h3 class="text-lg font-semibold mb-2 text-red-700">Transaksi Non Member</h3>
        <div id="nonmember-table"></div>
        <div id="paginationContainerNon" class="mt-2"></div>
        <div id="viewDataNon" class="text-sm text-gray-500 mt-1"></div>
      </div>
    </div>
    ${topBarangHTML}
  `;

  // Jalankan pagination
  paginationCard(
    1,
    10,
    transaksiMember,
    renderMemberTable,
    "paginationContainerMember",
    "viewDataMember"
  );
  paginationCard(
    1,
    10,
    transaksiNonMember,
    renderNonMemberTable,
    "paginationContainerNon",
    "viewDataNon"
  );
};
export const detailCabangAll = (data) => {
  const ringkasan = data.total_trans[0];
  const topBarangMember = data.top_10_member;
  const topBarangNon = data.top_10_non;

  const ringkasanCardHTML = `
    <div class="mb-8">
      <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
          <i class="fas fa-globe text-white text-2xl"></i>
        </div>
        <div>
          <h2 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Semua Cabang</h2>
          <p class="text-gray-600 text-lg">Ringkasan Transaksi Seluruh Cabang</p>
          <p class="text-sm text-gray-500 font-medium mt-1">Data per: Kemarin</p>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 shadow-lg border border-green-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-shopping-cart text-white text-lg"></i>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-green-600">Total Transaksi</div>
              <div class="text-3xl font-bold text-green-700">${ringkasan.total_transaksi}</div>
            </div>
          </div>
          <div class="text-xs text-green-600 font-medium">Semua transaksi cabang</div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 shadow-lg border border-blue-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-user-check text-white text-lg"></i>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-blue-600">Member</div>
              <div class="text-3xl font-bold text-blue-700">${ringkasan.member}</div>
            </div>
          </div>
          <div class="text-xs text-blue-600 font-medium">Transaksi member aktif</div>
        </div>
        
        <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-2xl p-6 shadow-lg border border-red-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-user-xmark text-white text-lg"></i>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-red-600">Non Member</div>
              <div class="text-3xl font-bold text-red-700">${ringkasan.non_member}</div>
            </div>
          </div>
          <div class="text-xs text-red-600 font-medium">Transaksi non member</div>
        </div>
      </div>
    </div>
  `;

  const topBarangHTML = `
    <div class="space-y-8">
      <div class="text-center mb-8">
        <h3 class="text-xl font-bold text-gray-800 mb-2">Produk Terlaris Nasional</h3>
        <p class="text-gray-600 text-sm">Produk dengan penjualan tertinggi di seluruh cabang</p>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-8 shadow-xl border border-blue-200">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <i class="fas fa-user-check text-white text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-blue-800">Top Barang Member</h3>
              <p class="text-blue-600 text-xs">Produk favorit member nasional</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            ${topBarangMember
              .map(
                (b, index) => `
                <div class="group bg-white rounded-2xl p-5 shadow-md border border-blue-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:border-blue-300">
                  <div class="flex items-start justify-between mb-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                      ${index + 1}
                    </div>
                    <div class="text-xs text-blue-600 font-medium bg-blue-100 px-2 py-1 rounded-full">
                      Member
                    </div>
                  </div>
                  <div class="font-medium text-gray-800 truncate mb-2 text-sm" title="${
                    b.barang
                  }">${b.barang}</div>
                  <div class="flex items-center gap-2 text-xs text-gray-600">
                    <i class="fas fa-shopping-bag text-blue-500"></i>
                    <span>Total dibeli: <span class="font-semibold text-blue-700">${
                      b.total_qty
                    }x</span></span>
                  </div>
                </div>
              `
              )
              .join("")}
          </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-3xl p-8 shadow-xl border border-red-200">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
              <i class="fas fa-user-xmark text-white text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-red-800">Top Barang Non Member</h3>
              <p class="text-red-600 text-xs">Produk favorit non member nasional</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            ${topBarangNon
              .map(
                (b, index) => `
                <div class="group bg-white rounded-2xl p-5 shadow-md border border-red-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:border-red-300">
                  <div class="flex items-start justify-between mb-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-red-500 to-pink-600 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                      ${index + 1}
                    </div>
                    <div class="text-xs text-red-600 font-medium bg-red-100 px-2 py-1 rounded-full">
                      Non Member
                    </div>
                  </div>
                  <div class="font-medium text-gray-800 truncate mb-2 text-sm" title="${
                    b.barang
                  }">${b.barang}</div>
                  <div class="flex items-center gap-2 text-xs text-gray-600">
                    <i class="fas fa-shopping-bag text-red-500"></i>
                    <span>Total dibeli: <span class="font-semibold text-red-700">${
                      b.total_qty
                    }x</span></span>
                  </div>
                </div>
              `
              )
              .join("")}
          </div>
        </div>
      </div>
    </div>
  `;

  // Inject content utama
  document.getElementById("detail-content").innerHTML = `
    ${ringkasanCardHTML}
    ${topBarangHTML}
  `;
};
export default {
  cardContainer,
  cardContainerAll,
  detailCabang,
  detailCabangAll,
};
