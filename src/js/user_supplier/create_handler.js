document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formCreate");
    const btn = document.getElementById("btn-submit");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        // UI Loading
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

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