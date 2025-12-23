document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById('logout-btn');
    if (btn) {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const response = await fetch("/in_logout.php", {
                    method: "POST",
                    credentials: "include"
                });

                if (response.ok) {
                    document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    window.location.href = "/in_login";
                } else {
                    console.error("Logout gagal:", await response.text());
                }
            } catch (error) {
                console.error("Error saat logout:", error);
            }
        });
    }
});
