import {
  checkBoxAddress,
  loadKec,
  loadKel,
  loadKota,
  loadProvinsi,
} from "../index/fetch/wilayah.js";
import { logoutUser } from "../index/fetch/logout_user.js";
import getCookie from "../index/utils/cookies.js";
import { openModal, closeModalProfile } from "../index/ui/modal_interaction.js";
import checkTerms from "../index/ui/terms_condition.js";
const token = getCookie("customer_token");
const statusMember = document.getElementById("status");
const addressCustomer = document.getElementById("addres");
const fetchProfile = async (token) => {
  const response = await fetch("/src/api/user/profile_user_pubs", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Authorization: "Bearer " + token,
    },
  });
  const data = await response.json();
  return { data: data, status: response.status };
};
if (!token) {
  Swal.fire({
    icon: "error",
    title: "Akses Ditolak",
    text: "Silahkan Login Kembali",
    timer: 1000,
    showConfirmButton: false,
  }).then(() => {
    window.location.href = "/log_in";
  });
} else {
  fetchProfile(token).then(({ data, status }) => {
    if (status === 200) {
      isiFormProfile(data.data);
      isiNumberPhone(data.data);
      loadProvinsi("provinsi");
      loadKota("kota", "kec", "kel", "provinsi");
      loadKec("kec", "kel", "kota");
      loadKel("kel", "kec");
      loadProvinsi("provinsi_domisili");
      loadKota(
        "kota_domisili",
        "kecamatan_domisili",
        "kelurahan_domisili",
        "provinsi_domisili"
      );
      loadKec("kecamatan_domisili", "kelurahan_domisili", "kota_domisili");
      loadKel("kelurahan_domisili", "kecamatan_domisili");
      statusMember.textContent = `Status : ${normalize(
        data.data.status_member
      )}`;
      addressCustomer.textContent = normalize(data.data.alamat);
      document.getElementById("alamatDomisili").textContent = normalize(
        data.data.domisili_prov
      );

      document.getElementById("gender").textContent = normalize(
        data.data.gender == "0" ? "-" : data.data.gender
      );
      document.getElementById("jumlahAnak").textContent = normalize(
        data.data.anak
      );
      checkTerms();
      sendNumberPhone();
      checkBoxAddress(
        "alamat_domisili",
        "provinsi_domisili",
        "kota_domisili",
        "kelurahan_domisili",
        "kecamatan_domisili"
      );
    } else if (status === 400 || status === 401) {
      console.log(data.message);
    }
  });
}
const normalize = (value) => {
  return value === null ||
    value === "null" ||
    value === undefined ||
    value === ""
    ? "-"
    : value;
};
const isiFormProfile = (data) => {
  const setSelectValue = (selectId, value) => {
    const select = document.getElementById(selectId);
    if (!select) return;

    if (value === "0" || value === 0) return;

    const option = [...select.options].find((opt) => opt.value === value);

    if (option) {
      select.value = value;
    } else if (value) {
      const newOption = document.createElement("option");
      newOption.value = value;
      newOption.textContent = value;
      newOption.selected = true;
      select.appendChild(newOption);
    }
  };
  document.getElementById("memberKode").value = data.phone_number ?? "";
  document.getElementById("nama_lengkap").value = data.nama_lengkap ?? "";
  document.getElementById("alamat_ktp").value = data.alamat ?? "";
  document.getElementById("alamat_domisili").value = data.domisili_alamat ?? "";
  document.getElementById("no_nik").value = data.nik ?? "";
  document.getElementById("tanggal_lahir").value = data.tanggal_lahir ?? "";
  setSelectValue("provinsi", data.provinsi);
  setSelectValue("kota", data.kota);
  setSelectValue("kec", data.kecamatan);
  setSelectValue("kel", data.kelurahan);
  setSelectValue("provinsi_domisili", data.domisili_prov);
  setSelectValue("kota_domisili", data.domisili_kota);
  setSelectValue("kecamatan_domisili", data.domisili_kecamatan);
  setSelectValue("kelurahan_domisili", data.domisili_kelurahan);
  setSelectValue("jenis_kelamin", data.gender);
  setSelectValue("jumlah_anak", data.anak);
  document.getElementById("syarat").checked = !!data.syarat_ketentuan;
};

