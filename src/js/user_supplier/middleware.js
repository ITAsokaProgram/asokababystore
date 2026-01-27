document.addEventListener("DOMContentLoaded", () => {
    const token = getCookie("supplier_token");
    if (!token) {
        Swal.fire({
            icon: 'warning',
            title: 'Akses Ditolak',
            text: 'Silahkan login sebagai supplier terlebih dahulu',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "/supplier_login.php";
        });
        return;
    }
});
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
}
function logoutSupplier() {
    document.cookie = "supplier_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    localStorage.removeItem("supplier_token");
    localStorage.removeItem("supplier_data");
    window.location.href = "/supplier_login.php";
}