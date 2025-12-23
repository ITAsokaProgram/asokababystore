import renderDetail from "./detail.js";

const tbody = document.querySelector("tbody");
export const renderTablePoin = ({ data, page = 1, limit = 15 }) => {
  if (!data || data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-2">Tidak ada data</td></tr>`;
    return;
  }
  tbody.innerHTML = "";
  let rows = "";
  data.forEach((item, index) => {
    const nomorUrut = (page - 1) * limit + index + 1;
    const statusText = item.status_aktif || "Aktif";
    const statusColor = statusText === "Aktif" ? "green" : "red";
    const formatDate = new Date(item.tgl_trans_terakhir);
    const tgl = formatDate.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });
    rows += `
      <tr class="border-b hover:bg-gray-50">
        <td class="px-4 py-2 text-center">${nomorUrut}</td>
        <td class="px-4 py-2" id='namaCust'>${item.nama_cust}</td>
        <td class="px-4 py-2">${item.kd_cust}</td>
        <td class="px-4 py-2 text-green-600 font-semibold">${
          item.total_poin_pk_pm
        }</td>
        <td class="px-4 py-2 text-green-600 font-semibold">${
          item.total_poin
        }</td>
        <td class="px-4 py-2">${tgl || "-"}</td>
        <td class="px-4 py-2">${item.nama_cabang || "-"}</td>
        <td class="px-4 py-2">
          <span class="px-2 py-1 text-xs bg-${statusColor}-100 text-${statusColor}-700 rounded-full">${statusText}</span>
        </td>
        <td class="px-4 py-2">
          <button class="text-blue-500 hover:underline text-xs lihat-detail" data-kode="${
            item.kd_cust
          }" data-nama="${item.nama_cust}">Lihat Detail</button>
        </td>
      </tr>
    `;
  });
  tbody.innerHTML = rows;
};

tbody.addEventListener("click", function (e) {
  if (e.target.classList.contains("lihat-detail")) {
    const kd_cust = e.target.getAttribute("data-kode");
    if (kd_cust) {
      openMemberModal();
      const nama = document.getElementById("namaMember");
      nama.textContent = e.target.getAttribute("data-nama");
      renderDetail(kd_cust);
    }
  }
});
function openMemberModal() {
  document.getElementById("memberDetailModal").classList.remove("hidden");
}

function closeMemberModal() {
  document.getElementById("memberDetailModal").classList.add("hidden");
}

document.getElementById("closeModal").addEventListener("click", (e) => {
  e.preventDefault();
  closeMemberModal();
});

export default { renderTablePoin };
