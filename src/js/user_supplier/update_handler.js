document.addEventListener("DOMContentLoaded", async () => {
    const form = document.getElementById("formUpdate");
    const btn = document.getElementById("btn-submit");
    const idInput = document.getElementById("kode");
    const loadingOverlay = document.getElementById("form-loading");

    // 1. Load Data
    try {
        const id = idInput.value;
        const response = await fetch(`/src/api/user_supplier/get_detail.php?id=${id}`);
        const data = await response.json();

        if (data.error) throw new Error(data.error);

        // Isi Form
        document.getElementById("nama").value = data.nama || "";
        document.getElementById("email").value = data.email || "";
        document.getElementById("no_telpon").value = data.no_telpon || "";
        document.getElementById("wilayah").value = data.wilayah || "";

        // Hide loading
        loadingOverlay.classList.add("hidden");
    } catch (error) {
        Swal.fire("Error", "Gagal mengambil data: " + error.message, "error").then(() => {
            window.location.href = "index.php";
        });
    }

    // 2. Submit Update
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

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