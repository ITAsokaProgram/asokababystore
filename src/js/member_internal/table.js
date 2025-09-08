export const renderTable = (responseData) => {
  const data = responseData.data; // <-- ambil property `data` dulu

  const tbody = document.getElementById('memberTableBody');
  tbody.innerHTML = '';
  data.top_10_member?.forEach(m => {
    tbody.innerHTML += `
            <tr>
              <td class="p-2">${m.nama_cust}</td>
              <td class="p-2">${m.Nm_Alias}</td>
              <td class="p-2">Rp ${Number(m.total_belanja).toLocaleString()}</td>
            </tr>
          `;
  });

  const barangBody = document.getElementById('topProductTableBody');
  barangBody.innerHTML = '';
  data.top_10_barang?.forEach(b => {
    barangBody.innerHTML += `
            <tr>
              <td class="p-2">${b.barcode}</td>
              <td class="p-2">${b.descp}</td>
              <td class="p-2 text-center">${b.total_terjual}</td>
            </tr>
          `;
  });
};

export const renderTableNon = (responseData, limit = 10, page = 1) => {
  const data = responseData.data;
  const tbody = document.getElementById('nonactiveMemberTableBody');
  const start = (page - 1) * limit;
  const end = start + limit;
  tbody.innerHTML = '';
  data.segmen.slice(start, end).forEach(s => {
    tbody.innerHTML += `
      <tr>
      <td class="p-2"> ${s.kd_cust}</td>
      <td class="p-2"> ${s.nama_cust}</td>
      <td class="p-2"> ${s.tgl_trans_terakhir.split(' ')[0]}</td>
      <td class="p-2"> ${s.nama_cabang}</td>
      </tr>
    `
  })
};

export default { renderTable, renderTableNon};