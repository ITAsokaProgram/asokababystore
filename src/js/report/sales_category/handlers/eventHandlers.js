/**
 * @fileoverview Event Handlers untuk laporan penjualan kategori
 * @description Mengelola semua event listeners dan user interactions
 */

import { ELEMENT_IDS } from "../config/constants.js";
import { formatCurrency } from "../utils/formatters.js";
import salesCategoryState from "../utils/state.js";
import salesCategoryAPI from "../services/api.js";
import branchService from "../services/branchService.js";
import chartManager from "../components/chartManager.js";
import tableManager from "../components/tableManager.js";
import uiManager from "../components/uiManager.js";

/**
 * Class untuk mengelola event handlers
 */
class EventHandlers {
  constructor() {
    this.isInitialized = false;
  }

  /**
   * Initialize semua event handlers
   * @returns {boolean} Success status
   */
  initialize() {
    try {
      this._bindBranchChangeHandler();
      this._bindButtonHandlers();
      this._bindSortingHandlers();
      this._bindSidebarHandlers();
      this._bindProfileHandlers();

      this.isInitialized = true;
      return true;
    } catch (error) {
      console.error("Failed to initialize event handlers:", error);
      return false;
    }
  }

  /**
   * Bind handler untuk perubahan cabang
   * @private
   */
  _bindBranchChangeHandler() {
    $("#cabang").on("change", async (e) => {
      const selectedBranch = $(e.target).val();

      try {
        await salesCategoryState.setStoreCode(selectedBranch);
      } catch (error) {
        console.error("Failed to set store code:", error);
        uiManager.showError("Error", "Gagal mengatur kode cabang");
      }
    });

    // Trigger initial change setelah branch data loaded
    this._initializeBranchDropdown();
  }

  /**
   * Initialize branch dropdown dengan data dari API
   * @private
   */
  async _initializeBranchDropdown() {
        try {
            uiManager.showLoading("Memuat data cabang...");
            const options = await branchService.getSelectOptions(true);
            const $cabangSelect = $("#cabang");

            if ($cabangSelect.length > 0) {
                $cabangSelect.empty();
                
                // Render Options
                options.forEach((option) => {
                    // Kita simpan storeCode asli di atribut data agar mudah diambil
                    const storeCodeVal = option.isAll ? 'ALL' : option.storeCode;
                    $cabangSelect.append(
                        `<option value="${option.value}" data-store="${storeCodeVal}">${option.text}</option>`
                    );
                });

                // Inisialisasi Select2
                $cabangSelect.select2({
                    placeholder: "Pilih Cabang (Bisa lebih dari 1)",
                    allowClear: true,
                    width: '100%',
                    closeOnSelect: false 
                });

                // Logic Khusus: Jika pilih "SEMUA CABANG", hapus pilihan lain
                $cabangSelect.on('select2:select', function (e) {
                    const selectedValue = e.params.data.id;
                    if (selectedValue === 'SEMUA CABANG') {
                        // Jika klik semua, reset dan set hanya SEMUA
                        $cabangSelect.val(['SEMUA CABANG']).trigger('change');
                    } else {
                        // Jika klik spesifik, pastikan SEMUA tidak terpilih
                        const currentVal = $cabangSelect.val() || [];
                        if (currentVal.includes('SEMUA CABANG')) {
                            const newVal = currentVal.filter(v => v !== 'SEMUA CABANG');
                            $cabangSelect.val(newVal).trigger('change');
                        }
                    }
                });
            }
        } catch (error) {
            console.error("Failed to initialize branch dropdown:", error);
            uiManager.showError("Error", "Gagal memuat data cabang");
        } finally {
            uiManager.hideLoading();
        }
    }

  /**
   * Bind handler untuk tombol-tombol utama
   * @private
   */
  _bindButtonHandlers() {
    // Send button handler
    const btnSend = document.getElementById(ELEMENT_IDS.BTN_SEND);
    if (btnSend) {
      btnSend.addEventListener("click", async (e) => {
        e.preventDefault();
        await this._handleSendButtonClick();
      });
    }

    // Back button handler
    const btnBack = document.getElementById(ELEMENT_IDS.BTN_BACK);
    if (btnBack) {
      btnBack.addEventListener("click", (e) => {
        e.preventDefault();
        this._handleBackButtonClick();
      });
    }
  }

