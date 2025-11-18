import getCookie from "../../index/utils/cookies.js";

export const fetchTransaction = async ({
  member = "",
  cabang = "",
  no_trans = "",
  filter_type = null,
  filter = null,
  start_date = null,
  end_date = null,
} = {}) => {
  const token = getCookie("admin_token");
  try {
    // --- MODIFIKASI: Gunakan URLSearchParams ---
    const params = new URLSearchParams();
    if (member && cabang) {
      params.append("member", member);
      params.append("cabang", cabang);
    } else if (no_trans) {
      params.append("kode", no_trans);
    } else {
      // No valid parameter
      console.error("fetchTransaction: Parameter tidak valid");
      return null;
    }

    // Tambahkan parameter filter tanggal
    if (filter_type) params.append("filter_type", filter_type);
    if (filter) params.append("filter", filter);
    if (start_date) params.append("start_date", start_date);
    if (end_date) params.append("end_date", end_date);

    let url = `/src/api/member/product/get_transaction?${params.toString()}`;
    // --- AKHIR MODIFIKASI ---

    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (!response.ok) {
      // Optionally, you can throw or return null
      console.error(
        "fetchTransaction: HTTP error",
        response.status,
        response.statusText
      );
      return null;
    }
    return await response.json();
  } catch (error) {
    console.error("fetchTransaction error:", error);
    return null;
  }
};
