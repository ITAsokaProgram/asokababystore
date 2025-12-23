export const getTopMargin = async (token) => {
    try {
        const response = await fetch("/src/api/margin/top_margin.php", {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
        });
        if(response.status === 200){
          return await response.json();
        } else {
          Toastify({
            text: "Data tidak ditemukan",
            duration: 1000,
            gravity: "top",
            position: "right",
            style: {
              background: "#f87171",
              color: "$fff",
            },
          }).showToast();
        }        
    } catch (error) {
        Toastify({
            text: "Terjadi kesalahan saat mengambil data",
            duration: 1000,
            gravity: "top",
            position: "right",
            style: {
              background: "#f87171",
              color: "$fff",
            },
          }).showToast();
    }
};

export const getDetailMargin = async (store, token) => {
  try {
    const response = await fetch(`/src/api/margin/margin_top_detail?store=${store}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if(response.status === 200){
      return await response.json();
    } else {
      Toastify({
        text: "Data tidak ditemukan",
        duration: 1000,
        gravity: "top",
        position: "right",
        style: {
          background: "#f87171",
          color: "$fff",
        },
      }).showToast();
    }
  } catch (error) {
    Toastify({
      text: "Terjadi kesalahan saat mengambil data",
      duration: 1000,
      gravity: "top",
      position: "right",
      style: {
        background: "#f87171",
        color: "$fff",
      },
    }).showToast();
  }
};

export default { getTopMargin, getDetailMargin };