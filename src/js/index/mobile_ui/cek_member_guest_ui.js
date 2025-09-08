export const modalViewMemberOrNot = (data, updateProfile) => {
    const modalContent = document.getElementById("modalContent1");
    const eDiv = document.createElement("div");
    const addHtml = `
<div class="text-sm text-gray-800 space-y-4">
  <div class="grid grid-cols-2 gap-y-2 border-b pb-2">
    <div class="font-semibold">Kode Member:</div>
    <div>${data.data.customer.kode_member
            ? data.data.customer.kode_member.slice(0, 4) + '******' + data.data.customer.kode_member.slice(-2)
            : ''
        }</div>
    
    <div class="font-semibold">Nama:</div>
    <div>${data.data.customer.nama_customer}</div>
    
    <div class="font-semibold">Member:</div>
    <div>${data.data.customer.member}</div>
  </div>
  <div class="flex gap-3 justify-end mt-2">
    <button id="openModalProfile" class="px-4 py-2 bg-pink-600 text-white rounded-md shadow hover:bg-pink-700 transition">Lengkapi Data</button>
    <button id="btn-kembali" class="px-4 py-2 bg-gray-300 rounded-md shadow hover:bg-gray-400 transition">Kembali</button>
  </div>
`
    eDiv.innerHTML = addHtml;
    modalContent.appendChild(eDiv);
    document.getElementById("btn-kembali").addEventListener("click", () => resetView(inforDiv, inforDiv1));

}

export default modalViewMemberOrNot;

