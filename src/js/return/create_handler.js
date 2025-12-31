document.addEventListener("DOMContentLoaded", () => {
  const inpStore = document.getElementById("kode_store_input"); // Tambahan
  const inpKode = document.getElementById("kode_supp");
  const listContainer = document.getElementById("kode_supp_list");
  const inpNama = document.getElementById("nama_supplier");
  let timeoutId = null;

  // Autocomplete Logic
  inpKode.addEventListener("input", function () {
    const val = this.value;
    if (!val) {
      listContainer.classList.add("hidden");
      return;
    }
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      fetchSuggestions(val);
    }, 300);
  });

  // TAMBAHAN: Load Stores
  async function loadStoresForCreate() {
    try {
      // Helper cookie
      const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(";").shift();
      };
      const token = getCookie("admin_token");

      const response = await fetch("/src/api/cabang/get_kode.php", {
        headers: {
          Accept: "application/json",
          Authorization: "Bearer " + token,
        },
      });

      const result = await response.json();

      if (result.data && result.data.length > 0) {
        let options = '<option value="">-- Pilih Cabang --</option>';
        result.data.forEach((store) => {
          // Mapping: store.store (Kode), store.nama_cabang (Nama)
          options += `<option value="${store.store}">${store.nama_cabang}</option>`;
        });
        if (inpStore) inpStore.innerHTML = options;
      } else {
        if (inpStore)
          inpStore.innerHTML = '<option value="">Gagal memuat / Data Kosong</option>';
      }
    } catch (error) {
      console.error("Gagal load store:", error);
      Swal.fire("Error", "Gagal mengambil data cabang", "error");
    }
  }

  async function fetchSuggestions(term) {
    try {
      // AMBIL NILAI STORE
      const storeVal = inpStore ? inpStore.value : '';

      // Kirim kd_store
      const res = await fetch(`/src/api/return/get_suppliers.php?term=${term}&kd_store=${storeVal}`);

      const data = await res.json();
      listContainer.innerHTML = "";

      if (data.length === 0) {
        listContainer.classList.add("hidden");
        return;
      }

      data.forEach((item) => {
        const div = document.createElement("div");
        div.textContent = item.text;
        div.addEventListener("click", function () {
          inpKode.value = item.id;
          if (item.nama) {
            inpNama.value = item.nama;
          }
          listContainer.classList.add("hidden");
        });
        listContainer.appendChild(div);
      });
      listContainer.classList.remove("hidden");
    } catch (e) {
      console.error("Autocomplete error", e);
    }
  }

  document.addEventListener("click", function (e) {
    if (e.target !== inpKode && e.target.parentNode !== listContainer) {
      listContainer.classList.add("hidden");
    }
  });

  // Submit Form Logic
  const form = document.getElementById("formReturn");
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = document.getElementById("btn-submit");
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> menyimpan...';

    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    // Ambil value numeric dari hidden input (karena yang display diformat rupiah)
    payload.total_return = document.getElementById("total_return").value;

    try {
      const response = await fetch("/src/api/return/insert_return.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const result = await response.json();

      if (result.success) {
        Swal.fire({
          title: "Berhasil!",
          text: result.message,
          icon: "success",
        }).then(() => {
          window.location.href = "index.php";
        });
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      Swal.fire("Gagal", error.message, "error");
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  });

  // Jalankan load stores
  loadStoresForCreate();
});
