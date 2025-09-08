import {renderChart, renderChartFilter } from "./chart.js";
import {renderTable} from "./table.js";
import {storeCode} from "./storeCode.js";
const wrapperFilterSelect = document.getElementById("divSelect");
wrapperFilterSelect.classList.add("hidden");
export const fetchData = async () => {
    const kode_store = storeCode("branch");
    const response = await fetch("/src/api/member/member_active", {
        method: 'POST',
        body: JSON.stringify({ "cabang": kode_store}),
        headers: {
            "Content-Type": "application/json"
        },
    })
    const data = await response.json();
    return data;
}
const fetchDataTrend = async () => {
    const kode_store = storeCode("branch");
    const waktu_trend = document.getElementById('periodeSelect').value
    const response = await fetch("/src/api/member/member_active_trend_filter", {
        method: 'POST',
        body: JSON.stringify({ "cabang": kode_store , "periode" : waktu_trend}),
        headers: {
            "Content-Type": "application/json"
        },
    })
    const data = await response.json();
    return data;
}

export const filterTrend = async () => {
    document.getElementById('periodeSelect').addEventListener("change", async function () {
        const selectedValue = this.value
        showChartLoading();
        if(!selectedValue) return;

        try {
            const data = await fetchDataTrend();
            if(data && data.active_trend){
                renderChartFilter(data)
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Data Tidak Ada",
                    text: "Maaf Data Tidak Ditemukan Silahkan Refresh Halaman Atau Coba Lagi Nanti",
                    showConfirmButton: true,
                })
            }
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Oops, ada error!",
                text: error.message,
                showConfirmButton: true,
            });
        } finally {
            hideChartLoading();
        }
    })
}

export const loadAndRenderData = async () => {
    document.getElementById('branch').addEventListener('change', async function () {
        const selectedValue = this.value;
        showLoading();
        if (!selectedValue) return; // Kalau belum pilih apa-apa, jangan lanjut

        try {
            const data = await fetchData(); // di sini fetchData sudah ambil nilai dari select
            if (data && data.data) {
                renderTable(data);
                renderChart(data);
                document.getElementById("activeMemberCount").textContent = data.data.active_member[0].active_members
                wrapperFilterSelect.classList.remove("hidden");
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Data Tidak Ada",
                    text: "Maaf Data Tidak Ditemukan Silahkan Refresh Halaman Atau Coba Lagi Nanti",
                    showConfirmButton: true,
                });
            }
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Oops, ada error!",
                text: error.message,
                showConfirmButton: true,
            });
        } finally {
            hideLoading();
        }
    });
}


const showLoading = () => {
    document.getElementById('loadingOverlay').classList.remove('hidden');
};
const hideLoading = () => {
    document.getElementById('loadingOverlay').classList.add('hidden');
};

const showChartLoading = () => {
  document.getElementById('chartLoading').classList.remove('hidden');
}

const hideChartLoading = () => {
  document.getElementById('chartLoading').classList.add('hidden');
}
export default { fetchData, loadAndRenderData, filterTrend }