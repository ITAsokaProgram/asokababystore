/**
 * @fileoverview Table Manager untuk laporan penjualan kategori
 * @description Mengelola DataTables operations dan styling
 */
import { ELEMENT_IDS } from "../config/constants.js";
import { capitalize } from "../utils/formatters.js";
/**
 * Class untuk mengelola table operations
 */
class TableManager {
  constructor() {
    this.currentTable = null;
    this.tableElement = null;
    this.exportEventBound = false; 
  }
  /**
   * Initialize table element
   * @returns {boolean} Success status
   */
  initialize() {
    this.tableElement = document.getElementById(ELEMENT_IDS.DATA_TABLE);
    if (!this.tableElement) {
      console.error("Table element not found");
      return false;
    }
    return true;
  }
  /**
   * Render table dengan data array
   * @param {Array} dataArray - Array data untuk table
   * @param {Object} options - Options untuk customization
   */
  renderTable(dataArray, options = {}) {
    if (!this.initialize()) return;
    if (!Array.isArray(dataArray) || dataArray.length === 0) {
      console.warn("No data to render table");
      this._showEmptyTable();
      return;
    }
    this._destroyExistingTable();
    const columns = this._generateColumns(dataArray[0]);
    const config = this._getTableConfig(dataArray, columns, options);
    try {
      this.currentTable = $(this.tableElement).DataTable(config);
      this._customizeTableLayout();
      this._bindExportButtons();
    } catch (error) {
      console.error("Failed to render table:", error);
    }
  }
  /**
   * Generate columns configuration dari data
   * @private
   * @param {Object} sampleData - Sample data untuk generate columns
   * @returns {Array} Columns configuration
   */
  _generateColumns(sampleData) {
    const columns = [
      {
        title: "No",
        data: null,
        render: (data, type, row, meta) => {
          return meta.row !== undefined ? meta.row + 1 : "-";
        },
        className: "text-center",
        width: "30px",
        orderable: false,
      },
    ];
    Object.keys(sampleData).forEach((key) => {
      const title = this._formatColumnTitle(key);
      columns.push({
        title: title,
        data: key,
        className: this._getColumnClass(key),
        render: (data, type) => {
          if (type === "display" && data != null) {
            return this._formatCellData(data, key);
          }
          return data;
        },
      });
    });
    return columns;
  }
  /**
   * Format column title
   * @private
   * @param {string} key - Column key
   * @returns {string} Formatted title
   */
  _formatColumnTitle(key) {
    if (key.toLowerCase() === "barcode") return "BARCODE";
    return capitalize(key.replace(/_/g, " "));
  }
  /**
   * Get CSS class untuk column berdasarkan key
   * @private
   * @param {string} key - Column key
   * @returns {string} CSS class
   */
  _getColumnClass(key) {
    const numericFields = ["qty", "total", "quantity", "amount", "price"];
    const isNumeric = numericFields.some((field) =>
      key.toLowerCase().includes(field)
    );
    return isNumeric ? "text-right" : "text-left";
  }
  /**
   * Format cell data berdasarkan type
   * @private
   * @param {*} data - Cell data
   * @param {string} key - Column key
   * @returns {*} Formatted data
   */
  _formatCellData(data, key) {
    if (typeof data === "string" && data.startsWith("Rp ")) {
      return data;
    }
    if (typeof data === "number" && key.toLowerCase().includes("total")) {
      return `Rp ${data.toLocaleString("id-ID")}`;
    }
    return data;
  }
  /**
   * Get DataTable configuration
   * @private
   * @param {Array} dataArray - Data array
   * @param {Array} columns - Columns configuration
   * @param {Object} options - Additional options
   * @returns {Object} DataTable configuration
   */
  _getTableConfig(dataArray, columns, options) {
    return {
      data: dataArray,
      columns: columns,
      dom: '<"topbar flex flex-wrap md:flex-nowrap justify-between items-center gap-4 mb-4"lf<"#custom-filters">>t<"bottombar flex justify-between items-center mt-4"ip>',
      responsive: true,
      autoWidth: false,
      scrollX: false,
      pageLength: options.pageLength || 25,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "Semua"],
      ],
      language: {
        search: "Cari:",
        searchPlaceholder: "Ketik untuk mencari...",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
        infoEmpty: "Menampilkan 0 - 0 dari 0 data",
        infoFiltered: "(difilter dari _MAX_ total data)",
        paginate: {
          first: "Pertama",
          last: "Terakhir",
          next: "›",
          previous: "‹",
        },
        emptyTable: "Tidak ada data tersedia",
        zeroRecords: "Tidak ditemukan data yang sesuai",
      },
      columnDefs: [
        {
          targets: "_all",
          createdCell: (td) => {
            td.style.whiteSpace = "normal";
            td.style.wordWrap = "break-word";
            td.style.width = "auto";
            td.style.minWidth = "100px";
          },
        },
      ],
      drawCallback: () => {
        this._stylePaginationButtons();
        this._applyTableStyling();
      },
      ...options,
    };
  }
  /**
   * Customize DataTable layout styling
   * @private
   */
  _customizeTableLayout() {
    const tableId = ELEMENT_IDS.DATA_TABLE;
    const $wrapper = $(`#${tableId}`).closest(".dataTables_wrapper");
    $wrapper
      .find(".dataTables_length label")
      .addClass("text-sm text-gray-600 flex items-center gap-2");
    $wrapper
      .find(".dataTables_length select")
      .addClass(
        "px-2 py-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
      );
    $wrapper
      .find(".dataTables_filter label")
      .addClass("text-sm text-gray-600 flex items-center gap-2");
    $wrapper
      .find(".dataTables_filter input")
      .addClass(
        "px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
      )
      .attr("placeholder", "Cari data...");
  }
  /**
   * Apply table styling
   * @private
   */
  _applyTableStyling() {
    const tableSelector = `#${ELEMENT_IDS.DATA_TABLE}`;
    $(`${tableSelector} thead th`).addClass(
      "bg-pink-500 text-white text-sm font-semibold py-3 px-4 border-b"
    );
    $(`${tableSelector} tbody td`).addClass(
      "text-sm text-gray-700 py-2 px-4 border-b border-gray-200"
    );
    $(`${tableSelector} tbody tr`).addClass(
      "hover:bg-pink-50 hover:scale-[101%] transition-all duration-150 ease-in-out"
    );
    $(`${tableSelector} tbody tr:nth-child(even)`).addClass("bg-gray-50");
  }
  /**
   * Style pagination buttons
   * @private
   */
  _stylePaginationButtons() {
    $(".dataTables_paginate a").addClass(
      "px-3 py-2 mx-1 border rounded-lg text-sm text-gray-700 hover:bg-pink-100 cursor-pointer transition-colors duration-200"
    );
    $(".dataTables_paginate .current")
      .removeClass("text-gray-700 hover:bg-pink-100")
      .addClass(
        "bg-pink-500 text-white font-semibold border-pink-500 hover:bg-pink-600"
      );
    $(".dataTables_paginate .disabled").addClass(
      "opacity-50 cursor-not-allowed hover:bg-transparent"
    );
  }
  /**
   * Bind export buttons (Excel & PDF)
   * @private
   */
  _bindExportButtons() {
    if (!this.currentTable) return;
    const dateStart = $("#date").val() || "";
    const dateEnd = $("#date1").val() || "";
    const store = $("#cabang").val() || "-";
    const filename =
      `Laporan_Penjualan_${store}_${dateStart}_${dateEnd}`.replace(/\s+/g, "_");
    if (this._buttonsContainer) {
      this._buttonsContainer.remove();
      this._buttonsContainer = null;
    }
    const headers = this.currentTable
      .columns()
      .header()
      .to$()
      .map((i, th) => $(th).text().trim())
      .get();
    const isCurrencyHeader = (t) =>
      /(total|amount|harga|price|net|gross)/i.test(t);
    const isQtyHeader = (t) => /(qty|quantity|jumlah)/i.test(t);
    const currencyIdx = headers
      .map((t, i) => (isCurrencyHeader(t) ? i : -1))
      .filter((i) => i > 0); 
    const qtyIdx = headers
      .map((t, i) => (isQtyHeader(t) ? i : -1))
      .filter((i) => i > 0);
    const allNumericIdx = [...new Set([...currencyIdx, ...qtyIdx])];
    const colLetter = (i) => {
      let n = i + 1,
        s = "";
      while (n > 0) {
        const r = (n - 1) % 26;
        s = String.fromCharCode(65 + r) + s;
        n = Math.floor((n - 1) / 26);
      }
      return s;
    };
    const toLetters = (idxs) => idxs.map(colLetter);
    new $.fn.dataTable.Buttons(this.currentTable, {
      buttons: [
        {
          extend: "excelHtml5",
          className: "buttons-excel hidden",
          sheetName: "Laporan",
          exportOptions: {
            columns: ":visible",
            format: {
              body: (data, row, col) => {
                if (allNumericIdx.includes(col)) {
                  const str = (data ?? "").toString();
                  const cleaned = str
                    .replace(/<[^>]*>/g, "")
                    .replace(/\s+/g, "")
                    .replace(/Rp/gi, "")
                    .replace(/\./g, "") 
                    .replace(/,/g, "."); 
                  const num = Number(cleaned);
                  return isNaN(num) ? str : num;
                }
                return typeof data === "string"
                  ? data.replace(/<[^>]*>/g, "")
                  : data;
              },
            },
          },
          filename,
          title: null,
          messageTop: null,
          customize: (xlsx) => {
            const sheet = xlsx.xl.worksheets["sheet1.xml"];
            const styles = xlsx.xl["styles.xml"];
            const $sheet = $(sheet);
            const $styles = $(styles);
            const lastRow = $("sheetData row", sheet).length; 
            const lastColLetter = colLetter(headers.length - 1); 
            let numFmts = $styles.find("numFmts");
            if (numFmts.length === 0) {
              $styles.find("styleSheet").prepend('<numFmts count="0"/>');
              numFmts = $styles.find("numFmts");
            }
            const currentNumFmtCount = parseInt(numFmts.attr("count")) || 0;
            const rupiahFmtId = 300; 
            numFmts.attr("count", currentNumFmtCount + 1);
            numFmts.append(
              `<numFmt numFmtId="${rupiahFmtId}" formatCode="&quot;Rp&quot; #,##0"/>`
            );
            const cellXfs = $styles.find("cellXfs");
            const currentXfCount = parseInt(cellXfs.attr("count")) || 0;
            const rupiahXfId = currentXfCount; 
            cellXfs.attr("count", currentXfCount + 1);
            cellXfs.append(
              `<xf xfId="0" fontId="0" fillId="0" borderId="0" numFmtId="${rupiahFmtId}" applyNumberFormat="1"/>`
            );
            toLetters(currencyIdx).forEach((letter) => {
              $(`row c[r^="${letter}"]`, sheet).each(function () {
                const r = $(this).attr("r");
                if (!/1$/.test(r)) $(this).attr("s", rupiahXfId);
              });
            });
            let sheetViews = $sheet.find("sheetViews");
            if (sheetViews.length === 0) {
              $sheet.find("worksheet").prepend("<sheetViews/>");
              sheetViews = $sheet.find("sheetViews");
            }
            sheetViews.html(
              '<sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView>'
            );
            const colsXml = ["<cols>"];
            headers.forEach((h, i) => {
              const lower = h.toLowerCase();
              let width = 18;
              if (i === 0) width = 6; 
              else if (/barcode|kode/.test(lower)) width = 20;
              else if (/nama|label|desc|produk|kategori/.test(lower))
                width = 32;
              else if (/qty|quantity|jumlah/.test(lower)) width = 10;
              else if (/harga|price|amount|total|net|gross/.test(lower))
                width = 14;
              colsXml.push(
                `<col min="${i + 1}" max="${
                  i + 1
                }" width="${width}" customWidth="1"/>`
              );
            });
            colsXml.push("</cols>");
            $sheet.find("cols").remove(); 
            $sheet.find("sheetData").before(colsXml.join(""));
            $sheet.find("autoFilter").remove();
            $sheet
              .find("worksheet")
              .append(`<autoFilter ref="A1:${lastColLetter}${lastRow}"/>`);
            let mergeCells = $sheet.find("mergeCells");
            if (mergeCells.length === 0) {
              $sheet.find("worksheet").prepend('<mergeCells count="0"/>');
              mergeCells = $sheet.find("mergeCells");
            }
            const totalsRow = lastRow + 1;
            const cells = [];
            cells.push(
              `<c r="A${totalsRow}" t="inlineStr"><is><t>TOTAL</t></is></c>`
            );
            mergeCells.append(`<mergeCell ref="A${totalsRow}:D${totalsRow}"/>`);
            mergeCells.attr("count", parseInt(mergeCells.attr("count")) + 1);
            cells.push(`<c r="B${totalsRow}"/>`);
            cells.push(`<c r="C${totalsRow}"/>`);
            cells.push(`<c r="D${totalsRow}"/>`);
            for (let i = 4; i < headers.length; i++) {
              const letter = colLetter(i);
              if (currencyIdx.includes(i) || qtyIdx.includes(i) || i >= 4) {
                cells.push(
                  `<c r="${letter}${totalsRow}"><f>SUBTOTAL(9,${letter}4:${letter}${lastRow})</f></c>`
                );
              } else {
                cells.push(`<c r="${letter}${totalsRow}"/>`);
              }
            }
            $sheet
              .find("sheetData")
              .append(`<row r="${totalsRow}">${cells.join("")}</row>`);
          },
        },
        {
          extend: "pdfHtml5",
          className: "buttons-pdf hidden",
          exportOptions: { columns: ":visible" },
          filename,
          title: null,
          messageTop: null,
          orientation: "landscape",
          pageSize: "A4",
          customize: (doc) => {
            doc.pageMargins = [28, 60, 28, 40]; 
            doc.defaultStyle.fontSize = 9;
            const title = `Laporan Penjualan Kategori - ${store}`;
            const sub = `Periode: ${dateStart} s/d ${dateEnd}`;
            doc.content.unshift(
              {
                text: title,
                fontSize: 14,
                bold: true,
                alignment: "center",
                margin: [0, 0, 0, 4],
                color: "#111827",
              },
              {
                text: sub,
                fontSize: 11,
                bold: false,
                alignment: "center",
                margin: [0, 0, 0, 12],
                color: "#374151",
              }
            );
            const tableIdx = doc.content.findIndex((n) => n.table);
            if (tableIdx === -1) return; 
            const table = doc.content[tableIdx].table;
            table.widths = headers.map((h, i) => {
              const lower = h.toLowerCase();
              if (i === 0) return 25; 
              if (/barcode|kode/.test(lower)) return 90;
              if (/nama|label|desc|produk|kategori/.test(lower)) return 220;
              if (/qty|quantity|jumlah/.test(lower)) return 50;
              if (/harga|price|amount|total|net|gross/.test(lower)) return 80;
              return "auto";
            });
            const headerRow = table.body[0];
            for (let c = 0; c < headerRow.length; c++) {
              const cell = headerRow[c];
              headerRow[c] =
                typeof cell === "object"
                  ? {
                      ...cell,
                      fillColor: "#EC4899",
                      color: "#FFFFFF",
                      bold: true,
                      alignment: "center",
                    }
                  : {
                      text: cell,
                      fillColor: "#EC4899",
                      color: "#FFFFFF",
                      bold: true,
                      alignment: "center",
                    };
            }
            const totals = new Array(headerRow.length).fill(0);
            for (let r = 1; r < table.body.length; r++) {
              const row = table.body[r];
              for (let c = 0; c < row.length; c++) {
                if (allNumericIdx.includes(c)) {
                  const cell = row[c];
                  const text =
                    typeof cell === "object"
                      ? String(cell.text ?? "")
                      : String(cell ?? "");
                  const cleaned = text
                    .replace(/<[^>]*>/g, "")
                    .replace(/\s+/g, "")
                    .replace(/Rp/gi, "")
                    .replace(/\./g, "")
                    .replace(/,/g, ".");
                  const num = parseFloat(cleaned);
                  if (!isNaN(num)) totals[c] += num;
                  const isCurrency = currencyIdx.includes(c);
                  const formatted = isNaN(num)
                    ? text
                    : isCurrency
                    ? `Rp ${new Intl.NumberFormat("id-ID").format(
                        Math.round(num)
                      )}`
                    : new Intl.NumberFormat("id-ID").format(Math.round(num));
                  row[c] = { text: formatted, alignment: "right" };
                } else {
                  if (typeof row[c] === "string")
                    row[c] = { text: row[c], alignment: "left" };
                }
              }
            }
            const totalRow = headers.map((h, i) => {
              if (i === 0)
                return { text: "TOTAL", bold: true, fillColor: "#FCE7F3" };
              if (allNumericIdx.includes(i)) {
                const val = totals[i];
                const isCurrency = currencyIdx.includes(i);
                const label = isNaN(val)
                  ? ""
                  : isCurrency
                  ? `Rp ${new Intl.NumberFormat("id-ID").format(
                      Math.round(val)
                    )}`
                  : new Intl.NumberFormat("id-ID").format(Math.round(val));
                return {
                  text: label,
                  bold: true,
                  alignment: "right",
                  fillColor: "#FCE7F3",
                };
              }
              return { text: "", fillColor: "#FCE7F3" };
            });
            table.body.push(totalRow);
            doc.content[tableIdx].layout = {
              fillColor: (rowIdx, node) => {
                if (rowIdx === 0) return null; 
                if (rowIdx === node.table.body.length - 1) return "#FCE7F3"; 
                return rowIdx % 2 === 0 ? null : "#FAFAFA";
              },
              hLineColor: () => "#E5E7EB",
              vLineColor: () => "#E5E7EB",
              hLineWidth: () => 0.6,
              vLineWidth: () => 0.6,
            };
            doc.footer = (currentPage, pageCount) => ({
              columns: [
                {
                  text: `Periode: ${dateStart} s/d ${dateEnd}`,
                  alignment: "left",
                  margin: [28, 0, 0, 0],
                },
                {
                  text: `Hal ${currentPage} / ${pageCount}`,
                  alignment: "right",
                  margin: [0, 0, 28, 0],
                },
              ],
              fontSize: 9,
            });
          },
        },
      ],
    });
    const $container = this.currentTable.buttons(0, null).container();
    this._buttonsContainer = $container[0];
    $container.appendTo(document.body);
    if (!this.exportEventBound) {
      document.addEventListener('triggerExcelExport', (e) => {
        const { data, source } = e.detail;
        try {
          Toastify({
            text: `⚙️ Memproses data ${source === 'api' ? 'dari API' : 'dari state'} menjadi Excel...`,
            duration: 2000,
            gravity: "top",
            position: "right",
            backgroundColor: "#f59e0b",
          }).showToast();
          const filterData = {
            start_date: $("#date").val() || "",
            end_date: $("#date1").val() || "",
          };
          this._createTempTableAndExport(data, filterData);
          setTimeout(() => {
            Toastify({
              text: "✅ File Excel berhasil diunduh!",
              duration: 3000,
              gravity: "top",
              position: "right",
              backgroundColor: "#10b981",
            }).showToast();
          }, 1000);
        } catch (error) {
          console.error("Excel export failed:", error);
          Toastify({
            text: `❌ Export gagal: ${error.message}`,
            duration: 5000,
            gravity: "top",
            position: "right",
            backgroundColor: "#ef4444",
          }).showToast();
        }
      });
      this.exportEventBound = true;
    }
    $("#exportExcel").off("click");
    $("#exportPDF").off("click");
    $("#exportExcel")
      .on("click", async (e) => {
        e.preventDefault();
        try {
          const selectedBranch = $("#cabang").val();
          const selectedBranchText = $("#cabang option:selected").text();
          const isAllBranches = selectedBranchText.toUpperCase().includes("SEMUA");
          let exportData;
          if (isAllBranches) {
            const loadingToast = Toastify({
              text: "📊 Mengunduh data untuk export...",
              duration: -1,
              gravity: "top",
              position: "right",
              backgroundColor: "#3b82f6",
              close: false,
            }).showToast();
            const kodeSupp = $("#" + ELEMENT_IDS.BTN_DATASET).data("supplier");
            const kategori = $("#" + ELEMENT_IDS.BTN_DATASET).data("category");
            const kodeStore = $("#" + ELEMENT_IDS.BTN_DATASET).data("kodeStore");
            const filterData = {
              kd_store: String(kodeStore),
              start_date: $("#date").val() || "",
              end_date: $("#date1").val() || "",
              type_kategori: kategori || "",
              kode_supp: kodeSupp || "",
            };
            const response = await fetch(
              "/src/api/export/excel_category_sales.php",
              {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                },
                body: JSON.stringify(filterData),
              }
            );
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();
            if (result.status !== "success") {
              throw new Error(result.message || "Failed to fetch data");
            }
            exportData = result.data;
            loadingToast.hideToast();
          } else {
            if (!this.currentTable) {
              throw new Error("Tidak ada data tabel yang tersedia untuk export");
            }
            const tableData = this.currentTable.data().toArray();
            if (!tableData || tableData.length === 0) {
              throw new Error("Tidak ada data dalam tabel untuk di-export");
            }
            exportData = tableData;
          }
          Toastify({
            text: "⚙️ Memproses data menjadi Excel...",
            duration: 2000,
            gravity: "top",
            position: "right",
            backgroundColor: "#f59e0b",
          }).showToast();
          const filterData = {
            start_date: $("#date").val() || "",
            end_date: $("#date1").val() || "",
          };
          this._createTempTableAndExport(exportData, filterData);
          setTimeout(() => {
            Toastify({
              text: "✅ File Excel berhasil diunduh!",
              duration: 3000,
              gravity: "top",
              position: "right",
              backgroundColor: "#10b981",
            }).showToast();
          }, 1000);
        } catch (error) {
          console.error("Excel export failed:", error);
          Toastify({
            text: `❌ Export gagal: ${error.message}`,
            duration: 5000,
            gravity: "top",
            position: "right",
            backgroundColor: "#ef4444",
          }).showToast();
        }
      });
    $("#exportPDF")
      .on("click", (e) => {
        e.preventDefault();
        this.currentTable.button(".buttons-pdf").trigger();
      });
  }
  /**
   * Create temporary table and trigger Excel export with fresh data
   * @private
   * @param {Array} data - Fresh data from API or current table state
   * @param {Object} filterData - Filter data for filename
   */
  _createTempTableAndExport(data, filterData) {
    if (!data || data.length === 0) {
      throw new Error("Tidak ada data untuk di-export");
    }
    if (!filterData || !filterData.start_date || !filterData.end_date) {
      throw new Error("Filter data tidak valid, export dibatalkan.");
    }
    let processedData;
    const firstItem = data[0];
    const isApiData = firstItem.hasOwnProperty('barcode') || firstItem.hasOwnProperty('nama_barang');
    if (isApiData) {
      const cabangKeys = new Set();
      data.forEach((row) => {
        Object.keys(row).forEach((k) => {
          if (
            ![
              "barcode",
              "nama_barang",
              "type_kategori",
              "total",
              "total_qty",
            ].includes(k)
          ) {
            cabangKeys.add(k);
          }
        });
      });
      processedData = data.map((row) => {
        const newRow = { ...row };
        cabangKeys.forEach((cabang) => {
          if (newRow[cabang] === undefined) {
            newRow[cabang] = 0;
          }
        });
        return newRow;
      });
    } else {
      processedData = data.map(row => ({ ...row }));
    }
    const dataWithRowNumbers = processedData.map((row, index) => ({
      no: index + 1,
      ...row,
    }));
    const tempTableId = "temp-export-table";
    let tempTable = document.getElementById(tempTableId);
    if (tempTable) tempTable.remove();
    tempTable = document.createElement("table");
    tempTable.id = tempTableId;
    tempTable.style.display = "none";
    document.body.appendChild(tempTable);
    const columns = [{ title: "No", data: "no", className: "text-center" }];
    Object.keys(dataWithRowNumbers[0]).forEach((key) => {
      if (key !== "no") {
        const title = this._formatColumnTitle(key);
        columns.push({
          title: title,
          data: key,
          className: this._getColumnClass(key),
        });
      }
    });
    const tempDataTable = $(tempTable).DataTable({
      data: dataWithRowNumbers,
      columns: columns,
      paging: false,
      searching: false,
      info: false,
      ordering: false,
      dom: "t",
      destroy: true,
    });
    const headers = tempDataTable
      .columns()
      .header()
      .to$()
      .map((i, th) => $(th).text().trim())
      .get();
    const isCurrencyHeader = (t) =>
      /(total|amount|harga|price|net|gross)/i.test(t);
    const isQtyHeader = (t) => /(qty|quantity|jumlah)/i.test(t);
    const currencyIdx = headers
      .map((t, i) => (isCurrencyHeader(t) ? i : -1))
      .filter((i) => i > 0);
    const qtyIdx = headers
      .map((t, i) => (isQtyHeader(t) ? i : -1))
      .filter((i) => i > 0);
    const allNumericIdx = [...new Set([...currencyIdx, ...qtyIdx])];
    const selectedBranch = $("#cabang option:selected").text();
    const branchName = selectedBranch.includes("SEMUA") ? "Semua Cabang" : selectedBranch.replace(/\s+/g, "_");
    new $.fn.dataTable.Buttons(tempDataTable, {
      buttons: [
        {
          extend: "excelHtml5",
          className: "buttons-excel-temp",
          sheetName: "Laporan",
          exportOptions: {
            orthogonal: "export",
            format: {
              body: (data, row, col) => {
                if (allNumericIdx.includes(col)) {
                  const str = (data ?? "").toString();
                  const cleaned = str
                    .replace(/<[^>]*>/g, "")
                    .replace(/\s+/g, "")
                    .replace(/Rp/gi, "")
                    .replace(/\./g, "")
                    .replace(/,/g, ".");
                  const num = Number(cleaned);
                  return isNaN(num) ? str : num;
                }
                return typeof data === "string"
                  ? data.replace(/<[^>]*>/g, "")
                  : data;
              },
            },
          },
          filename:
            `Laporan_Penjualan_Category_${branchName}_${filterData.start_date}_${filterData.end_date}`.replace(
              /\s+/g,
              "_"
            ),
          title: null,
          messageTop: null,
        },
      ],
    });
    setTimeout(() => {
      tempDataTable.button(".buttons-excel-temp").trigger();
    }, 100);
    setTimeout(() => {
      tempDataTable.destroy();
      tempTable.remove();
    }, 5000);
  }
  /**
   * Destroy existing table instance
   * @private
   */
  _destroyExistingTable() {
    const tableSelector = `#${ELEMENT_IDS.DATA_TABLE}`;
    if ($.fn.DataTable.isDataTable(tableSelector)) {
      $(tableSelector).DataTable().destroy();
      $(tableSelector).empty();
    }
    $("#exportExcel").off("click");
    $("#exportPDF").off("click");
    if (this._buttonsContainer) {
      $(this._buttonsContainer).remove();
      this._buttonsContainer = null;
    }
    this.currentTable = null;
  }
  /**
   * Show empty table message
   * @private
   */
  _showEmptyTable() {
    if (this.tableElement) {
      this.tableElement.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-table fa-2x mb-3"></i>
                    <p class="text-lg">Tidak ada data untuk ditampilkan</p>
                    <p class="text-sm">Silakan ubah filter atau periode tanggal</p>
                </div>
            `;
    }
  }
  /**
   * Show table container
   */
  show() {
    const wrapperElement = document.getElementById(ELEMENT_IDS.WRAPPER_TABLE);
    if (wrapperElement) {
      wrapperElement.style.display = "block";
    }
  }
  /**
   * Hide table container
   */
  hide() {
    const wrapperElement = document.getElementById(ELEMENT_IDS.WRAPPER_TABLE);
    if (wrapperElement) {
      wrapperElement.style.display = "none";
    }
  }
  /**
   * Get current table instance
   * @returns {Object|null} DataTable instance
   */
  getCurrentTable() {
    return this.currentTable;
  }
  /**
   * Clear table data
   */
  clear() {
    if (this.currentTable) {
      this.currentTable.clear().draw();
    }
  }
  /**
   * Update table data
   * @param {Array} newData - New data array
   */
  updateData(newData) {
    if (this.currentTable && Array.isArray(newData)) {
      this.currentTable.clear();
      this.currentTable.rows.add(newData);
      this.currentTable.draw();
    }
  }
}
const tableManager = new TableManager();
export default tableManager;
