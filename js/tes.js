document.getElementById("toggle-sidebar").addEventListener("click", function () {
  document.getElementById("sidebar").classList.toggle("open");
});

document.getElementById("toggle-hide").addEventListener("click", function () {
  var sidebarTexts = document.querySelectorAll(".sidebar-text");
  let mainContent = document.getElementById("main-content");
  let sidebar = document.getElementById("sidebar");
  var toggleButton = document.getElementById("toggle-hide");
  var icon = toggleButton.querySelector("i");

  if (sidebar.classList.contains("w-64")) {
    // Sidebar mengecil
    sidebar.classList.remove("w-64", "px-5");
    sidebar.classList.add("w-16", "px-2");
    sidebarTexts.forEach((text) => text.classList.add("hidden")); // Sembunyikan teks
    mainContent.classList.remove("ml-64");
    mainContent.classList.add("ml-16"); // Main ikut mundur
    toggleButton.classList.add("left-20"); // Geser tombol lebih dekat
    toggleButton.classList.remove("left-64");
    icon.classList.remove("fa-angle-left"); // Ubah ikon
    icon.classList.add("fa-angle-right");
  } else {
    // Sidebar membesar
    sidebar.classList.remove("w-16", "px-2");
    sidebar.classList.add("w-64", "px-5");
    sidebarTexts.forEach((text) => text.classList.remove("hidden")); // Tampilkan teks kembali
    mainContent.classList.remove("ml-16");
    mainContent.classList.add("ml-64");
    toggleButton.classList.add("left-64"); // Geser tombol ke posisi awal
    toggleButton.classList.remove("left-20");
    icon.classList.remove("fa-angle-right"); // Ubah ikon
    icon.classList.add("fa-angle-left");
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const profileImg = document.getElementById("profile-img");
  const profileCard = document.getElementById("profile-card");

  profileImg.addEventListener("click", function (event) {
    event.preventDefault();
    profileCard.classList.toggle("show");
  });

  // Tutup profile-card jika klik di luar
  document.addEventListener("click", function (event) {
    if (
      !profileCard.contains(event.target) &&
      !profileImg.contains(event.target)
    ) {
      profileCard.classList.remove("show");
    }
  });
});

$(document).ready(function () {
  // Daftar kode store sesuai dengan cabang
  var storeCodes = {
    ABIN: "1502",
    ACE: "1505",
    ACIB: "1379",
    ACIL: "1504",
    ACIN: "1641",
    ACSA: "1902",
    ADET: "1376",
    ADMB: "3190",
    AHA: "1506",
    AHIN: "2102",
    ALANG: "1503",
    ANGIN: "2102",
    APEN: "1908",
    APIK: "3191",
    APRS: "1501",
    ARAW: "1378",
    ARUNG: "1611",
    ASIH: "2104",
    ATIN: "1642",
    AWIT: "1377",
    AXY: "2103",
  };

  // Event listener ketika cabang berubah
  $("#cabang").on("change", function () {
    var selectedBranch = $(this).val(); // Ambil nilai cabang yang dipilih
    var storeCode = storeCodes[selectedBranch] || "1501"; // Ambil kode store atau default '1501'
    $("#kd_store").val(storeCode); // Set nilai input kode_store
  });

  // Trigger event saat halaman dimuat untuk mengisi nilai awal
  $("#cabang").trigger("change");
});

document.addEventListener("DOMContentLoaded", function () {
  flatpickr("#date", {
    dateFormat: "d-m-Y",
    allowInput: true,
  });

  flatpickr("#date1", {
    dateFormat: "d-m-Y",
    allowInput: true,
  });
});


// Select Nomor Ratio 
$(document).ready(() => {
  const container = $("#select-supp");

  $("#ratio_number").on("change", function () {
    let selectedValue = parseInt($(this).val());

    // Bersihkan elemen lama & destroy select2
    $(".supplier-select").select2("destroy").remove();
    container.empty();

    if (!isNaN(selectedValue) && selectedValue > 0) {
      for (let i = 1; i <= selectedValue; i++) {
        const wrapper = $("<div>").addClass("flex flex-col w-full");
        const label = $("<label>")
          .addClass("text-gray-700 font-medium")
          .text("Supplier " + i + ":");
        const select = $("<select>")
          .addClass("w-full p-2 border rounded supplier-select")
          .attr("name", "supplier_" + i)
          .attr("id", "supplier_" + i); // ID unik agar tidak bentrok

        wrapper.append(label).append(select);
        container.append(wrapper);
      }

      // Aktifkan Select2 pada semua select baru
      $(".supplier-select").select2({
        width: '100%',
        ajax: {
          url: "https://asokababystore.com/get_kode_supp.php",
          type: "GET",
          dataType: "json",
          delay: 250, 
          data: (params) => ({
            search: params.term || '',
          }),
          processResults: (data) => {
            return {
              results: data.map(item => ({
                id: item.kode_supp,
                text: item.kode_supp
              }))
            }
          },
          cache: true
        },
        minimumInputLength: 0, 
        placeholder: "Pilih Supplier",
        allowClear: true
      });

      // Event listener: Saat Select2 berubah, update input #kode_supp
      $(".supplier-select").on("select2:select", function (e) {
        let selectedValue = e.params.data.id; // Ambil nilai yang dipilih
        $("#kode_supp").val(selectedValue); // Set ke input dengan id kode_supp
      });
    }
  });
});



document.getElementById("laporanForm").addEventListener("submit", function (e) {
  e.preventDefault(); // Mencegah form dikirim secara default
  let formData = new FormData();
  formData.append("ajax", true);
  formData.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formData.append("kode_supp",document.querySelector("#kode_supp")?.value);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);
  $.ajax({
    url: "https://asokababystore.com/tes.php?ajax=1",
    method: "POST",
    dataType: "json",
    processData: false,
    contentType: false,
    data: formData ,
    success: (data)=>{
    },
    error: (xhr, status, error)=>{
      console.log("Error : ", status, error);
    }
  })
  
});