  /**
   * Handle send button click
   * @private
   */
  async _handleSendButtonClick() {
        const startDate = uiManager.getValue(ELEMENT_IDS.DATE_START);
        const endDate = uiManager.getValue(ELEMENT_IDS.DATE_END);
        
        // Ambil value dari Select2 (ini akan return Array)
        let selectedBranchNames = $("#cabang").val();

        // Validasi
        if (!selectedBranchNames || selectedBranchNames.length === 0) {
            uiManager.showError("Error", "Silahkan pilih minimal satu cabang");
            return;
        }

        if (!startDate || !endDate) {
            uiManager.showError("Error", "Silahkan isi tanggal periode");
            return;
        }

        try {
            uiManager.showLoading("Memuat data kategori...");

            // LOGIKA PENTING: Konversi Nama Cabang ke Kode Store
            let storeCodesToSend = [];
            
            // Cek apakah "SEMUA CABANG" dipilih
            if (selectedBranchNames.includes("SEMUA CABANG")) {
                // Ambil semua kode toko dari service
                const allStoreMap = await branchService.getStoreCodes();
                storeCodesToSend = Object.values(allStoreMap);
            } else {
                // Ambil kode toko spesifik berdasarkan nama cabang yang dipilih
                const allStoreMap = await branchService.getStoreCodes();
                storeCodesToSend = selectedBranchNames.map(name => allStoreMap[name]).filter(code => code);
            }

            // Simpan ke state (dalam bentuk array)
            await salesCategoryState.setStoreCode(storeCodesToSend);

            const response = await salesCategoryAPI.fetchInitialData({
                storeCode: storeCodesToSend, // Kirim array kode toko
                startDate: startDate,
                endDate: endDate,
                query: "allCate",
            });

            // ... sisa kode sama seperti sebelumnya ...
            if (response.data && response.data.length >= 0) {
                 const processedData = this._processInitialData(response.data);
                 // ... rest of logic
                 chartManager.updateEarlyChart(
                    processedData.labels,
                    processedData.chartData,
                    (params) => this._handleChartClick(params, "category")
                 );
                 uiManager.setEarlyMode();
                 salesCategoryState.setFullCache({
                    chartMode: "early",
                    labels: processedData.labels,
                    chartData: processedData.chartData,
                    tableMode: "early",
                    tableData: ["input"],
                 });
            }

        } catch (error) {
            const errorMessage = salesCategoryAPI.handleError(error);
            uiManager.showError("Gagal Memuat Data", errorMessage);
        } finally {
            uiManager.hideLoading();
        }
    }

  /**
   * Handle back button click
   * @private
   */
  _handleBackButtonClick() {
    const previousState = salesCategoryState.restorePreviousState();
    if (!previousState) {
      console.warn("No previous state available");
      return;
    }

    const { chartMode, labels, chartData, tableMode, tableData } =
      previousState;

    try {
      // Restore chart berdasarkan mode
      switch (chartMode) {
        case "early":
          chartManager.updateEarlyChart(labels, chartData, (params) =>
            this._handleChartClick(params, "category")
          );
          uiManager.setEarlyMode();
          break;
        case "category":
          const sortBy = uiManager.getValue("sort-by") || "total_qty";
          chartManager.updateCategoryChart(
            labels,
            chartData,
            sortBy,
            (params) => this._handleChartClick(params, "detail")
          );
          uiManager.setCategoryMode();
          break;
        case "detail":
          const sortBy1 = uiManager.getValue("sort-by1") || "total_qty";
          chartManager.updateDetailChart(labels, chartData, sortBy1);
          uiManager.setDetailMode();
          break;
      }

      // Restore table
      if (
        tableData &&
        Array.isArray(tableData) &&
        tableData.length > 0 &&
        tableData[0] !== "input"
      ) {
        tableManager.renderTable(tableData);
        tableManager.show();
      } else {
        tableManager.hide();
      }
    } catch (error) {
      console.error("Failed to restore previous state:", error);
      uiManager.showError("Error", "Gagal mengembalikan ke state sebelumnya");
    }
  }

  /**
   * Handle chart click events
   * @private
   * @param {Object} params - Click event parameters
   * @param {string} nextMode - Next chart mode to load
   */
  async _handleChartClick(params, nextMode) {
    const startDate = uiManager.getValue(ELEMENT_IDS.DATE_START);
    const endDate = uiManager.getValue(ELEMENT_IDS.DATE_END);
    const storeCode = salesCategoryState.getStoreCode();

    try {
      uiManager.showLoading();

      if (nextMode === "category") {
        await this._loadCategoryData(
          startDate,
          endDate,
          params.name,
          storeCode
        );
      } else if (nextMode === "detail") {
        await this._loadDetailData(
          startDate,
          endDate,
          params.data.kode,
          storeCode,
          params.data.kategori
        );
      }
    } catch (error) {
      const errorMessage = salesCategoryAPI.handleError(error);
      uiManager.showError("Gagal Memuat Data", errorMessage);
    } finally {
      uiManager.hideLoading();
    }
  }

