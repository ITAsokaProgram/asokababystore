window.onload = function () {
  
  const token = getCookie("token");
  if (!token) {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Anda belum login. Silakan login terlebih dahulu.',
      showConfirmButton: true,
      confirmButtonText: 'Login'
    }).then(() => {
      window.location.href = "/log_in"; 
    });
    return;
  }

  
  const params = new URLSearchParams(window.location.search);
  const kd_tr = params.get("kode");
  const kd_cust = params.get("member");

  
  if (!kd_tr || !kd_cust) {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Kode transaksi dan member tidak ditemukan.',
      confirmButtonText: 'Login'
    }).then(() => {
      window.location.href = "/log_in";
    });
    return;
  }

  
  fetch(
    `src/api/customer/get_struk_belanja_customer_pubs?kode=${kd_tr}&member=${kd_cust}`
  )
    .then((response) => response.json())
    .then((data) => {
      const { detail_transaction } = data;
      if (detail_transaction.length > 0) {
        renderStruk(detail_transaction, data.total_poin);
      } else {
        document.getElementById(
          "struk-container"
        ).innerHTML = `<p class="text-center text-gray-500">Tidak ada transaksi untuk kode dan member tersebut.</p>`;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Terjadi kesalahan saat mengambil data.',
        confirmButtonText: 'Login'
      }).then(() => {
        window.location.href = "/log_in";
      });
    });
};

const btnPrint = document.getElementById("print");
btnPrint.addEventListener("click", (e) => {
  e.preventDefault();
  downloadStrukAsPDF();
});

function getCookie (name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
}

function hitungHemat(harga, hrg_promo) {
  return harga > hrg_promo ? harga - hrg_promo : 0;
}

function hitungDiskon(harga, diskon) {
  return diskon > 0 ? harga * (diskon / 100) : 0;
}