const isiNumberPhone = (data) => {
  // 1. Ambil elemen dari DOM
  const numberPhoneContainer = document.getElementById("numberPhoneContainer");
  const phoneValueText = document.getElementById("phoneValueText");
  const phoneActionIcon = document.getElementById("phoneActionIcon");
  
  const editBtn = document.getElementById("sendEdit");
  const editNote = document.getElementById("editNote");

  numberPhoneContainer.onclick = null;

  if (!data.phone_number) {
    
    phoneValueText.innerHTML = `<span class="text-blue-600 font-bold">Masukkan No. HP</span>`;
    phoneActionIcon.className = "fas fa-exclamation-circle text-blue-500 animate-pulse";

    const actionInputHp = () => {
      Swal.fire({
        icon: "info",
        title: "No Handphone Kosong",
        text: "Silahkan masukan no handphone terlebih dahulu untuk pengecekan member.",
        showConfirmButton: true,
      }).then(() => {
        showModalPhone();
      });
    };

    numberPhoneContainer.onclick = actionInputHp;


  } else {
    phoneValueText.textContent = data.phone_number;
    phoneValueText.classList.remove("text-blue-600", "font-bold"); 
    
    if (data.status_member === "member") {
      phoneActionIcon.className = "fas fa-lock text-gray-400"; 
      
      numberPhoneContainer.onclick = () => {
        Swal.fire({
          icon: "info",
          title: "Nomor Terkunci",
          text: "Nomor HP tidak dapat diubah karena sudah terdaftar sebagai Member. Silakan hubungi store cabang Asoka Baby Store untuk perubahan data.",
          showConfirmButton: true,
        });
      };
      
      if (editNote) editNote.classList.add("hidden");

    } else {
      phoneActionIcon.className = "fas fa-pen text-green-500"; 
      
      numberPhoneContainer.onclick = () => showModalPhone();
      
      if (editBtn) editBtn.remove();
      if (editNote) editNote.classList.remove("hidden");
    }
  }

  const openProfileModal = () => {
    if (document.getElementById("sendEdit")) {
      document.getElementById("sendEdit").addEventListener("click", () => {
        openModal("modalMemberProfile", "modalContentProfile", "sendEdit");
        closeModalProfile(
          "closeModalProfile",
          "modalMemberProfile",
          "modalContentProfile"
        );
      });
    }
  };
  openProfileModal();

  if (data.updated === 1 && document.getElementById("sendEdit")) {
    Swal.fire({
      icon: "info",
      title: "Lengkapi Data",
      text: "Harap mengisi data diri untuk mengakses semua fitur",
      showConfirmButton: true,
      allowOutsideClick: false,
    }).then(() => {
      const btn = document.getElementById("sendEdit");
      if (btn) btn.click();
    });
  }
};
const showModalPhone = () => {
  document.getElementById("modalInputNoHp").classList.remove("hidden");
};
const closeModalPhone = () => {
  document.getElementById("modalInputNoHp").classList.add("hidden");
};
document.getElementById("btnBatalNoHp").addEventListener("click", () => {
  closeModalPhone();
});
const sendNumberPhone = () => {
  const inputNoHp = document.getElementById("inputNoHp");
  const errorNoHp = document.getElementById("errorNoHp");
  function isValidNoHp(noHp) {
    return /^08\d{7,11}$/.test(noHp);
  }
  inputNoHp.addEventListener("input", () => {
    if (inputNoHp.value.trim() === "" || isValidNoHp(inputNoHp.value.trim())) {
      errorNoHp.classList.add("hidden");
    } else {
      errorNoHp.textContent = "Format 08xxx dan minimal 10 angka maks 13";
      errorNoHp.classList.remove("hidden");
    }
  });
  document.getElementById("btnSimpanNoHp").addEventListener("click", () => {
    const noHp = inputNoHp.value;
    if (!isValidNoHp(noHp)) {
      errorNoHp.classList.remove("hidden");
      return;
    }
    fetch("/src/api/user/update_phone_number_pubs", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ no_hp: noHp }),
    })
      .then((response) => {
        const statusCode = response.status;
        return response.json().then((data) => ({ statusCode, data }));
      })
      .then(({ statusCode, data }) => {
        if (statusCode === 200) {
          closeModalPhone();
          document.getElementById("numberPhoneContainer").textContent = noHp;
          Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: "Berhasil update nomor HP!",
            showConfirmButton: false,
            timer: 500,
            timerProgressBar: true,
            customClass: {
              popup: "text-sm bg-white border border-green-300 shadow-lg",
            },
          }).then(() => {
            window.location.reload();
          });
        } else {
          errorNoHp.textContent = data.message || "Terjadi kesalahan.";
          errorNoHp.classList.remove("hidden");
        }
      })
      .catch((err) => {
        console.error("Error catch:", err);
        errorNoHp.textContent = "Terjadi kesalahan saat menyimpan";
        errorNoHp.classList.remove("hidden");
      });
  });
};
logoutUser("logout");
