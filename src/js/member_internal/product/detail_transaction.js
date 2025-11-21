function showDetailModal(data) {
  const modal = document.getElementById("detail-modal");
  const title = document.getElementById("modal-title");
  const subtitle = document.getElementById("modal-subtitle");
  const content = document.getElementById("modal-content");
  const timestamp = document.getElementById("modal-timestamp");
  const icon = document.getElementById("modal-icon");
  timestamp.textContent = `Ditampilkan pada: ${new Date().toLocaleString(
    "id-ID"
  )}`;
  if (data[0].kd_cust) {
    title.textContent = "Detail Member";
    subtitle.textContent = `ID: ${data[0].kd_cust}`;
    icon.innerHTML = `
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            `;
    content.innerHTML = `
                  <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-2 border border-blue-200"> <h3 class="text-lg font-semibold text-blue-800 mb-2 flex items-center"> <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                          </svg>
                          Informasi Member
                      </h3>
                      <div class="grid grid-cols-2 gap-2"> <div class="space-y-1"> <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">No. Customer:</span>
                                  <span class="font-semibold text-blue-700">${
                                    data[0].kd_cust
                                  }</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Sumber:</span>
                                  <span class="font-semibold text-gray-800">${
                                    data[0].sumber
                                  }</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Status:</span>
                                  <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active Member</span>
                              </div>
                          </div>
                          <div class="space-y-1"> <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Total Penjualan:</span>
                                  <span class="font-bold text-green-600">${formatCurrency(
                                    data
                                      .map((item) => item.nominal)
                                      .reduce((sum, item) => sum + item, 0)
                                  )}</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Poin:</span>
                                  <span class="font-semibold text-yellow-600">${
                                    data[0].jumlah_point || 0
                                  }</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Tanggal:</span>
                                  <span class="font-semibold text-gray-700">${
                                    data[0].tanggal
                                  }</span>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="grid grid-cols-3 gap-2"> <div class="bg-blue-50 rounded-lg p-2 text-center border border-blue-200"> <div class="text-2xl font-bold text-blue-600">${
                    data[0].no_trans
                  }</div>
                          <div class="text-sm text-blue-700">No. Transaksi</div>
                      </div>
                      <div class="bg-green-50 rounded-lg p-2 text-center border border-green-200"> <div class="text-2xl font-bold text-green-600">${formatCurrency(
                        data[0].nominal
                      )}</div>
                          <div class="text-sm text-green-700">Total Pembelian</div>
                      </div>
                      <div class="bg-purple-50 rounded-lg p-2 text-center border border-purple-200"> <div class="text-2xl font-bold text-purple-600">${
                        data[0].cabang
                      }</div>
                          <div class="text-sm text-purple-700">Cabang</div>
                      </div>
                  </div>
                  <div class="bg-gray-50 rounded-xl p-2 border border-gray-200"> <h3 class="text-lg font-semibold text-gray-800 mb-2 flex items-center"> <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                          </svg>
                          Detail Transaksi
                      </h3>
                      <div class="space-y-1"> ${data
                        .map(
                          (item) => `
                          <div class="flex justify-between items-center py-1 border-b border-gray-200"> <div>
                                  <div class="font-medium text-gray-800">${
                                    item.no_trans
                                  }</div>
                                  <div class="text-sm text-gray-500">${
                                    item.tanggal
                                  } - ${item.jam}</div>
                              </div>
                              <div class="text-right">
                                  <div class="font-semibold text-green-600">${formatCurrency(
                                    item.nominal
                                  )}</div>
                                  <div class="text-sm text-green-500 cursor-pointer" data-detail-transaction="${
                                    item.no_trans
                                  }" data-kd-cust="${item.kd_cust}">Detail</div>
                              </div>
                          </div>
                      `
                        )
                        .join("")}
                      </div>
                  </div>
                `;
  } else if (data[0].kode_transaksi) {
    title.textContent = "Detail";
    subtitle.textContent = `ID: ${data[0].kode_transaksi}`;
    icon.innerHTML = `
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
              </svg>
            `;
    content.innerHTML = `
                  <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-2 border border-green-200"> <h3 class="text-lg font-semibold text-green-800 mb-2 flex items-center"> <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                          </svg>
                          Informasi Transaksi
                      </h3>
                      <div class="grid grid-cols-2 gap-2"> <div class="space-y-1"> <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Kode Transaksi:</span>
                                  <span class="font-semibold text-gray-800">${
                                    data[0].kode_transaksi
                                  }</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Status:</span>
                                  <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium">Non-Member</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Kasir:</span>
                                  <span class="font-semibold text-gray-700">${
                                    data[0].kasir
                                  }</span>
                              </div>
                          </div>
                          <div class="space-y-1"> <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Total Penjualan:</span>
                                  <span class="font-bold text-green-600">${formatCurrency(
                                    data.reduce(
                                      (sum, item) => sum + (item.nominal || 0),
                                      0
                                    )
                                  )}</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Jumlah Item:</span>
                                  <span class="font-semibold text-blue-600">
                                      ${data.reduce(
                                        (sum, item) =>
                                          sum + (item.jumlah_item || 0),
                                        0
                                      )}
                                  </span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Tanggal:</span>
                                  <span class="font-semibold text-gray-700">${
                                    data[0].tanggal
                                  }</span>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="bg-white rounded-xl border border-gray-200">
                      <div class="bg-gray-50 px-2 py-2 border-b border-gray-200"> <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                  <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                              </svg>
                              Detail Item
                          </h3>
                      </div>
                      <div class="p-2 space-y-2"> ${data
                        .map(
                          (item, index) => `
                          <div class="flex justify-between items-center border-b border-gray-200 pb-1"> <div>
                                  <div class="font-medium text-gray-800">${
                                    item.nama_item
                                  }</div>
                                  <div class="text-sm text-gray-500">Qty: ${
                                    item.jumlah_item
                                  }</div>
                              </div>
                              <div class="text-right">
                                  <div class="font-semibold text-green-700">${formatCurrency(
                                    item.nominal
                                  )}</div>
                              </div>
                          </div>
                        `
                        )
                        .join("")}
                          <div class="flex justify-between text-lg font-bold pt-2"> <span class="text-gray-800">Grand Total:</span>
                              <span class="text-green-600">
                                  ${formatCurrency(
                                    data.reduce(
                                      (sum, item) => sum + (item.nominal || 0),
                                      0
                                    )
                                  )}
                              </span>
                          </div>
                      </div>
                  </div>
                  <div class="bg-gray-50 rounded-xl p-2 border border-gray-200"> <h3 class="text-lg font-semibold text-gray-800 mb-2 flex items-center"> <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                          </svg>
                          Informasi Transaksi
                      </h3>
                      <div class="space-y-1"> <div class="flex justify-between items-center py-1 border-b border-gray-200"> <div>
                                  <div class="font-medium text-gray-800">${
                                    data[0].kode_transaksi
                                  }</div>
                                  <div class="text-sm text-gray-500">${
                                    data[0].tanggal
                                  } - ${data[0].jam_trs}</div>
                              </div>
                              <div class="text-right">
                                  <div class="font-semibold text-green-600">${formatCurrency(
                                    data.reduce(
                                      (sum, item) => sum + (item.nominal || 0),
                                      0
                                    )
                                  )}</div>
                                  <div class="text-sm text-green-500">Selesai</div>
                              </div>
                          </div>
                          <div class="flex justify-between items-center py-1"> <div>
                                  <div class="font-medium text-gray-800">Cabang: ${
                                    data[0].cabang
                                  }</div>
                                  <div class="text-sm text-gray-500">Kasir: ${
                                    data[0].kasir
                                  }</div>
                              </div>
                          </div>
                      </div>
                  </div>
                `;
  } else {
    title.textContent = "Detail Transaksi";
    subtitle.textContent = "Data tidak ditemukan";
    content.innerHTML = `
    <div class="bg-red-50 rounded-xl p-2 border border-red-200"> <h3 class="text-lg font-semibold text-red-800">Tidak ada data</h3>
    <p class="text-sm text-red-500">Data tidak ditemukan atau format tidak sesuai</p>
    </div>
    `;
  }
  modal.classList.remove("hidden");
}
function formatCurrency(amount) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(amount);
}
function showDetailModalMember(data) {
  const modal = document.getElementById("detail-modal");
  const title = document.getElementById("modal-title");
  const subtitle = document.getElementById("modal-subtitle");
  const content = document.getElementById("modal-content");
  const timestamp = document.getElementById("modal-timestamp");
  const icon = document.getElementById("modal-icon");
  timestamp.textContent = `Ditampilkan pada: ${new Date().toLocaleString(
    "id-ID"
  )}`;
  title.textContent = "Detail Transaksi Member";
  subtitle.textContent = `ID: ${data[0].kode_transaksi}`;
  icon.innerHTML = `
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                </svg>
              `;
  content.innerHTML = `
                  <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-2 border border-green-200"> <h3 class="text-lg font-semibold text-green-800 mb-2 flex items-center"> <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                          </svg>
                          Informasi Transaksi
                      </h3>
                      <div class="grid grid-cols-2 gap-2"> <div class="space-y-1"> <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Kode Transaksi:</span>
                                  <span class="font-semibold text-gray-800">${
                                    data[0].kode_transaksi
                                  }</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Status:</span>
                                  <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active Member</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Kasir:</span>
                                  <span class="font-semibold text-gray-700">${
                                    data[0].kasir
                                  }</span>
                              </div>
                          </div>
                          <div class="space-y-1"> <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Total Penjualan:</span>
                                  <span class="font-bold text-green-600">${formatCurrency(
                                    data
                                      .map((item) => item.nominal)
                                      .reduce((sum, item) => sum + item, 0)
                                  )}</span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Jumlah Item:</span>
                                  <span class="font-semibold text-blue-600">
                                      ${data.reduce(
                                        (sum, item) =>
                                          sum + (item.jumlah_item || 0),
                                        0
                                      )}
                                  </span>
                              </div>
                              <div class="flex justify-between">
                                  <span class="font-medium text-gray-600">Tanggal:</span>
                                  <span class="font-semibold text-gray-700">${
                                    data[0].tanggal
                                  }</span>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="bg-white rounded-xl border border-gray-200">
                      <div class="bg-gray-50 px-2 py-2 border-b border-gray-200"> <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                  <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                              </svg>
                              Detail Item
                          </h3>
                      </div>
                      <div class="p-2 space-y-2"> ${data
                        .map(
                          (item) => `
                            <div class="flex justify-between items-center border-b border-gray-200 pb-1"> <div>
                                    <div class="font-medium text-gray-800">${
                                      item.nama_item
                                    }</div>
                                    <div class="text-sm text-gray-500">Qty: ${
                                      item.jumlah_item
                                    }</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-green-700">${formatCurrency(
                                      item.nominal
                                    )}</div>
                                </div>
                            </div>
                          `
                        )
                        .join("")}
                          <div class="flex justify-between text-lg font-bold pt-2"> <span class="text-gray-800">Grand Total:</span>
                              <span class="text-green-600">
                                  ${formatCurrency(
                                    data.reduce(
                                      (sum, item) => sum + (item.nominal || 0),
                                      0
                                    )
                                  )}
                              </span>
                          </div>
                      </div>
                  </div>
                  <div class="bg-gray-50 rounded-xl p-2 border border-gray-200"> <h3 class="text-lg font-semibold text-gray-800 mb-2 flex items-center"> <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                          </svg>
                          Informasi Transaksi
                      </h3>
                      <div class="space-y-1"> <div class="flex justify-between items-center py-1 border-b border-gray-200"> <div>
                                  <div class="font-medium text-gray-800">${
                                    data[0].kode_transaksi
                                  }</div>
                                  <div class="text-sm text-gray-500">${
                                    data[0].tanggal
                                  } - ${data[0].jam_trs}</div>
                              </div>
                              <div class="text-right">
                                  <div class="font-semibold text-green-600">${formatCurrency(
                                    data.reduce(
                                      (sum, item) => sum + (item.nominal || 0),
                                      0
                                    )
                                  )}</div>
                                  <div class="text-sm text-green-500">Selesai</div>
                              </div>
                          </div>
                          <div class="flex justify-between items-center py-1"> <div>
                                  <div class="font-medium text-gray-800">Cabang: ${
                                    data[0].cabang
                                  }</div>
                                  <div class="text-sm text-gray-500">Kasir: ${
                                    data[0].kasir
                                  }</div>
                              </div>
                          </div>
                      </div>
                  </div>
                `;
  modal.classList.remove("hidden");
}
document.getElementById("close-modal").onclick = function () {
  document.getElementById("detail-modal").classList.add("hidden");
};
document.getElementById("detail-modal").onclick = function (e) {
  if (e.target === this) {
    this.classList.add("hidden");
  }
};
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    document.getElementById("detail-modal").classList.add("hidden");
  }
});
window.topMembersData = [];
window.topNonMembersData = [];
export { showDetailModal, showDetailModalMember };
