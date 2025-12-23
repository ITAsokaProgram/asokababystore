import getCookie from "../index/utils/cookies.js";
window.onload = function () {
  const token = getCookie("admin_token");
  if (!token) {
    Swal.fire({
      icon: "error",
      title: "Akses Ditolak",
      text: "Anda harus login sebagai Admin.",
      showConfirmButton: true,
    }).then(() => {
      window.close();
    });
    return;
  }
  const params = new URLSearchParams(window.location.search);
  const kd_tr = params.get("kode");
  if (!kd_tr) {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "Kode transaksi tidak ditemukan.",
    });
    return;
  }
  fetch(`/src/api/customer/get_struk_by_admin.php?kode=${kd_tr}`, {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
  })
    .then((response) => response.json())
    .then((data) => {
      const { detail_transaction } = data;
      if (detail_transaction && detail_transaction.length > 0) {
        renderStruk(detail_transaction);
      } else {
        document.getElementById(
          "struk-container"
        ).innerHTML = `<p class="text-center text-gray-500 mt-10">Transaksi ${kd_tr} tidak ditemukan.</p>`;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Terjadi kesalahan sistem.",
      });
    });
};
const btnPrint = document.getElementById("print");
if (btnPrint) {
  btnPrint.addEventListener("click", (e) => {
    e.preventDefault();
    downloadStrukAsPDF();
  });
}
function hitungDiskon(harga, diskon) {
  return diskon > 0 ? harga * (diskon / 100) : 0;
}
function renderItem(d, i) {
  const harga = Number(d.harga);
  const qty = Number(d.qty);
  const diskon = Number(d.diskon || 0);
  const hrgPromo = Number(d.hrg_promo || 0);
  const nilaiDiskon = hitungDiskon(harga, diskon);
  const adaPromo = hrgPromo > 0;
  const selisihHemat = harga - hrgPromo;
  let hargaSetelahDiskon = harga;
  if (diskon > 0) {
    hargaSetelahDiskon = harga - nilaiDiskon;
  } else if (adaPromo) {
    hargaSetelahDiskon = hrgPromo;
  }
  const totalItem = hargaSetelahDiskon * qty;
  const keteranganPromo =
    diskon > 0
      ? `<div class="flex justify-between text-xs">
            <span>Diskon (${diskon.toFixed(0)}%)</span>
            <span>${totalItem.toLocaleString("id-ID")}</span>
        </div>`
      : adaPromo && selisihHemat !== 0
      ? `<div class="flex justify-between text-xs">
              <span>Hemat</span>
              <span>${totalItem.toLocaleString("id-ID")}</span>
            </div>`
      : "";
  return `
    <div class="mb-1 leading-tight">
        <div>${i + 1}# ${d.item}</div>
        <div class="flex justify-between pl-5">
            <span>${qty} x ${harga.toLocaleString("id-ID")}</span>
            <span>${(harga * qty).toLocaleString("id-ID")}</span>
        </div>
        ${keteranganPromo}
    </div>`;
}
function hideNumber(v) {
  const s = String(v ?? "");
  if (s.length > 4) {
    return s.slice(0, 4) + "****" + s.slice(-4);
  }
  return s;
}
function renderStruk(transactions) {
  const {
    tanggal,
    jam_trs,
    kasir,
    pelanggan,
    member,
    alamat_store,
    nama_store,
    logo,
    cash,
    nm_kartu,
    kembalian,
    voucher1,
    credit1,
    no_kredit1,
  } = transactions[0];
  let total = 0;
  let totalQty = 0;
  let totalDiskon = 0;
  let totalPromo = 0;
  const rows = transactions
    .map((d, i) => {
      const harga = Number(d.harga);
      const qty = Number(d.qty);
      const diskon = Number(d.diskon || 0);
      const hrgPromo = Number(d.hrg_promo || 0);
      const nilaiDiskon = hitungDiskon(harga, diskon);
      const adaPromo = hrgPromo > 0;
      const selisihHemat = harga - hrgPromo;
      let hargaAkhir = harga;
      if (diskon > 0) {
        hargaAkhir = harga - nilaiDiskon;
        totalDiskon += nilaiDiskon * qty;
      } else if (adaPromo) {
        hargaAkhir = hrgPromo;
        if (selisihHemat !== 0) {
          totalPromo += selisihHemat * qty;
        }
      }
      total += hargaAkhir * qty;
      totalQty += qty;
      return renderItem(d, i);
    })
    .join("");
  const html = `
    <div class="text-xs font-mono bg-white border border-gray-300 rounded shadow text-black p-5">
        <div class="flex justify-center mb-3">
            <img src="/public/images/${logo}" alt="Logo Toko" class="h-10 object-contain" />
        </div>
      <div class="text-center">
          <p class="font-bold text-sm">${nama_store}</p>
          <p class="text-[11px]">${alamat_store}</p>
      </div>
    </div>
    <div class="text-xs font-mono bg-white border border-gray-300 p-2 rounded shadow text-black space-y-0.5 mt-2 m-auto p-5">
        <p id="kode_trans"><strong>No Trans</strong>: ${
          transactions[0].kode_transaksi
        } ${kasir}</p>
        <p><strong>Kode Cust</strong>: ${member}</p> 
        <p><strong>Nama Cust</strong>: ${pelanggan}</p>
        <p>${tanggal} ${jam_trs}</p>
    </div>
    <div class="text-xs font-mono bg-white border border-gray-300 p-2 rounded shadow text-black mt-2 m-auto p-7">
        ${rows}
        <hr class="my-1 border-gray-400" />
        <div class="grid grid-cols-3 gap-x-2 space-y-1">
            <div>Total Qty</div><div class="text-right"></div><div class="text-right">${totalQty}</div>
            <div>Nilai Hemat</div><div class="text-right">Rp.</div><div class="text-right">${totalPromo.toLocaleString(
              "id-ID"
            )}</div>
            <div>Diskon Item</div><div class="text-right">Rp.</div><div class="text-right">${totalDiskon.toLocaleString(
              "id-ID"
            )}</div>
            <div class="font-bold pt-1">Sub Total</div>
            <div class="text-right font-bold pt-1">Rp.</div>
            <div class="text-right font-bold pt-1">${total.toLocaleString(
              "id-ID"
            )}</div>
        </div>
    </div>
    <div class="text-xs font-mono bg-white border border-gray-300 p-2 rounded shadow text-black mt-2 m-auto p-5">
       ${pembayaran({
         cash,
         credit1,
         voucher1,
         kembalian,
         no_kredit1,
         nm_kartu,
       })}
    </div>
    <div class="text-xs font-mono bg-white border border-gray-300 p-2 rounded shadow text-black text-center mt-2">
        <p class="uppercase font-bold">TERIMAKASIH</p>
        <p>ASOKA BABY STORE</p>
    </div>
    `;
  document.getElementById("struk-container").innerHTML = html;
}
const pembayaran = ({
  cash,
  credit1,
  voucher1,
  kembalian,
  no_kredit1,
  nm_kartu,
}) => {
  const nCash = Number(cash) || 0;
  const nCredit = Number(credit1) || 0;
  const nVoucher = Number(voucher1) || 0;
  const nKembalian = Number(kembalian) || 0;
  let html = '<div class="grid grid-cols-3 gap-x-2">';
  if (nCash > 0) {
    html += `<div>Tunai</div><div class="text-right">Rp.</div><div class="text-right">${nCash.toLocaleString(
      "id-ID"
    )}</div>`;
  }
  if (nCredit > 0) {
    html += `<div>Kartu</div><div class="text-right col-span-2"></div>
             <div class="text-left col-span-2">${
               no_kredit1 ? no_kredit1.replace(/[a-zA-Z`]/g, "*") : ""
             }</div>
             <div></div><div>${nm_kartu || "-"}</div>
             <div class="text-right">Rp.</div><div class="text-right">${nCredit.toLocaleString(
               "id-ID"
             )}</div>`;
  }
  if (nVoucher > 0) {
    html += `<div>Voucher</div><div class="text-right">Rp.</div><div class="text-right">${nVoucher.toLocaleString(
      "id-ID"
    )}</div>`;
  }
  html += `<div></div><div class="col-span-2 text-right"></div>
           <div>Kembalian</div><div class="text-right">Rp.</div><div class="text-right">${nKembalian.toLocaleString(
             "id-ID"
           )}</div>
         </div>`;
  return html;
};
function downloadStrukAsPDF() {
  const { jsPDF } = window.jspdf;
  const kode = document.getElementById("kode_trans").textContent.trim();
  const strukHTML = document.getElementById("struk-container");
  const pdfContainer = document.getElementById("pdf-container");
  pdfContainer.innerHTML = strukHTML.innerHTML;
  pdfContainer.className = "w-[360px] text-sm font-mono";
  setTimeout(() => {
    html2canvas(pdfContainer, { scale: 2, width: 360, useCORS: true }).then(
      (canvas) => {
        const imgData = canvas.toDataURL("image/png");
        const imgWidth = 95;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        const doc = new jsPDF({
          orientation: "portrait",
          unit: "mm",
          format: [210, imgHeight + 20],
        });
        const x = (210 - imgWidth) / 2;
        doc.addImage(imgData, "PNG", x, 10, imgWidth, imgHeight);
        doc.save(`STRUK-${kode}.pdf`);
        pdfContainer.innerHTML = "";
      }
    );
  }, 100);
}
