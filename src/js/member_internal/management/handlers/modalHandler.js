// Modal Handler for Member Management
import { el } from "../services/dom.js";

class ModalHandler {
    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        // Close modal events
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
                this.closeAllModals();
            }
        });

        // Bind specific modal events
        this.bindMemberManagementEvents();
        this.bindActivityLogEvents();
        this.bindBulkActivationEvents();
    }

    bindMemberManagementEvents() {
        const statusFilter = document.getElementById('statusFilter');
        const branchFilter = document.getElementById('branchFilter');

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterMembers());
        }
        if (branchFilter) {
            branchFilter.addEventListener('change', () => this.filterMembers());
        }
    }

    bindActivityLogEvents() {
        const searchInput = document.getElementById('activitySearch');
        const dateRangeFilter = document.getElementById('dateRangeFilter');
        const typeFilter = document.getElementById('activityTypeFilter');

        if (searchInput) {
            searchInput.addEventListener('input', () => this.filterActivities());
        }
        if (dateRangeFilter) {
            dateRangeFilter.addEventListener('change', () => this.filterActivities());
        }
        if (typeFilter) {
            typeFilter.addEventListener('change', () => this.filterActivities());
        }
    }

    bindBulkActivationEvents() {
        // Select all functionality
        document.addEventListener('change', (e) => {
            if (e.target.id === 'selectAll') {
                const checkboxes = document.querySelectorAll('#modalBulkActivation input[type="checkbox"]:not(#selectAll)');
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                this.updateSelectedCount();
            } else if (e.target.type === 'checkbox' && e.target.id !== 'selectAll' && e.target.closest('#modalBulkActivation')) {
                this.updateSelectedCount();
            }
        });
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                const modalContent = modal.querySelector('.animate-fade-in-up');
                if (modalContent) {
                    modalContent.style.transform = 'translateY(0)';
                    modalContent.style.opacity = '1';
                }
            }, 10);
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const modalContent = modal.querySelector('.animate-fade-in-up');
            if (modalContent) {
                modalContent.style.transform = 'translateY(20px)';
                modalContent.style.opacity = '0';
            }
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    }

    closeAllModals() {
        const modals = ['modalTambahMember', 'modalSuspendMember', 'modalEmailBlast', 'modalWhatsAppBlast', 'modalBulkActivation', 'modalActivityLog', 'modalMemberManagement'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && !modal.classList.contains('hidden')) {
                this.closeModal(modalId);
            }
        });
    }

    showMemberManagement(filterType) {
        const modal = document.getElementById('modalMemberManagement');
        const title = document.getElementById('memberModalTitle');
        const subtitle = document.getElementById('memberModalSubtitle');
        const statusFilter = document.getElementById('statusFilter');

        // Set modal content based on filter type
        switch (filterType) {
            case 'all':
                title.textContent = 'Semua Member';
                subtitle.textContent = '1,847 member terdaftar';
                statusFilter.value = 'all';
                break;
            case 'active':
                title.textContent = 'Member Aktif';
                subtitle.textContent = '1,623 member aktif';
                statusFilter.value = 'Aktif';
                break;
            case 'inactive':
                title.textContent = 'Member Non-Aktif';
                subtitle.textContent = '189 member non-aktif';
                statusFilter.value = 'Non-Aktif';
                break;
            case 'new':
                title.textContent = 'Member Baru';
                subtitle.textContent = '35 member baru bulan ini';
                statusFilter.value = 'all';
                break;
        }

        // Show modal
        this.showModal('modalMemberManagement');

        // Apply filter
        this.filterMembers();
    }

    filterMembers() {
        const statusFilter = document.getElementById('statusFilter')?.value || 'all';
        const branchFilter = document.getElementById('branchFilter')?.value || 'all';

        const memberItems = document.querySelectorAll('.member-item');

        memberItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            const status = item.getAttribute('data-status');
            const branch = item.getAttribute('data-branch');

            let visible = true;
        

            // Status filter
            if (statusFilter !== 'all' && status !== statusFilter) {
                visible = false;
            }

            // Branch filter
            if (branchFilter !== 'all' && branch !== branchFilter) {
                visible = false;
            }

            item.style.display = visible ? 'block' : 'none';
        });
    }

    filterActivities() {
        const searchTerm = document.getElementById('activitySearch')?.value.toLowerCase() || '';
        const dateRange = document.getElementById('dateRangeFilter')?.value || 'all';
        const typeFilter = document.getElementById('activityTypeFilter')?.value || 'all';

        const activities = document.querySelectorAll('.activity-item');

        activities.forEach(activity => {
            const text = activity.textContent.toLowerCase();
            const type = activity.getAttribute('data-type');
            const date = activity.getAttribute('data-date');

            let visible = true;

            // Text search filter
            if (searchTerm && !text.includes(searchTerm)) {
                visible = false;
            }

            // Type filter
            if (typeFilter !== 'all' && type !== typeFilter) {
                visible = false;
            }

            // Date filter (simplified - in real app, use proper date comparison)
            if (dateRange !== 'all') {
                // Implement date filtering logic here
            }

            activity.style.display = visible ? 'block' : 'none';
        });
    }

    updateSelectedCount() {
        const selectedCheckboxes = document.querySelectorAll('#modalBulkActivation input[type="checkbox"]:checked:not(#selectAll)');
        const countElement = document.querySelector('#modalBulkActivation .text-gray-500');
        if (countElement) {
            countElement.textContent = `${selectedCheckboxes.length} member dipilih`;
        }
    }

    getTypeColor(type) {
        const colors = {
            'member': 'green',
            'email': 'blue',
            'admin': 'orange',
            'whatsapp': 'green',
            'system': 'purple'
        };
        return colors[type] || 'gray';
    }

    getTypeIcon(type) {
        const icons = {
            'member': 'fa-user-plus',
            'email': 'fa-envelope',
            'admin': 'fa-user-slash',
            'whatsapp': 'fab fa-whatsapp',
            'system': 'fa-cog'
        };
        return icons[type] || 'fa-info-circle';
    }
}

// Export instance
export const modalHandler = new ModalHandler();

// Global functions for HTML onclick events
window.showModal = (modalId) => modalHandler.showModal(modalId);
window.closeModal = (modalId) => modalHandler.closeModal(modalId);
window.showMemberManagement = (filterType) => modalHandler.showMemberManagement(filterType);
