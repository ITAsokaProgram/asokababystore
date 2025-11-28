function getCookie(name) {
  let match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
  if (match) return match[2];
  return null;
}

const token = getCookie("admin_token") || localStorage.getItem("token");

if (token) {
  fetch("/src/auth/decode_token", {
    method: "GET",
    headers: {
      Authorization: "Bearer " + token,
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        const userNama = data.data.nama;
        const userRole = data.data.role;

        document.getElementById("user-nama").textContent = userNama;
        document.getElementById("user-role").textContent = userRole;
        document.getElementById("user-nama-dropdown").textContent = userNama;
        document.getElementById("user-role-dropdown").textContent = userRole;

        const whatsappLink = document.getElementById("whatsappLink");
        const pajakLink = document.getElementById("pajakLink");

        if (
          whatsappLink &&
          (userNama === "Asfahan Rosyid" || userNama === "Muhammad Ridho")
        ) {
          whatsappLink.style.display = "flex";
        }
        if (
          whatsappLink &&
          (userNama === "Asfahan Rosyid" || userNama === "Muhammad Ridho")
        ) {
          pajakLink.style.display = "flex";
        }
      } else {
        console.error("Gagal mengambil user:", data.message);
      }
    })
    .catch((err) => console.error("Fetch error:", err));
}
