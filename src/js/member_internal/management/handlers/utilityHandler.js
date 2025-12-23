// Utility Handler for Member Management
import { api } from "../services/api.js";
import { animationHandler } from "./animationHandler.js";

class UtilityHandler {
  constructor() {
    this.bindEvents();
  }

  bindEvents() {
    // Global event delegation for utility functions
    document.addEventListener("click", (e) => {
      // Edit member
      if (e.target.closest("[data-edit-member]")) {
        const memberId = e.target
          .closest("[data-edit-member]")
          .getAttribute("data-edit-member");
        this.editMember(memberId);
      }

      // Delete member
      if (e.target.closest("[data-delete-member]")) {
        const element = e.target.closest("[data-delete-member]");
        const memberId = element.getAttribute("data-delete-member");
        const memberName =
          element.getAttribute("data-member-name") || "member ini";
        this.deleteMember(memberId, memberName);
      }

      // Export functions
      if (e.target.closest("[data-export]")) {
        const exportType = e.target
          .closest("[data-export]")
          .getAttribute("data-export");
        this.handleExport(exportType);
      }
    });
  }

  async editMember(memberId) {
    try {
      // Fetch member details
      const memberData = await api.getMemberDetail(memberId);

      if (!memberData.success) {
        throw new Error(memberData.message || "Gagal memuat data member");
      }

      const member = memberData.data;

      const { value: formValues } = await Swal.fire({
        title: "Edit Member",
        html: `
                    <div class="space-y-4 text-left">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" id="editName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="${
                              member.nama || ""
                            }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="editEmail" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="${
                              member.email || ""
                            }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor HP</label>
                            <input type="tel" id="editPhone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="${
                              member.no_hp || ""
                            }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="editStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active" ${
                                  member.status === "active" ? "selected" : ""
                                }>Aktif</option>
                                <option value="inactive" ${
                                  member.status === "inactive" ? "selected" : ""
                                }>Tidak Aktif</option>
                                <option value="suspended" ${
                                  member.status === "suspended"
                                    ? "selected"
                                    : ""
                                }>Suspended</option>
                                <option value="pending" ${
                                  member.status === "pending" ? "selected" : ""
                                }>Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                            <select id="editBranch" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="jakarta-pusat" ${
                                  member.cabang === "jakarta-pusat"
                                    ? "selected"
                                    : ""
                                }>Jakarta Pusat</option>
                                <option value="jakarta-selatan" ${
                                  member.cabang === "jakarta-selatan"
                                    ? "selected"
                                    : ""
                                }>Jakarta Selatan</option>
                                <option value="bandung" ${
                                  member.cabang === "bandung" ? "selected" : ""
                                }>Bandung</option>
                                <option value="surabaya" ${
                                  member.cabang === "surabaya" ? "selected" : ""
                                }>Surabaya</option>
                            </select>
                        </div>
                    </div>
                `,
        showCancelButton: true,
        confirmButtonColor: "#3b82f6",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Simpan Perubahan",
        cancelButtonText: "Batal",
        preConfirm: () => {
          const name = document.getElementById("editName").value;
          const email = document.getElementById("editEmail").value;
          const phone = document.getElementById("editPhone").value;
          const status = document.getElementById("editStatus").value;
          const branch = document.getElementById("editBranch").value;

          if (!name || !email || !phone) {
            Swal.showValidationMessage("Semua field harus diisi");
            return false;
          }

          return {
            nama: name,
            email: email,
            no_hp: phone,
            status: status,
            cabang: branch,
          };
        },
      });

      if (formValues) {
        // Update member via API
        const updateResponse = await api.updateMember(memberId, formValues);

        if (updateResponse.success) {
          Swal.fire({
            title: "Berhasil!",
            text: "Data member berhasil diperbarui.",
            icon: "success",
            confirmButtonColor: "#3b82f6",
          });

          // Refresh member list
          if (typeof window.refreshMemberList === "function") {
            window.refreshMemberList();
          }
        } else {
          throw new Error(updateResponse.message || "Gagal memperbarui member");
        }
      }
    } catch (error) {
      console.error("Error editing member:", error);
      Swal.fire({
        title: "Error!",
        text: error.message,
        icon: "error",
        confirmButtonColor: "#ef4444",
      });
    }
  }

