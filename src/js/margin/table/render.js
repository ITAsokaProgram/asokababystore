export const renderTableDefault = (data, offset = 0) => {
  const tbody = document.querySelector("#kategoriTable");

  // Reset UI
  const checkAll = document.getElementById("checkAll");
  if (checkAll) checkAll.checked = false;
  document.getElementById("btnBulkUpdate")?.classList.add("hidden");

  tbody.innerHTML = "";
  let row = "";

  const formatAngka = (angka) => {
    return Number(angka).toLocaleString("id-ID");
  };

  data.forEach((item, index) => {
    const formatDate = new Date(item.tgl);
    const tgl = formatDate.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });

    // Data object lengkap untuk dikirim saat bulk update
    const itemData = JSON.stringify({
      plu: item.plu,
      bon: item.no_trans,
      barang: item.descp,
      qty: item.qty,
      gros: item.GROSS,
      net: item.net,
      avg: item.avg_cost,
      ppn: item.PPN,
      margin: item.Margin,
      tgl: item.tgl,
      cabang: item.cabang,
      kd: item.kode,
    });

    row += `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2 text-center">
                <input type="checkbox" class="check-item w-4 h-4 text-pink-600 bg-gray-100 border-gray-300 rounded focus:ring-pink-500 cursor-pointer" value='${itemData}'>
            </td>
            <td class='px-4 py-2'> ${offset + index + 1} </td>
            <td class='px-4 py-2'> ${item.plu} </td>
            <td class='px-4 py-2 truncate' title="${item.no_trans}"> ${
      item.no_trans
    } </td>
            <td class='px-4 py-2 truncate' title="${item.descp}"> ${
      item.descp
    } </td>
            <td class='px-4 py-2 text-center'> ${item.qty} </td>
            <td class='px-4 py-2 text-center'> ${formatAngka(item.GROSS)} </td>
            <td class='px-4 py-2 text-center'> ${formatAngka(item.net)} </td>
            <td class='px-4 py-2 text-center'> ${formatAngka(
              item.avg_cost
            )} </td>
            <td class='px-4 py-2 text-center'> ${formatAngka(item.PPN)} </td>
            <td class='px-4 py-2 text-center font-bold ${
              Number(item.Margin) < 0 ? "text-red-600" : "text-green-600"
            }'> ${formatAngka(item.Margin)} </td>
            <td class='px-4 py-2'> ${tgl} </td>
            <td class='px-4 py-2'> ${item.cabang} </td>
            <td class="px-4 py-2 text-center">
            ${
              item.status_cek === 1 && item.status_cek !== null
                ? `<button class="text-green-600 hover:text-green-800 lihat-keterangan" 
                                data-plu="${item.plu}"
                                data-bon="${item.no_trans}"
                                data-cabang="${item.kode}"
                                title="Lihat keterangan">
                            <i class="fas fa-check-circle text-xl"></i>
                  </button>`
                : `
                <button class="text-red-600 hover:text-red-800 checking" 
                data-plu="${item.plu}"
                data-bon="${item.no_trans}"
                data-barang="${item.descp}"
                data-qty="${item.qty}"
                data-gros="${item.GROSS}"
                data-net="${item.net}"
                data-avg="${item.avg_cost}"
                data-ppn="${item.PPN}"
                data-margin="${item.Margin}"
                data-tgl="${item.tgl}"
                data-cabang="${item.cabang}"
                data-store="${item.kode}"
                >
                  <i class="fas fa-times-circle text-xl"></i>
                </button>
              `
            }
            </td>
        </tr>
        `;
  });
  tbody.innerHTML = row;
};

export const renderTop3Minus = (data) => {
  const minusData = data
    .filter((d) => Number(d.Margin) < 0)
    .sort((a, b) => Number(a.Margin) - Number(b.Margin))
    .slice(0, 3);

  const container = document.getElementById("top3-minus-summary");
  if (!container) return; // Prevent error if element not exists

  container.innerHTML = minusData
    .map(
      (item, idx) => `
    <div class="bg-gradient-to-br from-rose-100 to-rose-50 border border-rose-200 rounded-2xl p-6 flex flex-col gap-2 shadow-lg">
    <div class="flex items-center gap-3">
    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-pink-400 shadow-lg">
    <i class="fas fa-arrow-trend-down text-white text-2xl"></i>
    </span>
    <div>
    <div class="text-xs text-rose-600 font-semibold uppercase font-poppins tracking-wide">Minus #${
      idx + 1
    }</div>
    <div class="text-lg font-bold text-rose-700 font-poppins leading-tight">${
      item.cabang
    }</div>
    </div>
    </div>
    <div class="mt-2">
    <div class="text-xs text-gray-500">Margin</div>
    <div class="text-2xl font-extrabold text-rose-600">Rp ${Number(
      item.Margin
    ).toLocaleString("id-ID")}</div>
    </div>
    </div>
    `
    )
    .join("");
};

