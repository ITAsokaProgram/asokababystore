import { cabangHandler } from "./handler/cabangHandler.js";
import { eventHandler } from "./handler/eventHandler.js";
import { tableHandler } from "./handler/tableHandler.js";
import FilterHandler from "./handler/filterHandler.js";
import el from "./services/dom.js";
import { api, sendData } from "./services/api.js";

let filterHandler;

// Initialize pagination events
function initializePagination() {
    if (el.prevBtn) {
        el.prevBtn.addEventListener('click', () => {
            filterHandler.previousPage();
        });
    }

    if (el.nextBtn) {
        el.nextBtn.addEventListener('click', () => {
            filterHandler.nextPage();
        });
    }
}

const init = async () => {
    try {
        // Initialize handlers
        eventHandler.init();
        await cabangHandler.selectCabang(el.cabang);
        await cabangHandler.selectCabang(el.filterCabang);
        
        // Initialize FilterHandler
        filterHandler = new FilterHandler();
        window.filterHandler = filterHandler; // Make it globally accessible
        
        // Initialize pagination
        initializePagination();
        
        // Bind row events for edit and delete buttons
        const bindTableEvents = () => {
            const editButtons = document.querySelectorAll('button[data-id] i.fa-edit');
            const deleteButtons = document.querySelectorAll('button[data-id] i.fa-trash');

            editButtons.forEach(btn => {
                const id = btn.closest('button').dataset.id;
                btn.closest('button').addEventListener('click', () => eventHandler.editData(id));
            });

            deleteButtons.forEach(btn => {
                const id = btn.closest('button').dataset.id;
                btn.closest('button').addEventListener('click', async () => {
                    await eventHandler.deleteData(id);
                    await filterHandler.applyFilters();
                });
            });
        };

        // Override filterHandler's updateTable to rebind events
        const originalUpdateTable = filterHandler.updateTable;
        filterHandler.updateTable = function(data) {
            originalUpdateTable.call(this, data);
            bindTableEvents();
        };

        // Initial load with FilterHandler
        await filterHandler.applyFilters();
    } catch (error) {
        console.error("Failed to initialize:", error);
        tableHandler.renderTable(null); // Show empty state
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat menginisialisasi aplikasi'
        });
    }
};

// Run initialization
init();
