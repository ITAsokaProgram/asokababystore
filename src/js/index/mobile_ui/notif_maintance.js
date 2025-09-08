document.getElementById("profileBtn").addEventListener("click", (e)=>{
    e.preventDefault();
    Swal.fire({
        icon : 'info',
        title: 'Maintenance',
        text : 'Mohon maaf fitur ini sedang dalam pengerjaan',
        confirmButtonText: "Ok"
    })
})