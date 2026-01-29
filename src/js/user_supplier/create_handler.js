document.addEventListener("DOMContentLoaded", async () => {
    const form = document.getElementById("formCreate");
    const btn = document.getElementById("btn-submit");
    const selectWilayahEl = document.getElementById("select-wilayah");
    let tomSelectInstance;

    // --- 1. Load Data Kota & Init Tom Select ---
    try {
        const response = await fetch("/src/api/location/get_all_kota.php");
        const jsonResponse = await response.json(); // Tampung dulu response lengkapnya

        // PERBAIKAN DISINI: Ambil array dari properti .data
        const cities = jsonResponse.data || []; 

        // Kosongkan loading option
        selectWilayahEl.innerHTML = "";

        // Populate Options
        cities.forEach(city => {
            // Sesuai struktur data Anda: { "name": "Kabupaten Aceh Barat", ... }
            const cityName = city.name; 
            
            const option = document.createElement("option");
            option.value = cityName;
            option.text = cityName;
            selectWilayahEl.appendChild(option);
        });

        // Init Plugin
        tomSelectInstance = new TomSelect(selectWilayahEl, {
            plugins: ['remove_button', 'caret_position'],
            create: false,
            sortField: { field: "text", direction: "asc" },
            maxOptions: null
        });

    } catch (error) {
        console.error("Gagal memuat kota:", error);
        selectWilayahEl.innerHTML = '<option value="" disabled>Gagal memuat data kota</option>';
    }

    // --- 2. Handle Submit (TIDAK ADA PERUBAHAN) ---
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        if (tomSelectInstance) {
            const selectedValues = tomSelectInstance.getValue(); 
            payload.wilayah = selectedValues.join(", "); 
        } else {
            payload.wilayah = "";
        }

        try {
            const response = await fetch("/src/api/user_supplier/insert.php", {
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