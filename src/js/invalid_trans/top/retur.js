import { fetchTopRetur, fetchDetailKategori } from "../fetch/all_kategori.js";

// Render cashier cards
function renderCashierCards(cashierList) {
  const container = document.getElementById("cashier-cards-container");
  container.innerHTML = "";
  cashierList.forEach((cashier, idx) => {
    const card = createCashierCard(cashier, idx);
    container.appendChild(card);
  });
}

// Render recent transactions table (ambil 5 terbaru)
function renderRecentTransactions(transactions) {
  const tbody = document.getElementById("recent-transactions");
  tbody.innerHTML = "";
  transactions
    .sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal))
    .slice(0, 5)
    .forEach((trx) => {
      const row = document.createElement("tr");
      row.className =
        "border-b border-gray-200 hover:bg-violet-50 transition-colors duration-200";
      row.innerHTML = `
        <td class="py-3 px-4 font-medium">${trx.kode}</td>
        <td class="py-3 px-4">${trx.kasir}</td>
        <td class="py-3 px-4">${trx.no_trans}</td>
        <td class="py-3 px-4">${trx.barang}</td>
        <td class="py-3 px-4">${trx.plu}</td>
        <td class="py-3 px-4">${new Date(trx.tanggal).toLocaleDateString("id-ID")}</td>
        <td class="py-3 px-4">${trx.jml_retur}</td>
        <td class="py-3 px-4">${trx.cabang}</td>
        <td class="py-3 px-4">${trx.ket}</td>
        ${trx.ket_cek === null || trx.ket_cek === undefined ? 
          '<td class="py-3 px-4"><span class="status-badge status-warning">Belum Cek</span></td>' :
           '<td class="py-3 px-4"><span class="status-badge status-success">Sudah Cek</span></td>'}
      `;
      tbody.appendChild(row);
    });
}

// Card kasir
function createCashierCard(cashier, index) {
  const card = document.createElement("div");
  card.className =
    "bg-white/80 backdrop-blur-md border border-violet-200 rounded-3xl p-6 shadow-lg card-hover cursor-pointer";
  card.onclick = () => showCashierDetail(cashier);

  const colors = ["violet", "blue", "green", "orange", "red", "purple"];
  const color = colors[index % colors.length];

  card.innerHTML = `
    <div class="flex items-center justify-between mb-4">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-${color}-500 to-${color}-600 flex items-center justify-center">
        <i class="fas fa-user text-white text-xl"></i>
      </div>
    </div>
    <h3 class="text-lg font-semibold text-gray-700 mb-2">${cashier.kasir || cashier.name}</h3>
    <div class="text-3xl font-bold text-${color}-600 mb-2">${cashier.jml_retur || cashier.returns}</div>
    <div class="text-sm text-gray-500 mb-3">
      Cabang: ${cashier.cabang}
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2">
      <div class="progress-bar" style="width: 100%"></div>
    </div>
  `;
  return card;
}

// Show detail modal for cashier
async function showCashierDetail(cashier) {
  // Gunakan tanggal dari data kasir, atau fallback ke hari ini
  const tanggalAwal = cashier.tanggal ? cashier.tanggal.split(" ")[0] : new Date().toISOString().split("T")[0];
  const tanggalAkhir = new Date().toISOString().split("T")[0];
  const data = await fetchDetailKategori( "%RETUR%", cashier.kode, tanggalAwal, tanggalAkhir);

  document.getElementById("modal-title").textContent = `Detail Retur - ${cashier.kasir || cashier.name}`;
  document.getElementById("modal-subtitle").textContent = `${cashier.jml_retur || cashier.returns} transaksi retur`;
  const tbody = document.getElementById("modal-detail-tbody");
  tbody.innerHTML = "";
  data.data.forEach((transaction, index) => {
    const row = document.createElement("tr");
    row.className =
      "table-stripe hover:bg-violet-50 transition-colors duration-200";
    row.innerHTML = `
      <td class="py-3 px-4">${index + 1}</td>
      <td class="py-3 px-4 font-medium truncate">${transaction.no_trans}</td>
      <td class="py-3 px-4 truncate">${transaction.nama_product}</td>
      <td class="py-3 px-4 truncate">${transaction.barcode}</td>
      <td class="py-3 px-4">${transaction.kode}</td>
      <td class="py-3 px-4">${transaction.kasir}</td>
      <td class="py-3 px-4">${new Date(transaction.tgl).toLocaleDateString("id-ID")}</td>
      <td class="py-3 px-4">${transaction.cabang}</td>
      <td class="py-3 px-4">${transaction.nama_cek ? transaction.nama_cek : '-'}</td>
      <td class="py-3 px-4 truncate">${transaction.ket_cek ? transaction.ket_cek : '-'}</td>
      <td class="py-3 px-4">${transaction.ket_cek === null || transaction.ket_cek === undefined ? 
        '<span class="status-badge status-warning truncate">Belum Cek</span>' :
         '<span class="status-badge status-success truncate">Sudah Cek</span>'}</td>
    `;
    tbody.appendChild(row);
  });
  document.getElementById("modal-detail").classList.remove("hidden");
}

// Main init
async function init() {
  const data = await fetchTopRetur();

  // Statistik atas
  document.getElementById("stat-retur-today").textContent =
    data.summaryTotalReturDay[0].jml_retur;
  document.getElementById("stat-retur-month").textContent =
    data.summaryTotalReturMonth[0].jml_retur;

  // Card kasir
  renderCashierCards(data.data);

  // Tabel retur terbaru
  renderRecentTransactions(data.data);

  // Set update time setelah load sukses
  const now = new Date();
  document.getElementById('last-update').textContent = now.toLocaleString('id-ID', {
    dateStyle: 'medium', timeStyle: 'short'
  });
}

// Modal close event
window.showCashierDetail = showCashierDetail;
document.getElementById("close-modal").addEventListener("click", () => {
  document.getElementById("modal-detail").classList.add("hidden");
});
document.getElementById("modal-detail").addEventListener("click", (e) => {
  if (e.target.id === "modal-detail") {
    document.getElementById("modal-detail").classList.add("hidden");
  }
});

// --- BUTTON HANDLERS ---
// Export to Excel
if (window.XLSX === undefined) {
  const script = document.createElement('script');
  script.src = 'https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js';
  script.onload = () => {};
  document.head.appendChild(script);
}

document.getElementById('export-btn').addEventListener('click', async () => {
  const data = await fetchTopRetur();
  const wsData = [
    [
      'Kode', 'Kasir', 'No. Bon', 'Tanggal', 'Jumlah', 'Cabang', 'Keterangan', 'Status'
    ],
    ...data.data.map(trx => [
      trx.kode,
      trx.kasir,
      trx.no_trans,
      new Date(trx.tanggal).toLocaleDateString('id-ID'),
      trx.jml_retur,
      trx.cabang,
      trx.ket,
      (trx.ket_cek === null || trx.ket_cek === undefined) ? 'Belum Cek' : 'Sudah Cek'
    ])
  ];
  const ws = window.XLSX.utils.aoa_to_sheet(wsData);
  const wb = window.XLSX.utils.book_new();
  window.XLSX.utils.book_append_sheet(wb, ws, 'Retur');
  window.XLSX.writeFile(wb, 'Retur_Transaksi.xlsx');
});

// Refresh button
const refreshBtn = document.getElementById('refresh-btn');
if (refreshBtn) {
  refreshBtn.addEventListener('click', () => {
    init();
  });
}

init();
