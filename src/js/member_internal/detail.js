import { detailPoin } from "./fetch/fetch_poin.js";

export const renderDetail = async (kode) => {
    const data = await detailPoin(kode);
    const tbody = document.getElementById('detailTbody');
    const numberPhone = document.getElementById('noHpMember');
    const totalPoinCell = document.getElementById('totalPoin');
    tbody.innerHTML = '';
    numberPhone.textContent = kode
    let totalPoin = 0;
    function toSortableDate(str) {
        const [day, month, year] = str.split('-');
        return `${year}-${month}-${day}`;
    }

    const sortedData = data.data.sort((a, b) =>
        new Date(toSortableDate(a.tanggal)) - new Date(toSortableDate(b.tanggal))
    );
    sortedData.forEach((item, index) => {
        const isPenukaran = item.keterangan_struk === 'Tukar Poin';
        const poin = parseInt(item.jumlah_point);

        let poinMasuk = 0;
        let poinKeluar = 0;

        if (isPenukaran) {
            poinKeluar = Math.abs(poin);
            totalPoin -= poinKeluar;
        } else {
            poinMasuk = poin;
            totalPoin += poinMasuk;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
      <td class="p-2 border text-center">${index + 1}</td>
      <td class="p-2 border">${item.no_trans}</td>
      <td class="p-2 border">${item.tanggal}</td>
      <td class="p-2 border text-green-600 text-center">${poinMasuk}</td>
      <td class="p-2 border text-red-600 text-center">${poinKeluar}</td>
      <td class="p-2 border text-blue-600 text-center">${totalPoin}</td>
      <td class="p-2 border text-center">${item.cabang}</td>
    `;
        tbody.appendChild(row);
    });

    totalPoinCell.textContent = totalPoin.toLocaleString();
};

export default renderDetail;