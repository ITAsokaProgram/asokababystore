
// DOM elements mapping for member management
export const el = {
    // Summary cards (existing)
    MEMBER_BARU: document.getElementById('memberBaru'),
    TOTAL_MEMBER: document.getElementById('totalMember'),
    MEMBER_NON_AKTIF: document.getElementById('memberNonAktif'),
    MEMBER_AKTIF: document.getElementById('memberAktif'),
    TABLE_BODY: document.getElementById('tableBody'),

    // Additional summary elements
    summaryCards: {
        totalMembers: document.getElementById('totalMembers'),
        activeMembers: document.getElementById('activeMembers'),
        newMembers: document.getElementById('newMembers'),
        totalRevenue: document.getElementById('totalRevenue')
    },

    // Pagination elements
    pagination: {
        container: document.getElementById('pagination'),
        prevButton: document.querySelector('[data-pagination="prev"]'),
        nextButton: document.querySelector('[data-pagination="next"]'),
        pageNumbers: document.getElementById('pageNumbers'),
        currentPage: document.querySelector('[data-current-page]')
    },

    // Table elements
    table: {
        container: document.getElementById('memberTable'),
        tbody: document.querySelector('#memberTable tbody'),
        searchInput: document.getElementById('searchInput'),
        filterButtons: document.querySelectorAll('[data-filter]')
    },

    // Modal elements
    modals: {
        tambahMember: document.getElementById('modalTambahMember'),
        suspendMember: document.getElementById('modalSuspendMember'),
        emailBlast: document.getElementById('modalEmailBlast'),
        whatsAppBlast: document.getElementById('modalWhatsAppBlast'),
        bulkActivation: document.getElementById('modalBulkActivation'),
        activityLog: document.getElementById('modalActivityLog'),
        memberManagement: document.getElementById('modalMemberManagement')
    },

    // Form elements
    forms: {
        tambahMember: document.getElementById('formTambahMember'),
        emailBlast: document.getElementById('formEmailBlast'),
        whatsApp: document.getElementById('formWhatsApp'),
        bulkActivation: document.getElementById('formBulkActivation')
    },

    // Search and filter elements
    filters: {
        memberSearchInput: document.getElementById('memberSearchInput'),
        statusFilter: document.getElementById('statusFilter'),
        branchFilter: document.getElementById('branchFilter'),
        activitySearch: document.getElementById('activitySearch'),
        dateRangeFilter: document.getElementById('dateRangeFilter'),
        activityTypeFilter: document.getElementById('activityTypeFilter')
    },

    // Activity log elements
    activityLog: {
        container: document.getElementById('activityLogContainer'),
        detailPanel: document.getElementById('activityDetailPanel'),
        items: document.querySelectorAll('.activity-item')
    },

    // Bulk activation elements
    bulkActivation: {
        selectAll: document.getElementById('selectAll'),
        memberCheckboxes: document.querySelectorAll('#modalBulkActivation input[type="checkbox"]:not(#selectAll)'),
        selectedCount: document.querySelector('#modalBulkActivation .text-gray-500')
    },

    // Email blast elements
    emailBlast: {
        templateSelect: document.getElementById('emailTemplate'),
        subjectInput: document.getElementById('emailSubject'),
        messageInput: document.getElementById('emailMessage'),
        targetAudience: document.getElementById('targetAudience'),
        recipientCount: document.getElementById('recipientCount')
    },

    // WhatsApp elements
    whatsApp: {
        messageInput: document.getElementById('whatsappMessage'),
        charCount: document.getElementById('charCount'),
        targetAudience: document.getElementById('whatsappTargetAudience')
    },

    // Member management modal elements
    memberManagement: {
        title: document.getElementById('memberModalTitle'),
        subtitle: document.getElementById('memberModalSubtitle'),
        memberItems: document.querySelectorAll('.member-item')
    },

    // Animated elements
    animations: {
        counters: document.querySelectorAll('.animate-counter'),
        statsCards: document.querySelectorAll('.stats-card'),
        tableRows: document.querySelectorAll('.table-row'),
        animatedButtons: document.querySelectorAll('.animated-button'),
        progressBars: document.querySelectorAll('.progress-bar'),
        chartElements: document.querySelectorAll('.chart-element')
    }
};

