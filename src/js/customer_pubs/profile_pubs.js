import { checkBoxAddress, loadKec, loadKel, loadKota, loadProvinsi } from "../index/fetch/wilayah.js";
import { logoutUser} from "../index/fetch/logout_user.js";
import getCookie from "../index/utils/cookies.js";
import { openModal, closeModalProfile } from "../index/ui/modal_interaction.js";
import checkTerms from "../index/ui/terms_condition.js";

const token = getCookie("token");
const statusMember = document.getElementById('status');
const addressCustomer = document.getElementById('addres');
const fetchProfile = async (token) => {
    const response = await fetch("/src/api/user/profile_user_pubs", {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token
        }
    });
    const data = await response.json();
    return { data: data, status: response.status };
}
if (!token) {
    Swal.fire({
        icon: "error",
        title: "Akses Ditolak",
        text: "Silahkan Login Kembali",
        timer: 1000,
        showConfirmButton: false
    }).then(() => {
        window.location.href = "/log_in"
    })
} else {
    fetchProfile(token).then(({ data, status }) => {
        if (status === 200) {
            isiFormProfile(data.data)
            isiNumberPhone(data.data)
            loadProvinsi("provinsi");
            loadKota("kota", "kec", "kel", "provinsi");
            loadKec("kec", "kel", "kota");
            loadKel("kel", "kec");
            loadProvinsi("provinsi_domisili");
            loadKota("kota_domisili", "kecamatan_domisili", "kelurahan_domisili", "provinsi_domisili");
            loadKec("kecamatan_domisili", "kelurahan_domisili", "kota_domisili");
            loadKel("kelurahan_domisili", "kecamatan_domisili");
            statusMember.textContent = `Status : ${normalize(data.data.status_member)}`
            addressCustomer.textContent = normalize(data.data.alamat)
            document.getElementById("alamatDomisili").textContent = normalize(data.data.domisili_prov);
            document.getElementById("gender").textContent = normalize(data.data.gender);
            document.getElementById("jumlahAnak").textContent = normalize(data.data.anak);
            checkTerms();
            sendNumberPhone()
            checkBoxAddress("alamat_domisili", "provinsi_domisili", "kota_domisili", "kelurahan_domisili", "kecamatan_domisili");
        } else if (status === 400 || status === 401) {
            console.log(data.message)
        }
    })
}

const normalize = (value) => {
    return (value === null || value === 'null' || value === undefined || value === '') ? '-' : value;
}
// Misal response dari API sudah dalam variabel 'data' (lihat struktur data Anda)
const isiFormProfile = (data) => {
    const setSelectValue = (selectId, value) => {
        const select = document.getElementById(selectId);
        if (!select) return;
        const option = [...select.options].find(opt => opt.value === value);
        if (option) {
            select.value = value;
        } else if (value) {
            const newOption = document.createElement('option');
            newOption.value = value;
            newOption.textContent = value;
            newOption.selected = true;
            select.appendChild(newOption);
        }
    };
    // Input text
    document.getElementById('memberKode').value = data.phone_number ?? '';
    document.getElementById('nama_lengkap').value = data.nama_lengkap ?? '';
    document.getElementById('alamat_ktp').value = data.alamat ?? '';
    document.getElementById('alamat_domisili').value = data.domisili_alamat ?? '';
    document.getElementById('tanggal_lahir').value = data.tanggal_lahir ?? '';
    // document.getElementById('member-email').value = data.email ?? '';

    // Selects (KTP)
    setSelectValue('provinsi', data.provinsi);
    setSelectValue('kota', data.kota);
    setSelectValue('kec', data.kecamatan);
    setSelectValue('kel', data.kelurahan);

    // Selects (Domisili)
    setSelectValue('provinsi_domisili', data.domisili_prov);
    setSelectValue('kota_domisili', data.domisili_kota);
    setSelectValue('kecamatan_domisili', data.domisili_kecamatan);
    setSelectValue('kelurahan_domisili', data.domisili_kelurahan);

    // Jenis Kelamin dan Jumlah Anak
    setSelectValue('jenis_kelamin', data.gender);
    setSelectValue('jumlah_anak', data.anak);

    // Checkbox
    document.getElementById('syarat').checked = !!data.syarat_ketentuan;
}

