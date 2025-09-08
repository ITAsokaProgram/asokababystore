// disabel tombol update jika tidak ada perubahan value -----start
var updateButton;
var inputs;
var originalValues = {};

document.addEventListener('DOMContentLoaded', function () {
    updateButton = document.getElementById('updateButton');
    inputs = document.querySelectorAll('input, textarea, select');

    inputs.forEach(input => {
        originalValues[input.name] = input.type === 'checkbox' ? input.checked : input.value;
        input.addEventListener('change', checkChanges);
    });
});

function checkChanges() {
    let isChanged = false;
    for (const input of inputs) {
        const originalValue = originalValues[input.name];
        const currentValue = input.type === 'checkbox' ? input.checked : input.value;
        if (originalValue !== currentValue) {
            isChanged = true;
            break;
        }
    }
    updateButton.disabled = !isChanged;
}
// disabel tombol update jika tidak ada perubahan value -----End

// Untuk disabled kolom sesuai checkbox
window.onload = function () {
    var checkboxDomisili = document.querySelector('input[name="domisili"]');
    var inputAlamatK = document.querySelector('input[name="alamatk"]');
    var inputProvK = document.querySelector('input[name="provk"]');
    var inputKotaK = document.querySelector('input[name="kotak"]');
    var inputKecK = document.querySelector('input[name="keck"]');
    var inputDesaK = document.querySelector('input[name="desak"]');
    var inputAlamatD = document.querySelector('input[name="alamatd"]');
    var inputProvD = document.querySelector('input[name="provd"]');
    var inputKotaD = document.querySelector('input[name="kotad"]');
    var inputKecD = document.querySelector('input[name="kecd"]');
    var inputDesaD = document.querySelector('input[name="desad"]');

    function syncDomisiliFields() {
        if (checkboxDomisili.checked) {
            inputAlamatD.value = inputAlamatK.value;
            inputProvD.value = inputProvK.value;
            inputKotaD.value = inputKotaK.value;
            inputKecD.value = inputKecK.value;
            inputDesaD.value = inputDesaK.value;

            [inputAlamatK, inputProvK, inputKotaK, inputKecK, inputDesaK, inputAlamatD, inputProvD, inputKotaD, inputKecD, inputDesaD].forEach(input => {
                input.readOnly = true; // Ubah ini dari disabled menjadi readOnly
                input.style.color = 'grey';
            });
        } else {
            [inputAlamatK, inputProvK, inputKotaK, inputKecK, inputDesaK, inputAlamatD, inputProvD, inputKotaD, inputKecD, inputDesaD].forEach(input => {
                input.readOnly = false; // Ubah ini dari disabled menjadi readOnly
                input.style.color = 'black';
                //input.value = '';
            });

        }
        // Panggil checkChanges setelah mengubah nilai input
        checkChanges();
    }


    checkboxDomisili.addEventListener('change', syncDomisiliFields);
    // Jalankan fungsi ini saat halaman dimuat untuk menyesuaikan keadaan awal
    syncDomisiliFields();
}


// mengosongkan nilai jika ada perubahan di kolom prov kota dan kecamatan------ Start
var provk1, provk11, provd1, provd11
var kotak1, kotak11, kotad1, kotad11
var keck1, keck11, kecd1, kecd11

// Mendapatkan elemen input berdasarkan nama
var provk = document.getElementsByName("provk")[0];
var kotak = document.getElementsByName("kotak")[0];
var keck = document.getElementsByName("keck")[0];
var desk = document.getElementsByName("desak")[0];
var provd = document.getElementsByName("provd")[0];
var kotad = document.getElementsByName("kotad")[0];
var kecd = document.getElementsByName("kecd")[0];
var desd = document.getElementsByName("desad")[0];
document.addEventListener('DOMContentLoaded', function () {
    provk1 = provk.value
    provd1 = provd.value
    kotak1 = kotak.value
    kotad1 = kotad.value
    keck1 = keck.value
    kecd1 = kecd.value
});

// Fungsi untuk mengosongkan nilai input
function clearInputs() {
    desk.value = '';
    keck.value = '';
    kotak.value = '';
}
// Fungsi untuk mengosongkan nilai input
function clearInputs2() {
    desd.value = '';
    kecd.value = '';
    kotad.value = '';

}

