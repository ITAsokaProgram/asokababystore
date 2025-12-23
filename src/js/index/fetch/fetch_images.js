import getCookie from "../utils/cookies.js";

export const imagePromo = async () => {
    const token = getCookie('customer_token');
    if(!token){
        Swal.fire({
            icon: "error",
            title: "Akses Ditolak",
            text: "Sesi anda sudah berakhir atau belum login"
        }).then(()=>{
            window.location.href = "/log_in"
        })
        return;
    }
    const response = await fetch("/public/slider.json")
    const data = response.json();
    return data;
}

export default imagePromo;