const isiNumberPhone = (data) => {
    const numberPhoneContainer = document.getElementById('numberPhoneContainer');
    const editBtn = document.getElementById("sendEdit");
    const editNote = document.getElementById("editNote");

    if (!data.phone_number) {
        numberPhoneContainer.innerHTML = `
        <button id="btnIsiNoHp" class="text-blue-600 hover:underline">
            ➕ Masukkan No. HP
        </button>
    `;
        Swal.fire({
            icon: "info",
            title: "No Handphone Kosong",
            text: "Silahkan masukan no handphone terlebih dahulu untuk pengecekan member atau tidak",
            showConfirmButton: true,
        }).then(() => {
            showModalPhone();
        })
        // Tambahkan event untuk membuka modal/form input No HP
        document.getElementById('btnIsiNoHp').addEventListener('click', () => {
            showModalPhone();
        });
    } else {
        numberPhoneContainer.innerHTML = `
        <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center">
            <i class="fas fa-phone text-white text-sm"></i>
        </div>
        <div class="flex-1">
            <p class="text-xs text-gray-500 font-medium">No. HP</p>
            <p class="text-gray-800 font-semibold">${data.phone_number}</p>
        </div>
        `;
    }

    const openProfileModal = () => {
        openModal("modalMemberProfile", "modalContentProfile", "sendEdit");
        closeModalProfile("closeModalProfile", "modalMemberProfile", "modalContentProfile");
    };

    const disablePhoneEdit = () => {
        numberPhoneContainer.onclick = () => {
            Swal.fire({
                icon: "info",
                text: "Anda tidak bisa mengubah no telp karena sudah terhubung member. Jika ingin mengalihkan ke no lain, silakan ke store cabang Asoka Baby Store.",
                showConfirmButton: true
            });
        };
    };

    const enablePhoneEdit = () => {
        numberPhoneContainer.onclick = () => showModalPhone();
    };

    if (data.status_member === "member") {
        openProfileModal();
        disablePhoneEdit();
    } else {
        enablePhoneEdit();
        if (editBtn) editBtn.remove();
        if (editNote) editNote.classList.remove("hidden");
    }

    if(data.updated === 1 ) {
        Swal.fire({
            icon: 'info',
            title: 'Informasi',
            text: 'Harap mengisi data diri untuk mengakses semua fitur',
            showConfirmButton: true
        }).then(() => {
            editBtn.click();
        });
    }
}

// Event ketika tombol "Masukkan No. HP" diklik
const showModalPhone = () => {
    document.getElementById('modalInputNoHp').classList.remove('hidden');
}

// Tutup modal
const closeModalPhone = () => {
    document.getElementById('modalInputNoHp').classList.add('hidden');
}

document.getElementById('btnBatalNoHp').addEventListener('click', () => {
    closeModalPhone();
});


const sendNumberPhone = () => {
    const inputNoHp = document.getElementById('inputNoHp');
    const errorNoHp = document.getElementById('errorNoHp');

    function isValidNoHp(noHp) {
        return /^08\d{7,11}$/.test(noHp);
    }
    // Live check saat input berubah
    inputNoHp.addEventListener('input', () => {
        if (inputNoHp.value.trim() === '' || isValidNoHp(inputNoHp.value.trim())) {
            errorNoHp.classList.add('hidden');
        } else {
            errorNoHp.textContent = "Format 08xxx dan minimal 10 angka maks 13"
            errorNoHp.classList.remove('hidden');
        }
    });
    // Simpan No. HP
    document.getElementById('btnSimpanNoHp').addEventListener('click', () => {
        const noHp = inputNoHp.value;
        if (!isValidNoHp(noHp)) {
            errorNoHp.classList.remove('hidden');
            return;
        }

        fetch('/src/api/user/update_phone_number_pubs', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ no_hp: noHp })
        })
            .then(response => {
                const statusCode = response.status; // Ambil HTTP status
                return response.json().then(data => ({ statusCode, data })); // Gabungkan status + data
            })
            .then(({ statusCode, data }) => {
                console.log("code", statusCode)
                console.log("response", data)
                if (statusCode === 200) {
                    closeModalPhone();
                    document.getElementById('numberPhoneContainer').textContent = noHp;
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Berhasil update nomor HP!',
                        showConfirmButton: false,
                        timer: 500,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'text-sm bg-white border border-green-300 shadow-lg'
                        }
                    }).then(() => {
                        window.location.reload();
                    })
                } else {
                    errorNoHp.textContent = data.message || "Terjadi kesalahan.";
                    errorNoHp.classList.remove('hidden');
                }
            })
            .catch(err => {
                console.error("Error catch:", err);
                errorNoHp.textContent = "Terjadi kesalahan saat menyimpan";
                errorNoHp.classList.remove('hidden');
            });
    });
}

logoutUser('logout');