function clearandtetap_Provk() {
    provk11 = provk.value;
    provk.addEventListener('blur', function () {
        provk1 = provk11;
    });

    if (provk11 !== provk1) {
        clearInputs();
    }
}
function clearandtetap_Provd() {
    provd11 = provd.value;
    provd.addEventListener('blur', function () {
        provd1 = provd11;
    });

    if (provd11 !== provd1) {
        clearInputs2();
    }
}

function clearandtetap_kotak() {
    kotak11 = kotak.value;
    kotak.addEventListener('blur', function () {
        kotak1 = kotak11;
    });

    if (kotak11 !== kotak1) {
        desk.value = '';
        keck.value = '';
    }
}
function clearandtetap_kotad() {
    kotad11 = kotad.value;
    kotad.addEventListener('blur', function () {
        kotad1 = kotad11;
    });

    if (kotad11 !== kotad1) {
        desd.value = '';
        kecd.value = '';
    }
}

function clearandtetap_keck() {
    keck11 = keck.value;
    keck.addEventListener('blur', function () {
        keck1 = keck11;
    });

    if (keck11 !== keck1) {
        desk.value = '';
    }
}
function clearandtetap_kecd() {
    kecd11 = kecd.value;
    kecd.addEventListener('blur', function () {
        kecd1 = kecd11;
    });

    if (kecd11 !== kecd1) {
        desd.value = '';
    }
}

// mengosongkan nilai jika ada perubahan di kolom prov kota dan kecamatan------ End










// Untuk kolom provk
document.getElementById('provk').addEventListener('input', function (e) {
    var inputVal = this.value;
    var suggestionsBox = document.getElementById('provkSuggestions');

    if (inputVal.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_prov.php?q=' + encodeURIComponent(inputVal), true);
        xhr.onload = function () {
            if (this.status == 200) {
                var results = JSON.parse(this.responseText);
                var suggestions = '';

                for (var i = 0; i < results.length; i++) {
                    suggestions += '<p>' + results[i] + '</p>'; // Atau format yang Anda inginkan
                }

                suggestionsBox.innerHTML = suggestions;
            }
        };
        xhr.send();
    } else {
        suggestionsBox.innerHTML = '';
    }
});

document.getElementById('provkSuggestions').addEventListener('click', function (e) {
    if (e.target.tagName === 'P') {
        document.getElementById('provk').value = e.target.textContent;
        this.innerHTML = '';
    }
    clearandtetap_Provk();
});



// Untuk kolom kotak
document.getElementById('kotak').addEventListener('input', function (e) {
    var inputVal = this.value;
    var provinsiVal = document.getElementById('provk').value; // Mengambil nilai provinsi
    var suggestionsBox = document.getElementById('kotakSuggestions');

    if (inputVal.length > 0 && provinsiVal.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_kotak.php?q=' + encodeURIComponent(inputVal) + '&provinsi=' + encodeURIComponent(provinsiVal), true);
        xhr.onload = function () {
            if (this.status == 200) {
                var results = JSON.parse(this.responseText);
                var suggestions = '';

                for (var i = 0; i < results.length; i++) {
                    suggestions += '<p>' + results[i] + '</p>'; // Atau format yang Anda inginkan
                }

                suggestionsBox.innerHTML = suggestions;
            }
        };
        xhr.send();
    } else {
        suggestionsBox.innerHTML = '';
    }
});

document.getElementById('kotakSuggestions').addEventListener('click', function (e) {
    if (e.target.tagName === 'P') {
        document.getElementById('kotak').value = e.target.textContent;
        this.innerHTML = '';
    }
    clearandtetap_kotak();
});


// Untuk kolom keck
document.getElementById('keck').addEventListener('input', function (e) {
    var inputVal = this.value;
    var kotaVal = document.getElementById('kotak').value; // Mengambil nilai provinsi
    var suggestionsBox = document.getElementById('keckSuggestions');

    if (inputVal.length > 0 && kotaVal.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_keck.php?q=' + encodeURIComponent(inputVal) + '&kota=' + encodeURIComponent(kotaVal), true);
        xhr.onload = function () {
            if (this.status == 200) {
                var results = JSON.parse(this.responseText);
                var suggestions = '';

                for (var i = 0; i < results.length; i++) {
                    suggestions += '<p>' + results[i] + '</p>'; // Atau format yang Anda inginkan
                }

                suggestionsBox.innerHTML = suggestions;
            }
        };
        xhr.send();
    } else {
        suggestionsBox.innerHTML = '';
    }
});

