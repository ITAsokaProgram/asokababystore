import { getCookie } from "/src/js/index/utils/cookies.js";

// Helper untuk set text agar tidak error jika element tidak ada
function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text ?? "-";
}

function updateUI(data) {
    // Jika data null atau undefined (dari display.js catch)
    if (!data) {
        const fields = [
            "total_trans", "total_trans_member", "total_trans_non", "cabang-t",
            "trans_tertinggi_total", "trans_tertinggi_member", "trans_tertinggi_non",
            "cabang-tr", "trans_terendah_total", "trans_terendah_member",
            "trans_terendah_non", "top_sales_member", "top_sales_product_member"
        ];
        fields.forEach(field => setText(field, "-"));
        return;
    }

    // Ambil data transaksi tertinggi/terendah dengan safe navigation
    const transTinggi = data.trans_tertinggi?.[0] || {};
    const transRendah = data.trans_terendah?.[0] || {};
    
    // Cari jumlah member per cabang
    const findMember = (cabang) => data.jumlah_member_per_cabang?.find(item => item.cabang === cabang)?.jumlah_member || "0";

    setText("total_trans", data.total_trans?.[0]?.total_transaksi);
    setText("total_trans_member", data.total_trans?.[0]?.member);
    setText("total_trans_non", data.total_trans?.[0]?.non_member);

    setText("cabang-t", transTinggi.cabang ? `${transTinggi.cabang} (${findMember(transTinggi.cabang)} Member)` : "-");
    setText("trans_tertinggi_total", transTinggi.total_transaksi);
    setText("trans_tertinggi_member", transTinggi.member);
    setText("trans_tertinggi_non", transTinggi.non_member);

    setText("cabang-tr", transRendah.cabang ? `${transRendah.cabang} (${findMember(transRendah.cabang)} Member)` : "-");
    setText("trans_terendah_total", transRendah.total_transaksi);
    setText("trans_terendah_member", transRendah.member);
    setText("trans_terendah_non", transRendah.non_member);

    setText("top_sales_member", data.top_sales_by_member?.[0]?.barang);
    setText("top_sales_product_member", data.top_sales_by_product?.[0]?.barang);
}

async function loadInvalidTransaksi() {
    try {
        const token = getCookie("admin_token");
        const headers = {
            "Authorization": "Bearer " + token,
            "Content-Type": "application/json",
        };

        const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];

        // Fetch semua API secara paralel
        const [resInv, resMarg, resAct, resMem] = await Promise.all([
            fetch("/src/api/invalid/view_invalid_top", { headers }).then(r => r.json()).catch(() => ({ data: [], dataRetur: [] })),
            fetch("/src/api/margin/top_margin", { headers }).then(r => r.json()).catch(() => ({ data: [] })),
            fetch("/src/api/customer/get_top_5_activity_cust", { headers }).then(r => r.json()).catch(() => ({ data: [] })),
            fetch(`/src/api/member/product/get_top_member.php?filter_type=custom&start_date=${yesterday}&end_date=${yesterday}`, { headers }).then(r => r.json()).catch(() => ({ success: false }))
        ]);

        // Render masing-masing bagian
        displayInvalidTransaksi(resInv.data || []);
        displayTop5margin(resMarg.data || []);
        displayTop5Retur(resInv.dataRetur || []);
        displayTop5Activity(resAct.data || []);

        // Render Top Member (Gunakan properti 'success' sesuai API Anda)
        if (resMem.success && resMem.data?.[0]) {
            setText("top_member", resMem.data[0].nama_cust);
            setText("top_member_nominal", "Rp " + resMem.data[0].total_penjualan.toLocaleString("id-ID"));
        } else {
            setText("top_member", "-");
            setText("top_member_nominal", "-");
        }

    } catch (error) {
        console.error("Detail Error:", error);
    }
}

function displayInvalidTransaksi(data) {
    const container = document.getElementById("invalid-transaksi-container");
    if (!container) return;
    container.innerHTML = "";

    if (data.length === 0) {
        container.innerHTML = `<div class="p-4 text-center text-xs text-green-600 bg-green-50 rounded-xl border border-green-100">Semua Transaksi Valid</div>`;
        return;
    }

    data.slice(0, 3).forEach(item => {
        const card = document.createElement("div");
        card.className = "cursor-pointer bg-white rounded-xl p-2 shadow-sm border mb-1 transition-transform hover:scale-[1.02]";
        card.onclick = () => window.location.href = "/src/fitur/transaction/top_invalid";
        card.innerHTML = `
            <div class="flex justify-between text-[11px] mb-1">
                <span class="font-bold truncate w-24">${item.kasir}</span>
                <span class="">${item.cabang}</span>
            </div>
            <div class="flex justify-between items-center text-[11px]">
                <span class="text-red-500"><i class="fa-solid fa-ban mr-1"></i>${item.kategori}</span>
                <span class="bg-red-50 text-red-700 px-2 rounded-full font-bold">${item.jml_gagal}</span>
            </div>`;
        container.appendChild(card);
    });
}