function akumulasiPoin(totalItem) {
  if (totalItem >= 100000) {
    return Math.min(Math.floor(totalItem / 100000)); 
  }
  return 0; 
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
            <span>Diskon ==></span>
            <span>(${diskon.toFixed(0)}%)</span>
            <span>${totalItem.toLocaleString("id-ID")}</span>
        </div>`
      
      
      : (adaPromo && selisihHemat !== 0) 
      ? `<div class="flex justify-between text-xs">
                <span>Hemat ==></span>
                <span>${
                    selisihHemat > 0 
                      ? '-' + selisihHemat.toLocaleString("id-ID") 
                      
                      : Math.abs(selisihHemat).toLocaleString("id-ID") 
                  }</span>
                <span>${totalItem.toLocaleString("id-ID")}</span>
            </div>`
      : ""; 
  


  return `
<div class="mb-1 leading-tight">
    <div>${i + 1}# ${d.kode_barang || ""} ${d.item}</div>
    <div class="flex justify-between pl-5">
        <span>${qty} PCS X ${harga.toLocaleString("id-ID")}</span>
        <span>${(harga * qty).toLocaleString("id-ID")}</span>
    </div>
    ${keteranganPromo}
</div>`;
}

function hideNumber(v) {
  const s = String(v ?? "");
  if(s.length > 4) {
    return s.slice(0, 4) + "****" + s.slice(-4);
  }
  return s;
}

function renderStruk(transactions, poin) {
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
      const selisihHemat = harga - hrgPromo; // <-- Hitung selisihnya
      
      let hargaAkhir = harga;

      if (diskon > 0) {
        hargaAkhir = harga - nilaiDiskon;
        totalDiskon += nilaiDiskon * qty;
      } else if (adaPromo) { 
        hargaAkhir = hrgPromo; 
        if (selisihHemat !== 0) { // <-- PERBAIKAN
        	totalPromo += selisihHemat * qty; // <-- Gunakan selisihHemat (bisa positif/negatif)
        }
      }

      total += hargaAkhir * qty;
      totalQty += qty;
      return renderItem(d, i);
    })
    .join("");


  const totalBelanja = total + totalDiskon + totalPromo;

  const html = `
<!-- Card: Logo & Identitas Toko -->
<div class="text-xs font-mono bg-white border border-gray-300 rounded shadow text-black p-5">
    <div class="flex justify-center mb-3">
        <img src="/public/images/${logo}" alt="Logo Toko" class="h-10 object-contain" />
    </div>
  <div class="flex justify-between items-start text-justify">
    <div>
      <p class="font-bold text-sm text-center">${nama_store}</p>
      <p class="text-[11px] text-center">${alamat_store}</p>
    </div>
  </div>
</div>

<!-- Card: Detail Transaksi -->
<div class="text-xs font-mono bg-white border border-gray-300 p-2 rounded shadow text-black space-y-0.5 mt-2 m-auto p-5">
    <p  id="kode_trans"><strong>No Trans</strong>: ${
      transactions[0].kode_transaksi
    } ${kasir}</p>
    <p><strong>Kode Cust</strong>: ${hideNumber(member)}</p>
    <p><strong>Nama Cust</strong>: ${pelanggan}</p>
    <p>${tanggal} ${jam_trs}</p>
</div>

<!-- Card: Items -->
<div class="text-xs font-mono bg-white border border-gray-300 p-2 rounded shadow text-black mt-2 m-auto p-7">
    ${rows}
    <hr class="my-1 border-gray-400" />
    <div class="grid grid-cols-3 gap-x-2 space-y-1">
  <div>Total Qty</div>
  <div class="text-right"></div>
  <div class="text-right">${totalQty}</div>

  <div>Total Belanja</div>
  <div class="text-right">Rp.</div>
  <div class="text-right">${transactions[0].belanja.toLocaleString("id-ID")}</div>

  <div>Nilai Hemat</div>
  <div class="text-right">Rp.</div>
  <div class="text-right">${totalPromo.toLocaleString("id-ID")}</div>

  <div>Diskon Item</div>
  <div class="text-right">Rp.</div>
  <div class="text-right">${totalDiskon.toLocaleString("id-ID")}</div>
  
  <div class="font-bold pt-1">Sub Total</div>
  <div class="text-right font-bold pt-1">Rp.</div>
  <div class="text-right font-bold pt-1">${total.toLocaleString("id-ID")}</div>
</div>
</div>

<!-- Card: Pembayaran -->
<div class="text-xs font-mono bg-white border border-gray-300 p-2 rounded shadow text-black mt-2 m-auto p-5">
   ${pembayaran({ cash, credit1, voucher1, kembalian, no_kredit1, nm_kartu })}
</div>

<!-- Card: Footer Terima Kasih -->
<div class="text-xs font-mono bg-white border border-gray-300 p-2 rounded shadow text-black text-center mt-2">
    <p class="uppercase font-bold">TERIMAKASIH TELAH BERBELANJA DI</p>
    <p>ASOKA BABY STORE</p>
    <p>Kami tunggu kedatangan Anda kembali</p>
    <p>ASOKA CARE 08 1717 1212 50</p>
    <p class="mt-1">www.asokababystore.com</p>
</div>
`;
  const print = `<div class="text-xs font-mono text-black p-2 w-full max-w-[230px]">
  <div class="text-center mb-1">
    <img src="/public/images/${logo}" alt="Logo Toko" class="h-12 mx-auto object-contain mb-1" />
    <p class="font-bold text-sm leading-tight">${nama_store}</p>
    <p class="text-[11px] leading-tight whitespace-pre-line">${alamat_store}</p>
  </div>

  <div class="mt-2 leading-tight text-[11px]">
    <p><strong>No Trans</strong>: ${transactions[0].kode_transaksi} ${kasir}</p>
    <p><strong>Kode Cust</strong>: ${member}</p>
    <p><strong>Nama Cust</strong>: ${pelanggan}</p>
    <p>${tanggal} ${jam_trs}</p>
  </div>

  <div class="mt-2 leading-tight text-[11px]">
    ${rows}
    <hr class="my-1 border-gray-400" />
    <div>
      <div class="flex justify-between"><span>Total Qty</span><span>${totalQty}</span></div>
      <div class="flex justify-between"><span>Total Belanja</span><span>Rp. ${totalBelanja.toLocaleString(
        "id-ID"
      )}</span></div>
      <div class="flex justify-between"><span>Diskon Item</span><span>Rp. ${totalDiskon.toLocaleString(
        "id-ID"
      )}</span></div>
      <div class="flex justify-between"><span>Nilai Hemat</span><span>Rp. ${totalPromo.toLocaleString(
        "id-ID"
      )}</span></div>
      <div class="flex justify-between font-bold pt-1"><span>Sub Total</span><span>Rp. ${total.toLocaleString(
        "id-ID"
      )}</span></div>
    </div>
  </div>

  <div class="mt-2 leading-tight text-[11px]">
    <p class="font-bold text-center">PEMBAYARAN</p>
    ${
      cash > 0
        ? `
        <div class="flex justify-between"><span>Tunai:</span><span>Rp. ${cash.toLocaleString(
          "id-ID"
        )}</span></div>
        <div class="flex justify-between"><span>Voucher:</span><span>Rp. ${voucher1.toLocaleString(
          "id-ID"
        )}</span></div>
        <div class="flex justify-between"><span>Voucher:</span><span>--------------- ( - )</span></div>

        <div class="flex justify-between"><span>Kembali:</span><span>Rp. ${kembalian.toLocaleString(
          "id-ID"
        )} </span></div>
      `
        : `
        <div class="flex justify-between"><span>Kartu:</span><span>${no_kredit1.replace(
          /[a-zA-Z]/g,
          "*"
        )}</span></div>
        <div class="flex justify-between"><span>${nm_kartu}</span><span>Rp. ${credit1.toLocaleString(
            "id-ID"
          )}</span></div>
      `
    }

  </div>

  <div class="text-center mt-2 leading-tight text-[11px]">
    <p class="uppercase font-bold">TERIMAKASIH TELAH BERBELANJA DI</p>
    <p>ASOKA BABY STORE</p>
    <p>Kami tunggu kedatangan Anda kembali</p>
    <p>ASOKA CARE 08 1717 1212 50</p>
    <p class="mt-1">www.asokababystore.com</p>
  </div>
</div>`;

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
  let html = '<div class="grid grid-cols-3 gap-x-2">';

  if (cash > 0) {
    html += `
      <div>Tunai</div>
      <div class="text-right">Rp.</div>
      <div class="text-right">${cash.toLocaleString("id-ID")}</div>
    `;
  }

  if (credit1 > 0) {
    html += `
      <div>Kartu</div>
      <div class="text-right col-span-2"></div>
      
      <div class="text-left col-span-2">${no_kredit1.replace(
        /[a-zA-Z`]/g,
        "*"
      )}</div>

      <div></div>
      <div>${nm_kartu}</div>

      <div class="text-right">Rp.</div>
      <div class="text-right">${credit1.toLocaleString("id-ID")}</div>
    `;
  }

  if (voucher1 > 0) {
    html += `
      <div>Voucher</div>
      <div class="text-right">Rp.</div>
      <div class="text-right">${voucher1.toLocaleString("id-ID")}</div>
    `;
  }


  html += `
    <div></div>
    <div class="col-span-2 text-right"></div>

    <div>Kembalian</div>
    <div class="text-right">Rp.</div>
    <div class="text-right">${kembalian.toLocaleString("id-ID")}</div>
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
    html2canvas(pdfContainer, {
      scale: 2,
      width: 360,
      useCORS: true,
    })
      .then((canvas) => {
        const imgData = canvas.toDataURL("image/png");

        const imgWidth = 95; 
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        
        const doc = new jsPDF({
          orientation: "portrait",
          unit: "mm",
          format: [210, imgHeight + 20],
        });

        const x = (210 - imgWidth) / 2;
        const y = 10;

        doc.addImage(imgData, "PNG", x, y, imgWidth, imgHeight);
        doc.save(`ASOKA-${kode}.pdf`);

        pdfContainer.innerHTML = "";
      })
      .catch((err) => {
        console.error("html2canvas error:", err);
      });
  }, 100);
}