  /**
   * Load category data
   * @private
   */
  async _loadCategoryData(startDate, endDate, category, storeCode) {
    const filter = uiManager.getValue("sort-by") || "total_qty";
    const btnExcel = document.getElementById(ELEMENT_IDS.BTN_DATASET);
    const btnPDF = document.getElementById(ELEMENT_IDS.BTN_PDF);
    const response = await salesCategoryAPI.fetchCategoryData({
      storeCode: storeCode,
      startDate: startDate,
      endDate: endDate,
      query: category,
      filter: filter,
    });
    btnExcel.classList.add("hidden");
    btnPDF.classList.add("hidden");
    if (response.data && response.data.length > 0) {
      const processedData = this._processCategoryData(response.data);

      // Update chart
      chartManager.updateCategoryChart(
        processedData.labels,
        processedData.chartData,
        filter,
        (params) => this._handleChartClick(params, "detail")
      );

      // Update table
      tableManager.renderTable(processedData.tableData);

      // Update UI state
      uiManager.setCategoryMode();

      // Cache state
      salesCategoryState.setFullCache({
        chartMode: "category",
        labels: processedData.labels,
        chartData: processedData.chartData,
        tableMode: "category",
        tableData: processedData.tableData,
      });
    }
  }

  /**
   * Load detail data
   * @private
   */
  async _loadDetailData(startDate, endDate, supplierCode, storeCode, category) {
    const filter = uiManager.getValue("sort-by1") || "total_qty";
    const btnDataSet = document.getElementById(ELEMENT_IDS.BTN_DATASET);
    const btnPDF = document.getElementById(ELEMENT_IDS.BTN_PDF);

    btnDataSet.dataset.supplier = supplierCode;
    btnDataSet.dataset.category = category;
    btnDataSet.dataset.kodeStore = storeCode;
    const response = await salesCategoryAPI.fetchSupplierDetailData({
      storeCode: storeCode,
      startDate: startDate,
      endDate: endDate,
      supplierCode: supplierCode,
      category: category,
      filter: filter,
    });
    btnDataSet.classList.remove("hidden");
    btnPDF.classList.remove("hidden");
    if (response.data && response.data.length > 0) {
      const processedData = this._processDetailData(
        response.data,
        response.supplierTable
      );

      // Update chart
      chartManager.updateDetailChart(
        processedData.labels,
        processedData.chartData,
        filter
      );

      // Update table
      tableManager.renderTable(processedData.tableData);

      // Update UI state
      uiManager.setDetailMode();

      // Cache state
      salesCategoryState.setFullCache({
        chartMode: "detail",
        labels: processedData.labels,
        chartData: processedData.chartData,
        tableMode: "detail",
        tableData: processedData.tableData,
      });
    }
  }

  /**
   * Bind sorting handlers
   * @private
   */
  _bindSortingHandlers() {
    // Sort handler untuk category mode
    $("#sort-by").on("change", (e) => {
      this._handleCategorySorting($(e.target).val());
    });

    // Sort handler untuk detail mode
    $("#sort-by1").on("change", (e) => {
      this._handleDetailSorting($(e.target).val());
    });
  }

