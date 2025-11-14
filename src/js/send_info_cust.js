function validateNIK() {
  const kodeNik = document.getElementById("no_nik").value;
  const nikError = document.getElementById("nik-error");
  const submitButton = document.getElementById("send_data");

  // Jika NIK kosong, sembunyikan error dan aktifkan tombol (karena opsional)
  if (kodeNik === "") {
    nikError.classList.add("hidden");
    submitButton.disabled = false;
    return;
  }

  // Jika NIK diisi, validasi formatnya
  if (!isValidFormatNIK(kodeNik)) {
    nikError.classList.remove("hidden");
    submitButton.disabled = true; // Nonaktifkan tombol jika format salah
  } else {
    nikError.classList.add("hidden");
    submitButton.disabled = false; // Aktifkan tombol jika format benar
  }
}

function isValidFormatNIK(nik) {
  const nikRegex = /^[1-9][0-9]{15}$/;
  return nikRegex.test(nik);
}

async function sendCust() {
  const formData = new FormData(document.getElementById("data_cust"));
  const alamatKtp = document.getElementById("alamat_ktp").value;
  const selectProv = document.getElementById("provinsi");
  const selectKota = document.getElementById("kota");
  const selectKec = document.getElementById("kec");
  const selectKel = document.getElementById("kel");
  const namaProv = selectProv.options[selectProv.selectedIndex].textContent;
  const namaKota = selectKota.options[selectKota.selectedIndex].textContent;
  const namaKec = selectKec.options[selectKec.selectedIndex].textContent;
  const namaKel = selectKel.options[selectKel.selectedIndex].textContent;

  const alamatDom = document.getElementById("alamat_domisili").value;
  const selectProvDom = document.getElementById("provinsi_domisili");
  const selectKotaDom = document.getElementById("kota_domisili");
  const selectKecDom = document.getElementById("kecamatan_domisili");
  const selectKelDom = document.getElementById("kelurahan_domisili");

  const namaProvDom =
    selectProvDom.options[selectProvDom.selectedIndex].textContent;
  const namaKotaDom =
    selectKotaDom.options[selectKotaDom.selectedIndex].textContent;
  const namaKecDom =
    selectKecDom.options[selectKecDom.selectedIndex].textContent;
  const namaKelDom =
    selectKelDom.options[selectKelDom.selectedIndex].textContent;

  // Ambil nilai NIK untuk divalidasi
  const kodeNik = document.getElementById("no_nik").value;

  formData.append("alamat_ktp", alamatKtp);
  formData.append("alamat_domisili", alamatDom);
  formData.set("provinsi", namaProv);
  formData.set("kota", namaKota);
  formData.set("kec", namaKec);
  formData.set("kel", namaKel);
  formData.set("provinsi_domisili", namaProvDom);
  formData.set("kota_domisili", namaKotaDom);
  formData.set("kec_domisili", namaKecDom);
  formData.set("kel_domisili", namaKelDom);

  // Pengecekan NIK sebelum kirim:
  // Hanya validasi jika diisi (tidak kosong)
  if (kodeNik !== "" && !isValidFormatNIK(kodeNik)) {
    return Swal.fire({
      title: "NIK Tidak Valid",
      text: "Pastikan NIK terdiri dari 16 digit angka dan tidak dimulai dengan 0. Kosongkan jika tidak ingin mengisi.",
      icon: "error",
    });
  }

  fetch("/src/api/customer/update_customer.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => {
      if (!res.ok) throw new Error(`HTTP error: ${res.status}`);
      return res.json(); // langsung parse JSON
    })
    .then((data) => {
      if (data.success) {
        Swal.fire({
          title: "Berhasil Update Profile",
          icon: "success",
        }).then(() => location.reload());
      } else {
        Swal.fire({
          title: "Gagal Update Profile",
          text: data.message || "Terjadi kesalahan",
          icon: "error",
        });
      }
    })
    .catch((err) => {
      Swal.fire({
        title: "Error",
        text: err.message,
        icon: "error",
      });
    });
}

document.getElementById("send_data").addEventListener("click", (e) => {
  e.preventDefault();
  const form = document.getElementById("data_cust");
  if (!form.checkValidity()) {
    form.reportValidity(); // munculin pesan error default browser
    return; // hentikan kalau tidak valid
  }
  sendCust();
});