  // Method to delete member
  async deleteMember(memberId, memberName) {
    try {
      const result = await Swal.fire({
        title: "PERINGATAN!",
        text: `Apakah Anda yakin ingin menghapus member "${memberName}"? Tindakan ini tidak dapat dibatalkan!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc2626",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
        footer: "Data member dan riwayat transaksi akan hilang permanen",
      });

      if (result.isConfirmed) {
        // Double confirmation for delete action
        const finalConfirm = await Swal.fire({
          title: "Konfirmasi Terakhir",
          text: 'Ketik "HAPUS" untuk mengkonfirmasi penghapusan',
          input: "text",
          inputPlaceholder: "Ketik HAPUS",
          showCancelButton: true,
          confirmButtonColor: "#dc2626",
          cancelButtonColor: "#6b7280",
          confirmButtonText: "Hapus Sekarang",
          cancelButtonText: "Batal",
          inputValidator: (value) => {
            if (value !== "HAPUS") {
              return 'Anda harus mengetik "HAPUS" untuk mengkonfirmasi!';
            }
          },
        });

        if (finalConfirm.isConfirmed) {
          // Show loading
          Swal.fire({
            title: "Menghapus Member...",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });

          // Call API to delete member
          const response = await api.deleteMember(memberId);

          if (response.success) {
            Swal.fire({
              title: "Berhasil!",
              text: "Member berhasil dihapus dari sistem.",
              icon: "success",
              confirmButtonColor: "#3b82f6",
            });

            // Close detail panel and refresh member list
            this.hideMemberDetail();
            if (typeof window.refreshMemberList === "function") {
              window.refreshMemberList();
            }
          } else {
            throw new Error(response.message || "Gagal menghapus member");
          }
        }
      }
    } catch (error) {
      console.error("Error deleting member:", error);
      Swal.fire({
        title: "Error!",
        text: error.message,
        icon: "error",
        confirmButtonColor: "#ef4444",
      });
    }
  }

  async handleExport(exportType) {
    try {
      const result = await Swal.fire({
        title: `Export ${
          exportType === "members" ? "Data Member" : "Activity Log"
        }`,
        text: "Pilih format export yang diinginkan",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3b82f6",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Export Excel",
        cancelButtonText: "Export PDF",
        showDenyButton: true,
        denyButtonText: "Export CSV",
        denyButtonColor: "#059669",
      });

      let format = "";
      if (result.isConfirmed) {
        format = "excel";
      } else if (result.isDenied) {
        format = "csv";
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        format = "pdf";
      } else {
        return; // User cancelled
      }

      // Show loading
      Swal.fire({
        title: "Sedang memproses...",
        text: "Mohon tunggu, data sedang diekspor.",
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading();
        },
      });

      // Call export API
      await this.performExport(exportType, format);

      Swal.fire({
        title: "Berhasil!",
        text: `Data berhasil diekspor ke format ${format.toUpperCase()}.`,
        icon: "success",
        confirmButtonColor: "#3b82f6",
      });
    } catch (error) {
      console.error("Error exporting data:", error);
      Swal.fire({
        title: "Error!",
        text: "Gagal mengekspor data. Silakan coba lagi.",
        icon: "error",
        confirmButtonColor: "#ef4444",
      });
    }
  }

  async performExport(exportType, format) {
    // Simulate export process
    return new Promise((resolve) => {
      setTimeout(() => {
        // In real implementation, this would call the actual export API
        // and potentially trigger file download
        resolve();
      }, 2000);
    });
  }

  // Date range filtering utilities
  toggleDateRangeFilter() {
    const dropdown = document.getElementById("dateRangeDropdown");
    if (dropdown) {
      dropdown.classList.toggle("hidden");

      // Close dropdown when clicking outside
      if (!dropdown.classList.contains("hidden")) {
        document.addEventListener("click", function closeDropdown(e) {
          if (
            !e.target.closest("#dateRangeDropdown") &&
            !e.target.closest("#dateRangeButton")
          ) {
            dropdown.classList.add("hidden");
            document.removeEventListener("click", closeDropdown);
          }
        });
      }
    }
  }

  setDatePreset(preset) {
    const today = new Date();
    const dateFrom = document.getElementById("dateFrom");
    const dateTo = document.getElementById("dateTo");

    if (!dateFrom || !dateTo) return;

    let fromDate = new Date();

    switch (preset) {
      case "today":
        fromDate = new Date();
        break;
      case "week":
        fromDate.setDate(today.getDate() - 7);
        break;
      case "month":
        fromDate.setDate(today.getDate() - 30);
        break;
      case "year":
        fromDate.setFullYear(today.getFullYear() - 1);
        break;
    }

    dateFrom.value = fromDate.toISOString().split("T")[0];
    dateTo.value = today.toISOString().split("T")[0];
  }

  clearDateRange() {
    const dateFrom = document.getElementById("dateFrom");
    const dateTo = document.getElementById("dateTo");
    const filterType = document.getElementById("dateFilterType");

    if (dateFrom) dateFrom.value = "";
    if (dateTo) dateTo.value = "";
    if (filterType) filterType.value = "join";

    // Reset button appearance
    const button = document.getElementById("dateRangeButton");
    if (button) {
      button.innerHTML =
        '<i class="fas fa-calendar-alt"></i><span class="hidden sm:inline">Tanggal</span>';
      button.classList.remove("bg-green-500", "hover:bg-green-600");
      button.classList.add("bg-white/20", "hover:bg-white/30");
    }
  }

  applyDateRange() {
    const dateFrom = document.getElementById("dateFrom");
    const dateTo = document.getElementById("dateTo");
    const filterType = document.getElementById("dateFilterType");

    if (!dateFrom || !dateTo) return;

    if (dateFrom.value && dateTo.value) {
      // Close dropdown
      const dropdown = document.getElementById("dateRangeDropdown");
      if (dropdown) {
        dropdown.classList.add("hidden");
      }

      // Update button text to show active filter
      const button = document.getElementById("dateRangeButton");
      if (button) {
        button.innerHTML =
          '<i class="fas fa-calendar-alt"></i><span class="hidden sm:inline">Filtered</span>';
        button.classList.add("bg-green-500", "hover:bg-green-600");
        button.classList.remove("bg-white/20", "hover:bg-white/30");
      }

      // In real implementation, trigger data refresh with date filter
      if (typeof window.refreshMemberList === "function") {
        window.refreshMemberList();
      }
    } else {
      animationHandler.showNotification(
        "Silakan pilih rentang tanggal terlebih dahulu",
        "warning"
      );
    }
  }
}

// Export instance
export const utilityHandler = new UtilityHandler();

// Global functions for HTML onclick events
window.editMember = (memberId) => utilityHandler.editMember(memberId);
window.deleteMember = (memberId, memberName) =>
  utilityHandler.deleteMember(memberId, memberName);
window.exportMemberData = () => utilityHandler.handleExport("members");
window.exportActivityLog = () => utilityHandler.handleExport("activity");
window.toggleDateRangeFilter = () => utilityHandler.toggleDateRangeFilter();
window.setDatePreset = (preset) => utilityHandler.setDatePreset(preset);
window.clearDateRange = () => utilityHandler.clearDateRange();
window.applyDateRange = () => utilityHandler.applyDateRange();