document.getElementById('keckSuggestions').addEventListener('click', function (e) {
    if (e.target.tagName === 'P') {
        document.getElementById('keck').value = e.target.textContent;
        this.innerHTML = '';
    }
    clearandtetap_keck();
});


// Untuk kolom desk
document.getElementById('desk').addEventListener('input', function (e) {
    var inputVal = this.value;
    var kecamatanVal = document.getElementById('keck').value; // Mengambil nilai provinsi
    var suggestionsBox = document.getElementById('deskSuggestions');

    if (inputVal.length > 0 && kecamatanVal.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_desk.php?q=' + encodeURIComponent(inputVal) + '&kecamatan=' + encodeURIComponent(kecamatanVal), true);
        xhr.onload = function () {
            if (this.status == 200) {
                var results = JSON.parse(this.responseText);
                var suggestions = '';

                for (var i = 0; i < results.length; i++) {
                    suggestions += '<p>' + results[i] + '</p>'; // Atau format yang Anda inginkan
                }

                suggestionsBox.innerHTML = suggestions;
            }
        };
        xhr.send();
    } else {
        suggestionsBox.innerHTML = '';
    }
});

document.getElementById('deskSuggestions').addEventListener('click', function (e) {
    if (e.target.tagName === 'P') {
        document.getElementById('desk').value = e.target.textContent;
        this.innerHTML = '';
        // Memanggil fungsi checkChanges untuk memeriksa perubahan
        checkChanges();
    }
});


<<<<<<< HEAD





=======
>>>>>>> master
// Untuk kolom provd
document.getElementById('provd').addEventListener('input', function (e) {
    var inputVal = this.value;
    var suggestionsBox = document.getElementById('provdSuggestions');

    if (inputVal.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_prov.php?q=' + encodeURIComponent(inputVal), true);
        xhr.onload = function () {
            if (this.status == 200) {
                var results = JSON.parse(this.responseText);
                var suggestions = '';

                for (var i = 0; i < results.length; i++) {
                    suggestions += '<p>' + results[i] + '</p>'; // Atau format yang Anda inginkan
                }

                suggestionsBox.innerHTML = suggestions;
            }
        };
        xhr.send();
    } else {
        suggestionsBox.innerHTML = '';
    }
});

document.getElementById('provdSuggestions').addEventListener('click', function (e) {
    if (e.target.tagName === 'P') {
        document.getElementById('provd').value = e.target.textContent;
        this.innerHTML = '';
    }
    clearandtetap_Provd();
});

// Untuk kolom kotad
document.getElementById('kotad').addEventListener('input', function (e) {
    var inputVal = this.value;
    var provinsiVal = document.getElementById('provd').value; // Mengambil nilai provinsi
    var suggestionsBox = document.getElementById('kotadSuggestions');

    if (inputVal.length > 0 && provinsiVal.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_kotak.php?q=' + encodeURIComponent(inputVal) + '&provinsi=' + encodeURIComponent(provinsiVal), true);
        xhr.onload = function () {
            if (this.status == 200) {
                var results = JSON.parse(this.responseText);
                var suggestions = '';

                for (var i = 0; i < results.length; i++) {
                    suggestions += '<p>' + results[i] + '</p>'; // Atau format yang Anda inginkan
                }

                suggestionsBox.innerHTML = suggestions;
            }
        };
        xhr.send();
    } else {
        suggestionsBox.innerHTML = '';
    }
});

document.getElementById('kotadSuggestions').addEventListener('click', function (e) {
    if (e.target.tagName === 'P') {
        document.getElementById('kotad').value = e.target.textContent;
        this.innerHTML = '';
    }
    clearandtetap_kotad();
});


// Untuk kolom kecd
document.getElementById('kecd').addEventListener('input', function (e) {
    var inputVal = this.value;
    var kotaVal = document.getElementById('kotad').value; // Mengambil nilai provinsi
    var suggestionsBox = document.getElementById('kecdSuggestions');

    if (inputVal.length > 0 && kotaVal.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_keck.php?q=' + encodeURIComponent(inputVal) + '&kota=' + encodeURIComponent(kotaVal), true);
        xhr.onload = function () {
            if (this.status == 200) {
                var results = JSON.parse(this.responseText);
                var suggestions = '';

                for (var i = 0; i < results.length; i++) {
                    suggestions += '<p>' + results[i] + '</p>'; // Atau format yang Anda inginkan
                }

                suggestionsBox.innerHTML = suggestions;
            }
        };
        xhr.send();
    } else {
        suggestionsBox.innerHTML = '';
    }
});

