export const loadProvinsi = async (id) => {
    try {
        const data = await fetch('/src/api/location/get_wilayah.php')
        const response = await data.json()

        const provinsiArray = response.data;
        const provinsiSelect = document.getElementById(id);
        provinsiArray.forEach(prov => {
            const option = document.createElement("option");
            if (!prov.code && prov.name) {
                provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
            } else {
                option.value = prov.code;
                option.textContent = prov.name;
            }
            provinsiSelect.appendChild(option);
        });
        return provinsiArray;
    } catch (err) {
        console.error("Gagal mengambil data provinsi:", err);
        return [];
    };
}

export const loadKota = async (id, kecId, kelId, provinsiId) => {
    document.getElementById(provinsiId).addEventListener("change", async function () {
        try {

            const provinsiCode = this.value;
            const kotaSelect = document.getElementById(id);

            // Reset kota, kec, kel
            kotaSelect.innerHTML = '<option value="">Pilih Kota</option>';
            document.getElementById(kecId).innerHTML = '<option value="">Pilih Kecamatan</option>';
            document.getElementById(kelId).innerHTML = '<option value="">Pilih Kelurahan</option>';

            if (!provinsiCode) return;

            const res = await fetch(`/src/api/location/get_kota?provinsi=${provinsiCode}`)
            const response = await res.json()
            const kotaArray = response.data;

            kotaArray.forEach(kota => {
                const option = document.createElement("option");
                option.value = kota.code;
                option.textContent = kota.name;
                kotaSelect.appendChild(option);
            });
            return kotaArray;
        }
        catch (err) {
            console.error("Gagal mengambil data kota:", err);
            return [];
        }
    })
}

export const loadKec = async (id, kelId, kotaId) => {
    try {
        document.getElementById(kotaId).addEventListener("change", async (event) => {
            const kotaKode = event.target.value;
            const kecSelect = document.getElementById(id);

            // Reset kecamatan dan kelurahan
            kecSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            document.getElementById(kelId).innerHTML = '<option value="">Pilih Kelurahan</option>';

            if (!kotaKode) return;

            const res = await fetch(`/src/api/location/get_kec?kota=${kotaKode}`)
            const response = await res.json()

            const kecArray = response.data;

            kecArray.forEach(kec => {
                const option = document.createElement("option");
                option.value = kec.code;
                option.textContent = kec.name;
                kecSelect.appendChild(option);
            });
        })
    } catch (err) {
        console.error("Gagal mengambil data kecamatan", err);
        return [];
    }
}


export const loadKel = async (id, kecId) => {
    try {
        document.getElementById(kecId).addEventListener("change", async (event) => {
            const kecKode = event.target.value;
            const kelSelect = document.getElementById(id);

            // Reset kelurahan
            kelSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';

            if (!kecKode) return;

            const res = await fetch(`/src/api/location/get_kel?kecamatan=${kecKode}`)
            const response = await res.json()
            const kelArray = response.data;

            kelArray.forEach(kel => {
                const option = document.createElement("option");
                option.value = kel.code;
                option.textContent = kel.name;
                kelSelect.appendChild(option);
            });
        })
    } catch (err) {
        console.error("Gagal mengambil data kelurahan", err);
        return []
    }
}

export const checkBoxAddress = (alamatId, provinsiId, kotaId, kelId, kecId) => {
    const checkBox = document.getElementById("sesuai");

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
    function loadKotaDomisiliManual(provCode, selectEl) {
        return fetch(`/src/api/location/get_kota?provinsi=${provCode}`)
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
        return fetch(`/src/api/location/get_kec?kota=${kotaCode}`)
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
        return fetch(`/src/api/location/get_kel?kecamatan=${kecCode}`)
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

export default {
    loadProvinsi,
    loadKota,
    loadKec,
    loadKel,
    checkBoxAddress
}