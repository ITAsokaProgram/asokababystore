import { api } from "../services/api.js";
import getCookie from "../../index/utils/cookies.js";

const populateStoreSelect = (selectElement, stores) => {
    if (!selectElement) return;
    // Simpan opsi pertama ("Pilih Store...")
    const firstOption = selectElement.options[0];
    selectElement.innerHTML = '';
    selectElement.appendChild(firstOption);

    stores.forEach(store => {
        const option = document.createElement('option');
        option.value = store.Kd_Store;
        option.textContent = store.Nm_Alias;
        selectElement.appendChild(option);
    });
};

const initSelectStore = async () => {
    try {
        const token = getCookie("admin_token");
        if (!token) return;

        const stores = await api.getStores(token);

        // Isi select di modal tambah
        const addFormSelect = document.querySelector('#assetForm select[name="kd_store"]');
        populateStoreSelect(addFormSelect, stores);

        // Isi select di modal edit
        const editFormSelect = document.querySelector('#editAssetForm select[name="edit_kd_store"]');
        populateStoreSelect(editFormSelect, stores);

    } catch (error) {
        console.error("Gagal memuat data store:", error);
        const addFormSelect = document.querySelector('#assetForm select[name="kd_store"]');
        const editFormSelect = document.querySelector('#editAssetForm select[name="edit_kd_store"]');
        if(addFormSelect) addFormSelect.options[0].textContent = "Gagal memuat store";
        if(editFormSelect) editFormSelect.options[0].textContent = "Gagal memuat store";
    }
};

export default initSelectStore;