  /**
   * Handle category sorting
   * @private
   * @param {string} sortBy - Sort method
   */
  _handleCategorySorting(sortBy) {
    const cached = salesCategoryState.getFullCache();
    if (!cached || !cached.tableData || !cached.chartData) return;

    // Sort table data
    const sortedTableData = [...cached.tableData].sort((a, b) => {
      if (sortBy === "total_qty") {
        return parseFloat(b.qty || 0) - parseFloat(a.qty || 0);
      } else if (sortBy === "total") {
        const aTotal =
          typeof a.total === "string"
            ? parseFloat(a.total.replace(/[^0-9,-]+/g, "").replace(",", "."))
            : a.total;
        const bTotal =
          typeof b.total === "string"
            ? parseFloat(b.total.replace(/[^0-9,-]+/g, "").replace(",", "."))
            : b.total;
        return (bTotal || 0) - (aTotal || 0);
      }
      return 0;
    });

    // Sort chart data
    const sortedChartData = [...cached.chartData].sort((a, b) => {
      if (sortBy === "total_qty") {
        return parseFloat(b.persen_qty || 0) - parseFloat(a.persen_qty || 0);
      } else if (sortBy === "total") {
        return parseFloat(b.persen_rp || 0) - parseFloat(a.persen_rp || 0);
      }
      return 0;
    });

    const sortedLabels = sortedChartData.map(
      (item) => item.nama_supplier || item.kode || ""
    );

    // Update displays
    tableManager.renderTable(sortedTableData);
    chartManager.updateCategoryChart(
      sortedLabels,
      sortedChartData,
      sortBy,
      (params) => this._handleChartClick(params, "detail")
    );
  }

  /**
   * Handle detail sorting
   * @private
   * @param {string} sortBy - Sort method
   */
  _handleDetailSorting(sortBy) {
    const cached = salesCategoryState.getFullCache();
    if (!cached || !cached.tableData || !cached.chartData) return;

    // Sort table data
    const sortedTableData = [...cached.tableData].sort((a, b) => {
      if (sortBy === "total_qty") {
        return parseFloat(b.Qty || 0) - parseFloat(a.Qty || 0);
      } else if (sortBy === "total") {
        const aTotal =
          typeof a.Total === "string"
            ? parseFloat(a.Total.replace(/[^0-9,-]+/g, "").replace(",", "."))
            : a.Total;
        const bTotal =
          typeof b.Total === "string"
            ? parseFloat(b.Total.replace(/[^0-9,-]+/g, "").replace(",", "."))
            : b.Total;
        return (bTotal || 0) - (aTotal || 0);
      }
      return 0;
    });

    // Update table (chart data tetap sama untuk detail)
    tableManager.renderTable(sortedTableData);
    chartManager.updateDetailChart(cached.labels, cached.chartData, sortBy);
  }

  /**
   * Process initial data response
   * @private
   * @param {Array} data - Raw data from API
   * @returns {Object} Processed data
   */
  _processInitialData(data) {
    const labels = data.map((item) => item.type_kategori);
    const chartData = data.map((item) => ({
      value: item.total_qty,
      persentase: item.persentase,
      uang: formatCurrency(item.total),
    }));

    return { labels, chartData };
  }

  /**
   * Process category data response
   * @private
   * @param {Array} data - Raw data from API
   * @returns {Object} Processed data
   */
  _processCategoryData(data) {
    const tableData = data.map((item) => ({
      nama_supplier: item.nama_supp,
      kode_supplier: item.kode_supp,
      qty: item.total_qty,
      kategori: item.type_kategori,
      total: formatCurrency(item.total),
    }));

    const labels = data.map((item) => item.nama_supp);
    const chartData = data.map((item) => ({
      kategori: item.type_kategori,
      kode: item.kode_supp,
      qty: item.total_qty,
      total: formatCurrency(item.total),
      persen_qty: item.persentase,
      persen_rp: item.persentase_rp,
      nama_supplier: item.nama_supp,
    }));

    return { labels, chartData, tableData };
  }

  /**
   * Process detail data response
   * @private
   * @param {Array} data - Raw chart data from API
   * @param {Array} supplierTable - Raw table data from API
   * @returns {Object} Processed data
   */
  _processDetailData(data, supplierTable) {
    const labels = data.map((item) => item.descp);
    const chartData = data.map((item) => ({
      periode: item.periode,
      kategori: item.kategori,
      value: item.total_qty,
      persen_qty: item.persentase,
      persen_rp: item.persentase_rp,
      total: formatCurrency(item.Total),
    }));

    const tableData = supplierTable.map((item) => ({
      Barcode: item.barcode,
      Product: item.nama_barang,
      Qty: item.total_qty,
      Total: formatCurrency(item.total),
    }));

    return { labels, chartData, tableData };
  }

  /**
   * Bind sidebar handlers (existing functionality)
   * @private
   */
  _bindSidebarHandlers() {
    // Toggle sidebar
  }

  /**
   * Handle sidebar toggle
   * @private
   */
  _handleSidebarToggle() {}

  /**
   * Bind profile handlers (existing functionality)
   * @private
   */
  _bindProfileHandlers() {}
}

// Create singleton instance
const eventHandlers = new EventHandlers();

export default eventHandlers;
