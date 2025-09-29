// Alert Notification
function alertToast(icon, message) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })

    Toast.fire({
        icon: icon,
        title: message
    })
}
// END CODE

// POST Data 
export function getDataToMember(kode) {
    showProgressBar();
    fetch("/src/api/customer/get_member", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            "kode-member": kode
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data && data.message === 'Data berhasil didapatkan') {
                completeProgressBar();
                loadProvinsi();
                loadKota();
                loadKec();
                loadKel();
                loadProvinsiDomisili();
                loadKotaDomisili();
                loadKecDomisili();
                loadKelDomisili();
                viewPoinMember(data, data.data.customer.update_profile)
                // historyTrans(data,data.data.customer.update_profile);
                getPersonalInfo("memberKode", data.data.customer.kode_member, data.data.customer.nama_customer, "nama_lengkap");
                getAddress("alamat_ktp", data.data.customer.alamat, "provinsi", data.data.customer.provinsi, "kota", data.data.customer.kota, "kec", data.data.customer.kecamatan, "kel", data.data.customer.kelurahan)
                getAddress("alamat_domisili", data.data.customer.alamat_domisili, "provinsi_domisili", data.data.customer.provinsi_domisili, "kota_domisili", data.data.customer.kota_domisili, "kecamatan_domisili", data.data.customer.kecamatan_domisili, "kelurahan_domisili", data.data.customer.kelurahan_domisili)
                openModal("modalMemberProfile", "modalContentProfile", "openModalProfile");
                openModal("modalMemberProfile", "modalContentProfile", "openModalProfile1");

            } else {
                completeProgressBar();
                alertToast("error", "Tidak Ada Data")
            }
        })
        .catch(error => {
            completeProgressBar();
            console.log("Error", error)
        })
}

function getDataFromGuest(kode) {
    showProgressBar()
    fetch("/src/api/customer/get_member_guest_pubs", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            "kode-member": kode
        })
    }).then(res => res.json())
        .then(data => {
            if (data && data.message === 'Data berhasil didapatkan') {
                completeProgressBar();
                modalViewMemberOrNot(data, data.data.customer.update_profile);
            } else {
                completeProgressBar();
                alertToast("error", "Tidak Ada Data")
            }
        })
        .catch(error => {
            completeProgressBar();
            console.log("Error", error)
        })
}
// END CODE 

// Fetch Personal Info
function getPersonalInfo(inputId, kode, name, nameId) {
    document.getElementById(inputId).value = kode
    document.getElementById(nameId).value = name
}

function getAddress(Jl, JlValue, Prov, ProvValue, Kota, KotaValue, Kec, KecValue, Kel, KelValue) {
    document.getElementById(Jl).value = JlValue;
    const provSelect = document.getElementById(Prov);
    const kotaSelect = document.getElementById(Kota);
    const kecSelect = document.getElementById(Kec);
    const kelSelect = document.getElementById(Kel);
    // Set Provinsi
    provSelect.innerHTML = `<option value="${ProvValue}">${ProvValue}</option>`;
    provSelect.value = ProvValue;

    // Set Kota
    kotaSelect.innerHTML = `<option value="${KotaValue}">${KotaValue}</option>`;
    kotaSelect.value = KotaValue;

    // Set Kecamatan
    kecSelect.innerHTML = `<option value="${KecValue}">${KecValue}</option>`;
    kecSelect.value = KecValue;

    // Set Kelurahan
    kelSelect.innerHTML = `<option value="${KelValue}">${KelValue}</option>`;
    kelSelect.value = KelValue;
}
// END CODE


// ALL Modal In Home
export function openModal(modalId, contentId, btnId) {
    const open = document.getElementById(btnId);
    const modal = document.getElementById(modalId);
    const modalContent = document.getElementById(contentId);

    // Check if elements exist
    if (!open || !modal || !modalContent) {
        console.warn(`Modal elements not found: ${btnId}, ${modalId}, ${contentId}`);
        return;
    }

    open.addEventListener("click", () => {
        modal.classList.remove("hidden");
        const floatingMessage = document.getElementById("floatingMessage");
        if (floatingMessage) {
            floatingMessage.classList.add("hidden");
        }
        gsap.fromTo(
            modalContent,
            { opacity: 0, scale: 0.9 },
            {
                opacity: 1,
                scale: 1,
                duration: 0.5,
                ease: "power2.out",
            }
        );
    });
}

