import { renderChartNon } from "./chart.js";
import { renderTableNon } from "./table.js";

export const fetchDataNon = async () => {
    const response = await fetch("/src/api/member/member_non_active", {
        method: "GET",
        headers: { "Content-Type": "application/json" }
    })

    const data = await response.json();
    return data
}

export const loadDataFetchNon = async () => {
    let rataNon = document.getElementById('rataNonaktif');
    let totalNon = document.getElementById('totalNonaktif');
    let belumTrans = document.getElementById('belumTrans');
    let seluruh = document.getElementById('totalSeluruh');
    try {
        showLoading()
        const response = await fetchDataNon();
        if (response && response.data) {
            renderChartNon(response);
            renderTableNon(response,25);
            const avg = Number(response.data.total_rata?.[0]?.avg_inactive_months) || 0;
            const bindNumberTotalNon = Number(response.data.total_non_active?.[0]?.non_active_members ?? '0')
            const bindNumberTotalNonNUll = Number(response.data.total_non_active?.[0]?.belum_pernah_transaksi ?? '0')
            const total = bindNumberTotalNon + bindNumberTotalNonNUll;
            rataNon.textContent = `${Math.round(avg)} Bulan`;
            totalNon.textContent = response.data.total_non_active?.[0]?.non_active_members ?? '0';
            belumTrans.textContent = response.data.total_non_active?.[0]?.belum_pernah_transaksi ?? '0'
            seluruh.textContent = total
        } else {
            console.warn("data tidak ada")
        }
    } catch (error) {
        console.log(error)
    } finally {
        hideLoading();
    }
}
const showLoading = () => {
    document.getElementById('loadingOverlay').classList.remove('hidden');
};
const hideLoading = () => {
    document.getElementById('loadingOverlay').classList.add('hidden');
};
export default { fetchDataNon, loadDataFetchNon };