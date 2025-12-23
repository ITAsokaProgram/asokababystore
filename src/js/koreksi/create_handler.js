document.addEventListener("DOMContentLoaded", () => {
  const inpStore = document.getElementById("kode_store_input"); // Selector Input Store
  const inpKode = document.getElementById("kode_supp");
  const listContainer = document.getElementById("kode_supp_list");
  const inpNama = document.getElementById("nama_supplier");
  let timeoutId = null;

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

  // Tambahan Function Load Stores Create
  async function loadStoresForCreate() {
    try {
      const response = await fetch("/src/api/shared/get_all_store.php");
      const result = await response.json();
      if (result.success) {
        let options = '<option value="">-- Pilih Cabang --</option>';
        result.data.forEach((store) => {
          options += `<option value="${store.Kd_Store}">${store.Nm_Alias}</option>`;
        });
        if (inpStore) inpStore.innerHTML = options;
      }
    } catch (error) {
      console.error("Gagal load store:", error);
      Swal.fire("Error", "Gagal mengambil data cabang", "error");
    }
  }

  async function fetchSuggestions(term) {
    try {
      // Mengarah ke API get_suppliers di folder koreksi
      const res = await fetch(
        `/src/api/koreksi/get_suppliers.php?term=${term}`
      );
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

  const form = document.getElementById("formKoreksi");
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = document.getElementById("btn-submit");
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> menyimpan...';

    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    // Ambil nilai real dari hidden input
    payload.total_koreksi = document.getElementById("total_koreksi").value;

    try {
      const response = await fetch("/src/api/koreksi/insert_koreksi.php", {
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

  // Panggil saat inisialisasi
  loadStoresForCreate();
});