function closeModal(btnId, inputId, modalId, contentId) {
    const closeModal = document.getElementById(btnId);
    const modalContent = document.getElementById(contentId);
    const modal = document.getElementById(modalId);

    // Check if elements exist
    if (!closeModal || !modalContent || !modal) {
        console.warn(`Close modal elements not found: ${btnId}, ${contentId}, ${modalId}`);
        return;
    }

    closeModal.addEventListener("click", () => {
        const floatingMessage = document.getElementById("floatingMessage");
        if (floatingMessage) {
            floatingMessage.classList.remove("hidden");
        }
        gsap.to(modalContent, {
            opacity: 0,
            scale: 0.9,
            duration: 0.3,
            ease: "power2.in",
            onComplete: () => {
                modal.classList.add("hidden");
            },
        });
        const inputElement = document.getElementById(inputId);
        if (inputElement) {
            inputElement.value = "";
        }
    });
}

function closeModalProfile(btnId, modalId, contentId) {
    const closeModal = document.getElementById(btnId);
    const modal = document.getElementById(modalId);
    const modalContent = document.getElementById(contentId);
    document.getElementById("floatingMessage").classList.remove("hidden")
    closeModal.addEventListener("click", () => {
        gsap.to(modalContent, {
            opacity: 0,
            scale: 0.9,
            duration: 0.3,
            ease: "power2.in",
            onComplete: () => {
                modal.classList.add("hidden");
            },
        });
    });
}

function closeModalTerms(modalId, contentId) {
    const modal = document.getElementById(modalId);
    const modalContent = document.getElementById(contentId);

    gsap.to(modalContent, {
        opacity: 0,
        scale: 0.9,
        duration: 0.3,
        ease: "power2.in",
        onComplete: () => {
            modal.classList.add("hidden");
        },
    });
}

function toggleElements(ids, show = true) {
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.toggle("hidden", !show);
    });
}

function resetView(...elems) {
    toggleElements([
        "member", "member1", "cek-member", "cek-member1",
        "closeModal", "closeModal1", "petunjuk-member", "petunjuk-member1"
    ], true);
    elems.forEach(el => el.remove());
}