export const renderMinusMarginCards = (data) => {
  const container = document.getElementById("minus-margin-cards");
  if (!container) return;

  const minusData = data
    .filter((d) => Number(d.Margin) < 0)
    .sort((a, b) => Number(a.Margin) - Number(b.Margin));

  const groups = [
    {
      data: minusData.slice(0, 10),
      title: "Margin Tertinggi",
      subtitle: "1-10",
      color: "red",
      icon: "💰",
    },
    {
      data: minusData.slice(10, 20),
      title: "Margin Menengah",
      subtitle: "11-20",
      color: "orange",
      icon: "💰",
    },
    {
      data: minusData.slice(20, 30),
      title: "Margin Rendah",
      subtitle: "21-30",
      color: "yellow",
      icon: "💰",
    },
  ];

  const createAdvancedCard = (group, startIndex) => {
    const { data: items, title, subtitle, color, icon } = group;

    if (items.length === 0) {
      return `
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-${color}-500 to-${color}-600 px-6 py-4">
            <div class="flex items-center gap-3 text-white">
              <span class="text-2xl">${icon}</span>
              <div>
                <h3 class="font-bold text-lg">${title}</h3>
                <p class="text-${color}-100 text-sm">${subtitle}</p>
              </div>
            </div>
          </div>
          <div class="p-6 text-center">
            <div class="text-gray-400 text-6xl mb-4">📈</div>
            <p class="text-gray-500">Tidak ada data</p>
          </div>
        </div>
      `;
    }

    return `
      <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="bg-gradient-to-r from-${color}-500 to-${color}-600 px-6 py-4">
          <div class="flex items-center justify-between text-white">
            <div class="flex items-center gap-3">
              <span class="text-2xl">${icon}</span>
              <div>
                <h3 class="font-bold text-lg">${title}</h3>
                <p class="text-${color}-100 text-sm">${subtitle}</p>
              </div>
            </div>
            <div class="bg-white bg-opacity-20 rounded-full px-3 py-1">
              <span class="text-sm font-medium">${items.length}</span>
            </div>
          </div>
        </div>
        
        <div class="max-h-[90vh] overflow-y-auto">
          ${items
            .map(
              (item, idx) => `
                <div class="cursor-pointer item-detail border-b border-gray-100 last:border-b-0 hover:bg-${color}-50 transition-colors duration-200" data-plu="${
                item.plu
              }" data-bon="${item.no_trans}" data-store="${
                item.kode
              }" data-store-name="${item.cabang}">
                  <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                          <div class="w-8 h-8 bg-gradient-to-r from-${color}-500 to-${color}-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">${
                              startIndex + idx
                            }</span>
                          </div>
                        </div>
                        <div>
                          <div class="font-semibold text-gray-900">${
                            item.cabang
                          }</div>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="font-bold text-${color}-700">
                          Rp ${Number(item.Margin).toLocaleString("id-ID")}
                        </div>
                        <div class="text-sm text-gray-500">Kerugian</div>
                      </div>
                    </div>
                  </div>
                </div>
              `
            )
            .join("")}
        </div>
        
        <div class="bg-gray-50 px-6 py-3 text-center">
          <p class="text-sm text-gray-600">
            Total Margin: <span class="font-bold text-${color}-700">
              Rp ${items
                .reduce((sum, item) => sum + Number(item.Margin), 0)
                .toLocaleString("id-ID")}
            </span>
          </p>
        </div>
      </div>
    `;
  };

  container.innerHTML = `
      ${groups
        .map((group, index) => createAdvancedCard(group, index * 10 + 1))
        .join("")}
  `;
};

export const renderDetailMargin = (data) => {
  const container = document.getElementById("detailTbody");
  if (!container) return;

  container.innerHTML = "";
  let row = "";
  const formatAngka = (angka) => Number(angka).toLocaleString("id-ID");

  data.forEach((item, index) => {
    row += `
    <tr class="hover:bg-emerald-50 text-center">
    <td class="px-4 py-2 text-center">${index + 1}</td>
    <td class="px-4 py-2 text-center">${item.plu}</td>
    <td class="px-4 py-2 text-left truncate" title="${item.no_trans}">${
      item.no_trans
    }</td>
    <td class="px-4 py-2 text-left truncate" title="${item.descp}">${
      item.descp
    }</td>
    <td class="px-4 py-2 text-center">${item.qty}</td>
    <td class="px-4 py-2 text-center">Rp.${formatAngka(item.GROSS)}</td>
    <td class="px-4 py-2 text-center">Rp.${formatAngka(item.net)}</td>
    <td class="px-4 py-2 text-center">Rp.${formatAngka(item.avg_cost)}</td>
    <td class="px-4 py-2 text-center">Rp.${formatAngka(item.PPN)}</td>
    <td class="px-4 py-2 text-center hover:text-red-500 hover:font-bold hover:underline">Rp.${formatAngka(
      item.Margin
    )}</td>
    <td class="px-4 py-2 truncate" title="${item.tgl}">${
      item.tgl.split(" ")[0]
    }</td>
    <td class="px-4 py-2">${item.cabang}</td>
    </tr>
    `;
  });
  container.innerHTML = row;
};

export default {
  renderTableDefault,
  renderTop3Minus,
  renderMinusMarginCards,
  renderDetailMargin,
};
