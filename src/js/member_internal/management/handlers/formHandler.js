// Form Handler for Member Management
import { el } from "../services/dom.js";
import { api } from "../services/api.js";
import { animationHandler } from "./animationHandler.js";

class FormHandler {
    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        this.bindMemberForm();
        this.bindEmailBlastForm();
        this.bindWhatsAppForm();
        this.bindBulkActivationForm();
        this.bindSearchForms();
    }

    bindMemberForm() {
        const form = document.getElementById('formTambahMember');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleMemberSubmit(form);
            });
        }

        // Real-time validation
        const inputs = form?.querySelectorAll('input, select');
        inputs?.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
    }

    bindEmailBlastForm() {
        const form = document.getElementById('formEmailBlast');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleEmailBlastSubmit(form);
            });
        }

        // Template selection
        const templateSelect = document.getElementById('emailTemplate');
        if (templateSelect) {
            templateSelect.addEventListener('change', (e) => {
                this.loadEmailTemplate(e.target.value);
            });
        }

        // Recipient count update
        const targetAudience = document.getElementById('targetAudience');
        if (targetAudience) {
            targetAudience.addEventListener('change', (e) => {
                this.updateRecipientCount(e.target.value);
            });
        }
    }

    bindWhatsAppForm() {
        const form = document.getElementById('formWhatsApp');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleWhatsAppSubmit(form);
            });
        }

        // Character count for message
        const messageInput = document.getElementById('whatsappMessage');
        if (messageInput) {
            messageInput.addEventListener('input', (e) => {
                this.updateCharacterCount(e.target);
            });
        }
    }

    bindBulkActivationForm() {
        const form = document.getElementById('formBulkActivation');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleBulkActivationSubmit(form);
            });
        }
    }

    bindSearchForms() {
        // Global search functionality
        const searchInputs = document.querySelectorAll('[data-search-target]');
        searchInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                const target = e.target.getAttribute('data-search-target');
                this.handleSearch(e.target.value, target);
            });
        });
    }

    async handleMemberSubmit(form) {
        try {
            // Show loading
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            animationHandler.showLoadingAnimation(submitBtn);

            // Validate form
            if (!this.validateMemberForm(form)) {
                animationHandler.hideLoadingAnimation(submitBtn, originalText);
                return;
            }

            // Prepare form data
            const formData = new FormData(form);
            const memberData = Object.fromEntries(formData.entries());

            // Submit to API
            const response = await api.addMember(memberData);

            if (response.success) {
                animationHandler.showNotification('Member berhasil ditambahkan!', 'success');
                this.resetForm(form);
                // Refresh member list if needed
                if (typeof window.refreshMemberList === 'function') {
                    window.refreshMemberList();
                }
            } else {
                throw new Error(response.message || 'Gagal menambahkan member');
            }

        } catch (error) {
            animationHandler.showNotification(error.message, 'error');
            console.error('Error adding member:', error);
        } finally {
            const submitBtn = form.querySelector('button[type="submit"]');
            animationHandler.hideLoadingAnimation(submitBtn, 'Tambah Member');
        }
    }

    async handleEmailBlastSubmit(form) {
        try {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            animationHandler.showLoadingAnimation(submitBtn);

            // Validate form
            if (!this.validateEmailBlastForm(form)) {
                animationHandler.hideLoadingAnimation(submitBtn, originalText);
                return;
            }

            // Prepare email data
            const formData = new FormData(form);
            const emailData = Object.fromEntries(formData.entries());

            // Submit to API
            const response = await api.sendEmailBlast(emailData);

            if (response.success) {
                animationHandler.showNotification('Email blast berhasil dikirim!', 'success');
                this.resetForm(form);
            } else {
                throw new Error(response.message || 'Gagal mengirim email blast');
            }

        } catch (error) {
            animationHandler.showNotification(error.message, 'error');
            console.error('Error sending email blast:', error);
        } finally {
            const submitBtn = form.querySelector('button[type="submit"]');
            animationHandler.hideLoadingAnimation(submitBtn, 'Kirim Email');
        }
    }

    async handleWhatsAppSubmit(form) {
        try {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            animationHandler.showLoadingAnimation(submitBtn);

            // Validate form
            if (!this.validateWhatsAppForm(form)) {
                animationHandler.hideLoadingAnimation(submitBtn, originalText);
                return;
            }

            // Prepare WhatsApp data
            const formData = new FormData(form);
            const whatsappData = Object.fromEntries(formData.entries());

            // Submit to API
            const response = await api.sendWhatsAppBlast(whatsappData);

            if (response.success) {
                animationHandler.showNotification('WhatsApp blast berhasil dikirim!', 'success');
                this.resetForm(form);
            } else {
                throw new Error(response.message || 'Gagal mengirim WhatsApp blast');
            }

        } catch (error) {
            animationHandler.showNotification(error.message, 'error');
            console.error('Error sending WhatsApp blast:', error);
        } finally {
            const submitBtn = form.querySelector('button[type="submit"]');
            animationHandler.hideLoadingAnimation(submitBtn, 'Kirim WhatsApp');
        }
    }

    async handleBulkActivationSubmit(form) {
        try {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            animationHandler.showLoadingAnimation(submitBtn);

            // Get selected members
            const selectedMembers = Array.from(
                document.querySelectorAll('#modalBulkActivation input[type="checkbox"]:checked:not(#selectAll)')
            ).map(cb => cb.value);

            if (selectedMembers.length === 0) {
                animationHandler.showNotification('Pilih minimal satu member untuk diaktivasi', 'warning');
                animationHandler.hideLoadingAnimation(submitBtn, originalText);
                return;
            }

            // Submit to API
            const response = await api.bulkActivateMembers(selectedMembers);

            if (response.success) {
                animationHandler.showNotification(`${selectedMembers.length} member berhasil diaktivasi!`, 'success');
                this.resetForm(form);
                // Refresh member list
                if (typeof window.refreshMemberList === 'function') {
                    window.refreshMemberList();
                }
            } else {
                throw new Error(response.message || 'Gagal mengaktivasi member');
            }

        } catch (error) {
            animationHandler.showNotification(error.message, 'error');
            console.error('Error bulk activating members:', error);
        } finally {
            const submitBtn = form.querySelector('button[type="submit"]');
            animationHandler.hideLoadingAnimation(submitBtn, 'Aktivasi Member');
        }
    }

    validateMemberForm(form) {
        const requiredFields = ['nama', 'email', 'no_hp', 'cabang'];
        let isValid = true;

        requiredFields.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field || !field.value.trim()) {
                this.showFieldError(field, 'Field ini wajib diisi');
                isValid = false;
            }
        });

        // Email validation
        const emailField = form.querySelector('[name="email"]');
        if (emailField && emailField.value) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailField.value)) {
                this.showFieldError(emailField, 'Format email tidak valid');
                isValid = false;
            }
        }

        // Phone validation
        const phoneField = form.querySelector('[name="no_hp"]');
        if (phoneField && phoneField.value) {
            const phonePattern = /^[\d+\-\s\(\)]+$/;
            if (!phonePattern.test(phoneField.value)) {
                this.showFieldError(phoneField, 'Format nomor HP tidak valid');
                isValid = false;
            }
        }

        return isValid;
    }

    validateEmailBlastForm(form) {
        const requiredFields = ['subject', 'message', 'targetAudience'];
        let isValid = true;

        requiredFields.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field || !field.value.trim()) {
                this.showFieldError(field, 'Field ini wajib diisi');
                isValid = false;
            }
        });

        return isValid;
    }

    validateWhatsAppForm(form) {
        const messageField = form.querySelector('[name="message"]');
        const targetField = form.querySelector('[name="targetAudience"]');

        let isValid = true;

        if (!messageField || !messageField.value.trim()) {
            this.showFieldError(messageField, 'Pesan wajib diisi');
            isValid = false;
        }

        if (!targetField || !targetField.value) {
            this.showFieldError(targetField, 'Target audience wajib dipilih');
            isValid = false;
        }

        // Message length validation
        if (messageField && messageField.value.length > 1000) {
            this.showFieldError(messageField, 'Pesan maksimal 1000 karakter');
            isValid = false;
        }

        return isValid;
    }

    validateField(field) {
        this.clearFieldError(field);

        if (field.hasAttribute('required') && !field.value.trim()) {
            this.showFieldError(field, 'Field ini wajib diisi');
            return false;
        }

        // Specific validations
        if (field.type === 'email' && field.value) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(field.value)) {
                this.showFieldError(field, 'Format email tidak valid');
                return false;
            }
        }

        return true;
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        
        field.classList.add('border-red-500');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-sm mt-1 field-error';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
        
        animationHandler.showErrorAnimation(field);
    }

    clearFieldError(field) {
        field.classList.remove('border-red-500');
        
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    resetForm(form) {
        form.reset();
        
        // Clear all field errors
        const errorDivs = form.querySelectorAll('.field-error');
        errorDivs.forEach(div => div.remove());
        
        const fields = form.querySelectorAll('.border-red-500');
        fields.forEach(field => field.classList.remove('border-red-500'));
    }

    loadEmailTemplate(templateId) {
        if (!templateId) return;

        // Sample templates - in real app, fetch from API
        const templates = {
            'promo': {
                subject: 'Promo Spesial Weekend!',
                content: 'Dapatkan diskon hingga 50% untuk semua produk bunga favorit Anda!'
            },
            'newsletter': {
                subject: 'Newsletter Bulanan - Tips Merawat Tanaman',
                content: 'Pelajari tips terbaru untuk merawat tanaman hias Anda di rumah.'
            },
            'event': {
                subject: 'Undangan Workshop Berkebun',
                content: 'Bergabunglah dengan workshop berkebun gratis kami minggu depan!'
            }
        };

        const template = templates[templateId];
        if (template) {
            document.getElementById('emailSubject').value = template.subject;
            document.getElementById('emailMessage').value = template.content;
        }
    }

    updateRecipientCount(audience) {
        // Sample counts - in real app, fetch from API
        const counts = {
            'all': 1847,
            'active': 1623,
            'inactive': 189,
            'new': 35,
            'vip': 89
        };

        const countElement = document.getElementById('recipientCount');
        if (countElement) {
            const count = counts[audience] || 0;
            countElement.textContent = `${count} penerima`;
        }
    }

    updateCharacterCount(input) {
        const maxLength = 1000;
        const currentLength = input.value.length;
        const countElement = document.getElementById('charCount');
        
        if (countElement) {
            countElement.textContent = `${currentLength}/${maxLength}`;
            
            if (currentLength > maxLength * 0.9) {
                countElement.classList.add('text-red-500');
            } else {
                countElement.classList.remove('text-red-500');
            }
        }
    }

    handleSearch(query, target) {
        const items = document.querySelectorAll(`[data-searchable="${target}"]`);
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            const matches = text.includes(query.toLowerCase());
            item.style.display = matches ? 'block' : 'none';
        });
    }
}

// Export instance
export const formHandler = new FormHandler();