function viewPoinMember(data, updateProfile) {
    const modalContent = document.getElementById("modalContent");
    const modalContent1 = document.getElementById("modalContent1");
    toggleElements([
        "member", "member1", "cek-member", "cek-member1",
        "closeModal", "closeModal1", "petunjuk-member", "petunjuk-member1"
    ], false);
    const inforDiv = document.createElement("div");
    const inforDiv1 = document.createElement("div");
    const htmlInfor = `
<div class="text-sm text-gray-800 space-y-4">
  <div class="grid grid-cols-2 gap-y-2 border-b pb-2">
    <div class="font-semibold">Kode Member:</div>
    <div>${data.data.customer.kode_member
            ? data.data.customer.kode_member.slice(0, 4) + '******' + data.data.customer.kode_member.slice(-2)
            : ''
        }</div>
    
    <div class="font-semibold">Nama:</div>
    <div>${data.data.customer.nama_customer}</div>
    
    ${!updateProfile ? `
    <div class="font-semibold">Poin:</div>
    <div>${data.data.customer.total_point}</div>
    ` : ''}
  </div>

  ${updateProfile ? `
  <div class="flex gap-3 justify-end mt-2">
    <button id="openModalProfile" class="px-4 py-2 bg-pink-600 text-white rounded-md shadow hover:bg-pink-700 transition">Lengkapi Data</button>
    <button id="btn-kembali" class="px-4 py-2 bg-gray-300 rounded-md shadow hover:bg-gray-400 transition">Kembali</button>
  </div>
  ` : `
  <!-- History Transaksi -->
    <div class="space-y-1">
        <h3 class="font-semibold border-b pb-1">History Transaksi</h3>
        <div class="max-h-44 overflow-y-auto border rounded-md p-2 space-y-4 bg-white">
    ${data.data.transaksi_terakhir.map((transaksi) => {
            const formattedNominal = `Rp ${transaksi.total_belanja.toLocaleString('id-ID')}`;

            return `
        <div class="border-b pb-2">
        <div class="text-sm font-semibold text-gray-800">${transaksi.nama_toko}</div>
        <div class="flex justify-between items-center text-sm text-gray-700">
            <div>${formattedNominal}</div>
            <div class="flex items-center gap-2">
            <span class="text-gray-500">${transaksi.tanggal}</span>
            <a href="transaksi?kode=${transaksi.kode_transaksi ?? ''}&member=${data.data.customer.kode_member ?? ''}" 
                class="text-xs text-white bg-blue-500 px-2 py-1 rounded hover:bg-blue-600 transition"
                target="_blank">
                Lihat Struk
            </a>
            </div>
        </div>
        </div>`;
        }).join('')}
    </div>
  </div>

  <div class="flex justify-end mt-3">
    <button id="btn-kembali" class="px-4 py-2 bg-gray-300 rounded-md shadow hover:bg-gray-400 transition">Kembali</button>
  </div>
  `}
</div>
`;

    // /
    // /
    const htmlInfor1 = `
    <div class="text-sm text-gray-800 space-y-4">
      <div class="grid grid-cols-2 gap-y-2 border-b pb-2">
        <div class="font-semibold">Kode Member:</div>
        <div>${data.data.customer.kode_member
            ? data.data.customer.kode_member.slice(0, 4) + '******' + data.data.customer.kode_member.slice(-2)
            : ''
        }</div>
        
        <div class="font-semibold">Nama:</div>
        <div>${data.data.customer.nama_customer}</div>
        
        ${!updateProfile ? `
        <div class="font-semibold">Poin:</div>
        <div>${data.data.customer.total_point}</div>
        ` : ''}
      </div>
    
      ${updateProfile ? `
      <div class="flex gap-3 justify-end mt-2">
        <button id="openModalProfile1" class="px-4 py-2 bg-pink-600 text-white rounded-md shadow hover:bg-pink-700 transition">Lengkapi Data</button>
        <button id="btn-kembali1" class="px-4 py-2 bg-gray-300 rounded-md shadow hover:bg-gray-400 transition">Kembali</button>
      </div>
      ` : `
      <!-- History Transaksi -->
      <div class="space-y-1">
        <h3 class="font-semibold border-b pb-1">History Transaksi</h3>
        <div class="max-h-72 overflow-y-auto border rounded-md p-2 space-y-4 bg-white">
    ${data.data.transaksi_terakhir.map((transaksi) => {
            const formattedNominal = `Rp ${transaksi.total_belanja.toLocaleString('id-ID')}`;

            return `
        <div class="border-b pb-2">
        <div class="text-sm font-semibold text-gray-800">${transaksi.nama_toko}</div>
        <div class="flex justify-between items-center text-sm text-gray-700">
            <div>${formattedNominal}</div>
            <div class="flex items-center gap-2">
            <span class="text-gray-500">${transaksi.tanggal}</span>
            <a href="transaksi?kode=${transaksi.kode_transaksi ?? ''}&member=${data.data.customer.kode_member ?? ''}" 
                class="text-xs text-white bg-blue-500 px-2 py-1 rounded hover:bg-blue-600 transition"
                target="_blank">
                Lihat Struk
            </a>
            </div>
        </div>
        </div>`;
        }).join('')}
    </div>
    
      <div class="flex justify-end mt-3">
        <button id="btn-kembali1" class="px-4 py-2 bg-gray-300 rounded-md shadow hover:bg-gray-400 transition">Kembali</button>
      </div>
      `}
    </div>
    `;
    inforDiv.innerHTML = htmlInfor;
    inforDiv1.innerHTML = htmlInfor1;
    modalContent.appendChild(inforDiv);
    modalContent1.appendChild(inforDiv1);
    document.getElementById("btn-kembali").addEventListener("click", () => resetView(inforDiv, inforDiv1));
    document.getElementById("btn-kembali1").addEventListener("click", () => resetView(inforDiv, inforDiv1));
}