function displayTop5margin(data) {
    const container = document.getElementById("top-margin-minus-container");
    if (!container) return;
    container.innerHTML = "";

    if (data.length === 0) {
        container.innerHTML = `<div class="p-2 text-center text-[10px] ">Tidak ada margin minus</div>`;
        return;
    }

    data.slice(0, 3).forEach(item => {
        const div = document.createElement("div");
        div.className = "bg-orange-50 p-2 rounded-xl border border-orange-100 mb-1 text-center cursor-pointer";
        div.onclick = () => window.location.href = "/src/fitur/transaction/top_margin";
        div.innerHTML = `
            <div class="text-[10px] font-bold text-gray-700">${item.cabang}</div>
            <div class="text-[11px] text-orange-600 font-bold">Rp ${Number(item.Margin).toLocaleString("id-ID")}</div>`;
        container.appendChild(div);
    });
}

function displayTop5Retur(data) {
    const container = document.getElementById("top-retur-container");
    if (!container) return;
    container.innerHTML = "";

    if (!data || data.length === 0) {
        container.innerHTML = `<div class="p-2 text-center text-[10px] ">Tidak ada retur</div>`;
        return;
    }

    data.slice(0, 3).forEach(item => {
        const card = document.createElement("div");
        card.className = "bg-purple-50 p-2 rounded-xl border border-purple-100 mb-1 cursor-pointer";
        card.onclick = () => window.location.href = "/src/fitur/transaction/top_retur";
        card.innerHTML = `
            <div class="flex justify-between text-[11px] mb-1">
                <span class="font-bold truncate w-24">${item.kasir}</span>
                <span class="">${item.cabang}</span>
            </div>
            <div class="flex justify-between text-[11px]">
                <span><i class="fa-solid fa-rotate-left text-purple-600 mr-1"></i>Retur</span>
                <span class="font-bold text-purple-700">${item.jml_gagal}</span>
            </div>`;
        container.appendChild(card);
    });
}

function displayTop5Activity(data) {
    const container = document.getElementById("top-activity-container");
    if (!container) return;
    container.innerHTML = "";

    if (!data || data.length === 0) {
        container.innerHTML = `<div class="p-2 text-center text-[10px] ">Tidak ada aktivitas</div>`;
        return;
    }

    data.slice(0, 3).forEach(item => {
        const card = document.createElement("div");
        card.className = "bg-blue-50 p-2 rounded-xl border border-blue-100 mb-1 cursor-pointer";
        card.onclick = () => window.location.href = "/src/fitur/laporan/in_customer";
        card.innerHTML = `
            <div class="flex justify-between text-[11px] mb-1">
                <span class="font-bold truncate w-24">${item.nama_cust}</span>
                <span class="">${item.cabang || '-'}</span>
            </div>
            <div class="flex justify-between text-[11px]">
                <span><i class="fa-solid fa-user text-blue-600 mr-1"></i>Trx</span>
                <span class="font-bold text-blue-700">${item.T_Trans}</span>
            </div>`;
        container.appendChild(card);
    });
}

// Fungsi Review
async function loadReviewData() {
    try {
        const token = getCookie("admin_token");
        const res = await fetch("/src/api/dashboard/get_review_summary", {
            headers: { "Authorization": "Bearer " + token }
        }).then(r => r.json());

        if (res.status === "success" && res.data) {
            setText("avg-rating", res.data.avg_rating);
            setText("total-reviews", res.data.total_reviews);
            setText("pending-count", res.data.pending_count);
            displayFeaturedReview(res.data.featured_review);
        }
    } catch (e) { console.error("Review Error"); }
}

function displayFeaturedReview(review) {
    const container = document.getElementById("featured-review-container");
    if (!container) return;
    if (!review) {
        container.innerHTML = `<div class="text-[10px]  text-center p-2">Belum ada review</div>`;
        return;
    }
    
    let stars = "";
    for (let i = 1; i <= 5; i++) {
        stars += `<i class="fa-solid fa-star ${i <= review.rating ? "text-yellow-400" : "text-gray-200"}"></i>`;
    }

    container.innerHTML = `
        <div class="bg-gray-50 p-2 rounded-xl border border-gray-100 cursor-pointer" onclick="window.location.href='/src/fitur/laporan/in_review_cust'">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-bold">${review.nama_customer}</span>
                <span class="text-xs bg-orange-100 text-orange-600 px-2 rounded-full">${review.review_status}</span>
            </div>
            <div class="text-[10px]">${stars}</div>
        </div>`;
}

// Export fungsi agar dikenali display.js
export { updateUI, loadInvalidTransaksi, loadReviewData };