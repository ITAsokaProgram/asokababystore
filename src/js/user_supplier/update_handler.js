document.addEventListener("DOMContentLoaded", async () => {
    const form = document.getElementById("formUpdate");
    const btn = document.getElementById("btn-submit");
    const idInput = document.getElementById("kode");
    const loadingOverlay = document.getElementById("form-loading");
    const selectWilayahEl = document.getElementById("select-wilayah");
    let tomSelectInstance;

    try {
        // --- 1. Fetch Data Kota ---
        const respCities = await fetch("/src/api/location/get_all_kota.php");
        const jsonResponse = await respCities.json();
        // Ambil array dari property .data
        const cities = jsonResponse.data || [];

        // SAFETY CHECK: Pastikan elemen select ada
        if (selectWilayahEl) {
            selectWilayahEl.innerHTML = "";
            cities.forEach(city => {
                const cityName = city.name || city;
                const option = document.createElement("option");
                option.value = cityName;
                option.text = cityName;
                selectWilayahEl.appendChild(option);
            });

            // --- 2. Init Tom Select ---
            tomSelectInstance = new TomSelect(selectWilayahEl, {
                plugins: ['remove_button', 'caret_position'],
                create: false,
                sortField: { field: "text", direction: "asc" }
            });
        }

        // --- 3. Fetch Detail User Supplier ---
        const id = idInput.value;
        const response = await fetch(`/src/api/user_supplier/get_detail.php?id=${id}`);
        const data = await response.json();

        if (data.error) throw new Error(data.error);

        // Isi form
        document.getElementById("nama").value = data.nama || "";
        document.getElementById("email").value = data.email || "";
        document.getElementById("no_telpon").value = data.no_telpon || "";

        // --- 5. Set Wilayah ---
        // Cek jika ada data wilayah DAN tomSelect sudah ter-init
        if (data.wilayah && tomSelectInstance) {
            const wilayahArray = data.wilayah.split(',').map(s => s.trim());
            tomSelectInstance.setValue(wilayahArray);
        }

        // Hide loading
        if (loadingOverlay) loadingOverlay.classList.add("hidden");

    } catch (error) {
        Swal.fire("Error", "Gagal mengambil data: " + error.message, "error").then(() => {
            window.location.href = "index.php";
        });
    }

    // --- 4. Submit Update ---
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        // Ambil value dari Tom Select
        if (tomSelectInstance) {
            payload.wilayah = tomSelectInstance.getValue().join(", ");
        } else {
            payload.wilayah = "";
        }

        try {
            const response = await fetch("/src/api/user_supplier/update.php", {
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
                    confirmButtonColor: "#db2777"
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
});