async function historyTrans(data, updateProfile) {
    if (!updateProfile) {
        await Swal.fire({
            icon: 'info',
            title: 'Lengkapi Profil Anda',
            text: 'Silakan lengkapi profil terlebih dahulu sebelum melihat riwayat transaksi.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#f87171'
        });
        return; // Stop di sini kalau belum update profile
    }

    const app = document.getElementById("app");

    const addHtml = `<section id="history-transaksi" class="max-w-2xl mx-auto mt-6 p-4 bg-gray-50 border rounded-lg shadow space-y-4 relative">
      <div class="flex items-center gap-2 mb-2">
        <button id="btn-kembali" class="text-gray-600 hover:text-gray-800 transition">
          <!-- Heroicons arrow-left SVG -->
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <h2 class="text-lg font-semibold text-gray-800">🧾 History Transaksi</h2>
      </div>

      <div class="max-h-72 overflow-y-auto border rounded-md p-3 space-y-4 bg-white">
        ${data.data.transaksi_terakhir.map((transaksi) => {
        const formattedNominal = `Rp ${transaksi.total_belanja.toLocaleString('id-ID')}`;
        return `
          <div class="border-b pb-3">
            <div class="text-sm font-semibold text-gray-800">${transaksi.nama_toko}</div>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-sm text-gray-700 mt-1 gap-2">
              <div>${formattedNominal}</div>
              <div class="flex flex-wrap items-center gap-2 justify-between sm:justify-end">
                <span class="text-gray-500 text-xs">${transaksi.tanggal}</span>
                <a href="transaksi?kode=${transaksi.kode_transaksi ?? ''}&member=${data.data.customer.kode_member ?? ''}" 
                  class="text-xs text-white bg-blue-500 px-3 py-1 rounded hover:bg-blue-600 transition"
                  target="_blank">
                  Lihat Struk
                </a>
                <button
                  class="text-xs text-white bg-yellow-500 px-3 py-1 rounded hover:bg-yellow-600 transition"
                  onclick="reviewTransaksi('${transaksi.kode_transaksi}')">
                  Review
                </button>
              </div>
            </div>
          </div>`;
    }).join('')}
      </div>
    </section>`;

    app.innerHTML = addHtml;
}