document.getElementById('kecdSuggestions').addEventListener('click', function (e) {
    if (e.target.tagName === 'P') {
        document.getElementById('kecd').value = e.target.textContent;
        this.innerHTML = '';
    }
    clearandtetap_kecd();
});


// Untuk kolom desad
document.getElementById('desad').addEventListener('input', function (e) {
    var inputVal = this.value;
    var kecamatanVal = document.getElementById('kecd').value; // Mengambil nilai provinsi
    var suggestionsBox = document.getElementById('desadSuggestions');

    if (inputVal.length > 0 && kecamatanVal.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_desk.php?q=' + encodeURIComponent(inputVal) + '&kecamatan=' + encodeURIComponent(kecamatanVal), true);
        xhr.onload = function () {
            if (this.status == 200) {
                var results = JSON.parse(this.responseText);
                var suggestions = '';

                for (var i = 0; i < results.length; i++) {
                    suggestions += '<p>' + results[i] + '</p>'; // Atau format yang Anda inginkan
                }

                suggestionsBox.innerHTML = suggestions;
            }
        };
        xhr.send();
    } else {
        suggestionsBox.innerHTML = '';
    }
});

document.getElementById('desadSuggestions').addEventListener('click', function (e) {
    if (e.target.tagName === 'P') {
        document.getElementById('desad').value = e.target.textContent;
        this.innerHTML = '';

        // Memanggil fungsi checkChanges untuk memeriksa perubahan
        checkChanges();
    }
});



// Pop Up Syarat dan Ketentuan
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalSyaratKetentuan');
    const btnOpenModal = document.getElementById('btnOpenModal');
    const spanClose = document.querySelector('.modal .close');
    const modalBody = document.getElementById('modalBody');

    // Fungsi untuk membuka modal
    btnOpenModal.onclick = function (e) {
        e.preventDefault(); // Cegah link berpindah halaman
        modal.style.display = 'block'; // Tampilkan modal

        // Muat konten dari SyaratKetentuan.php
        fetch('SyaratKetentuan.php')
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Gagal memuat syarat dan ketentuan.');
                }
                return response.text();
            })
            .then((data) => {
                modalBody.innerHTML = data; // Tampilkan konten di modal
            })
            .catch((error) => {
                modalBody.innerHTML = `<p style="color: red;">${error.message}</p>`;
            });
    };

    // Fungsi untuk menutup modal
    spanClose.onclick = function () {
        modal.style.display = 'none';
    };

    // Tutup modal jika pengguna mengklik di luar area modal
    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };
    
});

function agreeTerms() {
    document.querySelector("input[name='agree_terms']").checked = true;
    document.getElementById("modalSyaratKetentuan").style.display = "none";
    checkChanges(); // Add this to check changes when agreeing
}

function closeModal() {
    document.querySelector("input[name='agree_terms']").checked = false; 
    document.getElementById("modalSyaratKetentuan").style.display = "none";
    checkChanges(); // Add this to check changes when closing
}


document.addEventListener("DOMContentLoaded", function () {
    const domisiliCheckbox = document.querySelector('input[name="domisili"]');
    const requiredFields = ['alamatk', 'provk', 'kotak', 'keck', 'desak'];
    const tooltip = document.getElementById("warningTooltip");

    function checkFields() {
       return requiredFields.every(id => {
          const field = document.querySelector(`input[name="${id}"]`);
          return field && field.value.trim() !== "";
       });
    }

    function showTooltip() {
       const rect = domisiliCheckbox.getBoundingClientRect();
       tooltip.style.top = `${rect.top + window.scrollY - 30}px`;
       tooltip.style.left = `${rect.left + window.scrollX}px`;
       tooltip.classList.add("show");

       // Hilangkan tooltip setelah 2 detik
       setTimeout(() => {
          tooltip.classList.remove("show");
       }, 2000);
    }

    function blockCheck(event) {
       if (!checkFields()) {
          event.preventDefault();
          showTooltip();
       }
    }

    domisiliCheckbox.addEventListener("click", blockCheck);
 });