const modalViewMemberOrNot = (data, updateProfile) => {
    const modalContent = document.getElementById("modalContent1");
    const eDiv = document.createElement("div");
    toggleElements([
        "member", "member1", "cek-member", "cek-member1",
        "closeModal", "closeModal1", "petunjuk-member", "petunjuk-member1"
    ], false);
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
  ${updateProfile ?
            `
    <div class="flex gap-3 justify-end mt-2">
    <button id="lengkapiData" class="px-4 py-2 bg-pink-600 text-white rounded-md shadow hover:bg-pink-700 transition">Lengkapi Data</button>
    <button id="btn-kembali" class="px-4 py-2 bg-gray-300 rounded-md shadow hover:bg-gray-400 transition">Kembali</button>
    </div>
    ` : `
    <div class="flex gap-3 justify-end mt-2">
    <button id="btn-kembali" class="px-4 py-2 bg-gray-300 rounded-md shadow hover:bg-gray-400 transition">Kembali</button>
    </div>
    `}
`
    eDiv.innerHTML = addHtml;
    modalContent.appendChild(eDiv);
    document.getElementById("btn-kembali").addEventListener("click", () => resetView(eDiv));

    document.getElementById("lengkapiData").addEventListener("click", (e) => {
        Swal.fire({
            icon: "info",
            title: "Login",
            text: "Silahkan login terlebih dahulu untuk melanjutkan pengisian data",
            confirmButtonText: "Ok"
        }).then(() => {
            window.location.href = "/log_in"
        })
    })
}
// END CODE

// Button Click For Check Data
function btnClickCheck(btnId, inputId) {
    const btn = document.getElementById(btnId);
    if (!btn) {
        console.warn(`Button not found: ${btnId}`);
        return;
    }
    
    btn.addEventListener("click", (e) => {
        e.preventDefault();
        const inputElement = document.querySelector(inputId);
        if (!inputElement) {
            console.warn(`Input element not found: ${inputId}`);
            return;
        }
        
        let kodeMember = inputElement.value;
        if (!kodeMember) {
            alertToast("info", "Kode Atau No Hp Tidak Boleh Kosong");
        } else if (kodeMember.length < 10) {
            alertToast("info", "Kode Atau No Hp Tidak Valid");
        } else {
            if (btnId === "cek-member") {
                getDataToMember(kodeMember);
            } else {
                getDataFromGuest(kodeMember);
            }
        }
    });
}
// END CODE
// Input Only Number And Alphabet
function onlyNumberInput(placeholder, maxLength) {
    const input = document.querySelector(placeholder);
    if (!input) {
        console.warn(`Input element not found: ${placeholder}`);
        return;
    }
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^0-9]/g, "").slice(0, maxLength);
    });
}

function onlyAlphabetInput(placeholder, maxLength) {
    const input = document.querySelector(placeholder);
    if (!input) {
        console.warn(`Input element not found: ${placeholder}`);
        return;
    }
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^a-zA-Z\s]/g, "").slice(0, maxLength);
    });
}

function onlyAlphaAndNumberInput(placeholder, maxLength) {
    const input = document.querySelector(placeholder);
    if (!input) {
        console.warn(`Input element not found: ${placeholder}`);
        return;
    }
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^a-zA-Z0-9]/g, "").slice(0, maxLength);
    });
}
// END CODE

// Checkbox Same Address With KTP
async function checkBoxAddress(alamatId, provinsiId, kotaId, kelId, kecId) {
    const checkBox = document.getElementById("sesuai");
    
    // Check if checkbox exists
    if (!checkBox) {
        console.warn("Checkbox 'sesuai' not found");
        return;
    }

    const ktpInputs = {
        alamat: document.querySelector("#alamat_ktp"),
        provinsi: document.querySelector("#provinsi"),
        kota: document.querySelector("#kota"),
        kel: document.querySelector("#kel"),
        kec: document.querySelector("#kec")
    };

    const domisiliInputs = {
        alamat: document.getElementById(alamatId),
        provinsi: document.getElementById(provinsiId),
        kota: document.getElementById(kotaId),
        kel: document.getElementById(kelId),
        kec: document.getElementById(kecId)
    };

    checkBox.addEventListener("change", async () => {
        const note = document.createElement("p");
        const noteId = "alamat-note";
        const container = document.getElementById("checkbox-container");
        const existingNote = document.getElementById(noteId);
        if (checkBox.checked) {
            note.id = noteId;
            note.className = "text-sm text-red-600 mt-2 italic";
            note.textContent = "catatan* : Jika ingin mengubah alamat, silakan hilangkan centangnya";
            container.appendChild(note);
            // Alamat
            domisiliInputs.alamat.value = ktpInputs.alamat.value;
            domisiliInputs.alamat.setAttribute("disabled", true);
            ktpInputs.alamat.setAttribute("disabled", true);

            // Provinsi
            const provValue = ktpInputs.provinsi.value;
            domisiliInputs.provinsi.value = provValue;
            domisiliInputs.provinsi.setAttribute("disabled", true);
            ktpInputs.provinsi.setAttribute("disabled", true);

            // Kota
            await loadKotaDomisiliManual(provValue, domisiliInputs.kota);
            domisiliInputs.kota.value = ktpInputs.kota.value;
            domisiliInputs.kota.setAttribute("disabled", true);
            ktpInputs.kota.setAttribute("disabled", true);

            // Kecamatan
            await loadKecDomisiliManual(ktpInputs.kota.value, domisiliInputs.kec);
            domisiliInputs.kec.value = ktpInputs.kec.value;
            domisiliInputs.kec.setAttribute("disabled", true);
            ktpInputs.kec.setAttribute("disabled", true);

            // Kelurahan
            await loadKelDomisiliManual(ktpInputs.kec.value, domisiliInputs.kel);
            domisiliInputs.kel.value = ktpInputs.kel.value;
            domisiliInputs.kel.setAttribute("disabled", true);
            ktpInputs.kel.setAttribute("disabled", true);

        } else {
            // Reset
            for (let key in ktpInputs) {
                ktpInputs[key].removeAttribute("disabled");
                domisiliInputs[key].removeAttribute("disabled");
                domisiliInputs[key].value = "";
            }
            if (existingNote) existingNote.remove();
        }
    });

}
// END CODE

// All Wilayah Code
function loadProvinsi() {
    fetch('src/api/location/get_wilayah.php')
        .then(res => res.json())
        .then(response => {
            const provinsiArray = response.data;
            const provinsiSelect = document.getElementById("provinsi");

            // Tambahkan default option
            provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';

            provinsiArray.forEach(prov => {
                const option = document.createElement("option");
                option.value = prov.code;
                option.textContent = prov.name;
                provinsiSelect.appendChild(option);
            });
        })
        .catch(err => {
            console.error("Gagal mengambil data provinsi:", err);
        });
}

function loadKota() {
    document.getElementById("provinsi").addEventListener("change", function () {
        const provinsiCode = this.value;
        const kotaSelect = document.getElementById("kota");
        const kecSelect = document.getElementById("kec");
        const kelSelect = document.getElementById("kel");

        if (!kotaSelect || !kecSelect || !kelSelect) {
            console.warn("Location select elements not found");
            return;
        }

        // Reset kota, kec, kel
        kotaSelect.innerHTML = '<option value="">Pilih Kota</option>';
        document.getElementById("kec").innerHTML = '<option value="">Pilih Kecamatan</option>';
        document.getElementById("kel").innerHTML = '<option value="">Pilih Kelurahan</option>';

        if (!provinsiCode) return;

        fetch(`src/api/location/get_kota?provinsi=${provinsiCode}`)
            .then(res => res.json())
            .then(response => {
                const kotaArray = response.data;

                kotaArray.forEach(kota => {
                    const option = document.createElement("option");
                    option.value = kota.code;
                    option.textContent = kota.name;
                    kotaSelect.appendChild(option);
                });
            })
            .catch(err => {
                console.error("Gagal mengambil data kota:", err);
            });
    });
}

function loadKec() {
    document.getElementById("kota").addEventListener("change", (event) => {
        const kotaKode = event.target.value;
        const kecSelect = document.getElementById("kec");

        // Reset kecamatan dan kelurahan
        kecSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
        document.getElementById("kel").innerHTML = '<option value="">Pilih Kelurahan</option>';

        if (!kotaKode) return;

        fetch(`src/api/location/get_kec?kota=${kotaKode}`)
            .then(res => res.json())
            .then(response => {
                const kecArray = response.data;

                kecArray.forEach(kec => {
                    const option = document.createElement("option");
                    option.value = kec.code;
                    option.textContent = kec.name;
                    kecSelect.appendChild(option);
                });
            })
            .catch(err => {
                console.error("Gagal mengambil data kecamatan", err);
            });
    });
}

function loadKel() {
    document.getElementById("kec").addEventListener("change", (event) => {
        const kecKode = event.target.value;
        const kelSelect = document.getElementById("kel");

        // Reset kelurahan
        kelSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';

        if (!kecKode) return;

        fetch(`src/api/location/get_kel?kecamatan=${kecKode}`)
            .then(res => res.json())
            .then(response => {
                const kelArray = response.data;

                kelArray.forEach(kel => {
                    const option = document.createElement("option");
                    option.value = kel.code;
                    option.textContent = kel.name;
                    kelSelect.appendChild(option);
                });
            })
            .catch(err => {
                console.error("Gagal mengambil data kelurahan", err);
            });
    });
}
function loadProvinsiDomisili() {
    fetch('src/api/location/get_wilayah.php')
        .then(res => res.json())
        .then(response => {
            const provinsiArray = response.data; // ambil array-nya
            const provinsiSelect = document.getElementById("provinsi_domisili");
            provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
            provinsiArray.forEach(prov => {
                const option = document.createElement("option");
                option.value = prov.code;
                option.textContent = prov.name;
                provinsiSelect.appendChild(option);
            });
        })
        .catch(err => {
            console.error("Gagal mengambil data provinsi:", err);
        });
}
function loadKotaDomisili() {
    document.getElementById("provinsi_domisili").addEventListener("change", function () {

        const provinsiCode = this.value;

        fetch(`src/api/location/get_kota?provinsi=${provinsiCode}`)
            .then(res => res.json())
            .then(response => {
                const kotaSelect = document.getElementById("kota_domisili");
                const kotaArray = response.data;
                kotaSelect.innerHTML = '<option value="">Pilih Kota</option>';
                console.log("Kota", kotaArray)
                kotaArray.forEach(kota => {
                    const option = document.createElement("option");
                    option.value = kota.code;
                    option.textContent = kota.name;
                    kotaSelect.appendChild(option);
                });
            })
            .catch(err => {
                console.error("Gagal mengambil data kota:", err);
            });
    });
}
function loadKecDomisili() {
    document.getElementById("kota_domisili").addEventListener("change", (event) => {
        const kotaKode = event.target.value;
        fetch(`src/api/location/get_kec?kota=${kotaKode}`)
            .then(res => res.json())
            .then(response => {
                const kecSelect = document.getElementById("kecamatan_domisili");
                const kecArray = response.data;
                kecSelect.innerHTML = "<option value=''>Pilih Kecamatan</option>";
                console.log("Kecamatan", kecArray)
                kecArray.forEach(kec => {
                    const option = document.createElement("option");
                    option.value = kec.code;
                    option.textContent = kec.name;
                    kecSelect.appendChild(option);
                })
            })
            .catch(err => {
                console.error("Gagal mengambil data kecamatan", err);
            });
    });
}
function loadKelDomisili() {
    document.getElementById("kecamatan_domisili").addEventListener("change", (event) => {
        const kecKode = event.target.value;
        fetch(`src/api/location/get_kel?kecamatan=${kecKode}`)
            .then(res => res.json())
            .then(response => {
                const kelSelect = document.getElementById("kelurahan_domisili");
                const kelArray = response.data;
                kelSelect.innerHTML = "<option value=''>Pilih Kelurahan</option>";
                console.log("Kelurahan", kelArray)
                kelArray.forEach(kel => {
                    const option = document.createElement("option");
                    option.value = kel.code;
                    option.textContent = kel.name;
                    kelSelect.appendChild(option);
                })
            })
            .catch(err => {
                console.error("Gagal mengambil data kecamatan", err);
            });
    })
}
function loadKotaDomisiliManual(provCode, selectEl) {
    return fetch(`src/api/location/get_kota?provinsi=${provCode}`)
        .then(res => res.json())
        .then(response => {
            selectEl.innerHTML = "";
            response.data.forEach(kota => {
                const option = document.createElement("option");
                option.value = kota.code;
                option.textContent = kota.name;
                selectEl.appendChild(option);
            });
        });
}
function loadKecDomisiliManual(kotaCode, selectEl) {
    return fetch(`src/api/location/get_kec?kota=${kotaCode}`)
        .then(res => res.json())
        .then(response => {
            selectEl.innerHTML = "";
            response.data.forEach(kec => {
                const option = document.createElement("option");
                option.value = kec.code;
                option.textContent = kec.name;
                selectEl.appendChild(option);
            });
        });
}
function loadKelDomisiliManual(kecCode, selectEl) {
    return fetch(`src/api/location/get_kel?kecamatan=${kecCode}`)
        .then(res => res.json())
        .then(response => {
            selectEl.innerHTML = "";
            response.data.forEach(kel => {
                const option = document.createElement("option");
                option.value = kel.code;
                option.textContent = kel.name;
                selectEl.appendChild(option);
            });
        });
}
//  END CODE

// Modal Terms And Condition
function checkTerms() {
    const checkSyarat = document.getElementById("syarat");
    const btnSetuju = document.getElementById("setuju");
    const btnTidakSetuju = document.getElementById("tidak-setuju");

    btnSetuju.addEventListener("click", function () {
        checkSyarat.checked = true;
        closeModalTerms("modalTerms", "modalContentTerms")
    })
    btnTidakSetuju.addEventListener("click", function () {
        checkSyarat.checked = false;
        closeModalTerms("modalTerms", "modalContentTerms")
    })
}
// END CODE

// Initialize all functionality when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
    
    btnClickCheck("cek-member", "#member");
    btnClickCheck("cek-member1", "#member1");
    // openModal("modalMember", "modalContent", "openModal");
    openModal("modalTerms", "modalContentTerms", "syarat");
    checkTerms();
    closeModal("closeModal", "member", "modalMember", "modalContent");
    closeModal("closeModal1", "member1", "modalMember1", "modalContent1");
    closeModalProfile("closeModalProfile", "modalMemberProfile", "modalContentProfile");
    onlyNumberInput('input[placeholder="kode atau no hp"]', 13);
    onlyNumberInput('input[placeholder="NIK KTP"]', 16);
    onlyNumberInput('input[placeholder="NO HP Aktif"]', 13);
    checkBoxAddress("alamat_domisili", "provinsi_domisili", "kota_domisili", "kelurahan_domisili", "kecamatan_domisili");
    
});

export default { getDataToMember, openModal };