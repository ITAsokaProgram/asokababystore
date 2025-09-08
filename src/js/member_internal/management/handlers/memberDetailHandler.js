// Member Detail Handler for Member Management
import { api } from "../services/api.js";
import { animationHandler } from "./animationHandler.js";

class MemberDetailHandler {
  constructor() {
    this.currentMember = null;
    this.bindEvents();
  }

  bindEvents() {
    // Event delegation for member cards
    document.addEventListener("click", (e) => {
      const memberCard = e.target.closest(".member-item");
      if (memberCard) {
        const memberId =
          memberCard.getAttribute("data-member-id") ||
          this.extractMemberIdFromCard(memberCard);
        this.showMemberDetail(memberId, memberCard);
      }
    });
  }

  // Helper  // Render point history
  renderPointHistory(history) {
    if (!history || !Array.isArray(history) || history.length === 0) {
      return `
        <div class="text-center py-4">
          <i class="fas fa-history text-gray-300 text-2xl mb-2"></i>
          <p class="text-sm text-gray-500">Belum ada riwayat poin</p>
        </div>
      `;
    }

    return history
      .map((item) => {
        const isPenukaran = item.sumber === "Tukar Poin";
        const isPositive = item.poin > 0;

        // Background and text colors based on type
        let bgColor = isPositive ? "bg-green-50" : "bg-red-50";
        let textColor = isPositive ? "text-green-600" : "text-red-600";
        let borderColor = isPositive ? "border-green-100" : "border-red-100";
        let icon = isPositive ? "fa-plus-circle" : "fa-minus-circle";

        // Special case for zero point transactions
        if (item.poin === 0 || item.poin === -0) {
          bgColor = "bg-gray-50";
          textColor = "text-gray-600";
          borderColor = "border-gray-100";
          icon = "fa-circle";
        }

        // Format date
        const date = new Date(item.tanggal);
        const formattedDate = date.toLocaleDateString("id-ID", {
          day: "numeric",
          month: "short",
          year: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });

        return `
        <div class="flex items-center gap-3 p-2 ${bgColor} border ${borderColor} rounded-lg hover:shadow-sm transition-all duration-200">
          <div class="flex-shrink-0">
            <i class="fas ${icon} ${textColor} text-lg"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start">
              <p class="text-sm font-medium text-gray-800">${item.sumber}</p>
              <span class="text-sm font-bold ${textColor}">${
          item.poin > 0 ? "+" : ""
        }${item.poin.toLocaleString("id-ID")}</span>
            </div>
            <p class="text-xs text-gray-500">${formattedDate}</p>
          </div>
        </div>
      `;
      })
      .join("");
  }

  // Helper method to hide member detail panel
  hideMemberDetail() {
    const detailPanel = document.getElementById("member-detail-panel");
    if (detailPanel) {
      animationHandler.slideOutPanel(detailPanel, "right");
    }
  }

  // Helper method to get status badge
  getStatusBadge(status) {
    // Handle all possible status from backend
    if (status === "Aktif") {
      return `
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check-circle"></i>
                    Aktif
                </span>
            `;
    } else if (status === "Non-Aktif") {
      return `
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <i class="fas fa-times-circle"></i>
                    Non-Aktif
                </span>
            `;
    } else if (status === "Member Lama Non-Aktif") {
      return `
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    <i class="fas fa-clock"></i>
                    Member Lama Non-Aktif
                </span>
            `;
    } else {
      return `
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    <i class="fas fa-question-circle"></i>
                    ${status || "Tidak Diketahui"}
                </span>
            `;
    }
  }

  // Helper method to get status text
  getStatusText(status) {
    // Return status exactly as received from backend
    return status || "Tidak Diketahui";
  }

  // Helper method to check if member is inactive
  isInactive(status) {
    // Only "Aktif" is considered active, all others are inactive
    return status !== "Aktif";
  }

  extractMemberIdFromCard(memberCard) {
    // Extract member ID from onclick attributes or data attributes
    const editButton = memberCard.querySelector('[onclick*="editMember"]');
    if (editButton) {
      const onclick = editButton.getAttribute("onclick");
      const match = onclick.match(/editMember\(['"]([^'"]+)['"]\)/);
      return match ? match[1] : null;
    }
    return null;
  }

  async showMemberDetail(memberId, memberCard) {
    try {
      // Highlight selected member card
      this.highlightSelectedCard(memberCard);

      // Show loading in detail panel
      const detailPanel = document.getElementById("memberDetailPanel");
      if (!detailPanel) return;

      this.showLoadingState(detailPanel);

      // Try to fetch data from API first
      try {
        const apiResponse = await api.getMemberDetail(memberId);
        if (apiResponse.success && apiResponse.member) {
          // Map API response to expected format
          const mappedData = this.mapApiResponseToMemberData(apiResponse);
          this.currentMember = apiResponse.member.kode_member;
          // Render with API data
          this.renderMemberDetail(detailPanel, mappedData);
          console.log(this.currentMember);
        } else {
          throw new Error("API response invalid");
        }
      } catch (apiError) {
        console.log("API call failed:", apiError);
        // Fallback: Extract member data from the card for display
        const errorMessage = apiError.message
          ? `API Error: ${apiError.message}`
          : "Tidak dapat memuat data member dari server maupun dari card";
        this.showErrorState(detailPanel, errorMessage);
      }
      this.currentMember = memberId;
    } catch (error) {
      console.error("Error showing member detail:", error);
      this.showErrorState(
        document.getElementById("memberDetailPanel"),
        "Terjadi kesalahan sistem"
      );
    }
  }

  // Map API response to member data format
  mapApiResponseToMemberData(apiResponse) {
    const member = apiResponse.member || {};
    const poinData = apiResponse.poin || {};
    const lastTransaksiArray = apiResponse.last_transaksi || [];
    const lastTransaksi =
      lastTransaksiArray.length > 0 ? lastTransaksiArray[0] : {}; // Take first transaction only
    const statusAktif = apiResponse.status_aktif || "Tidak Aktif"; // Get status from root level

    return {
      kode_member: member.kode_member,
      nama_lengkap: member.nama_lengkap,
      nama: member.nama_lengkap, // Fallback for nama
      jenis_kelamin: member.jenis_kelamin || "Tidak diisi",
      alamat_email: member.alamat_email || "Tidak ada email",
      email: member.alamat_email, // Fallback for email
      kota_domisili: member.kota_domisili || "Tidak diisi",
      tanggal_registrasi: member.tanggal_registrasi,
      tanggal_lahir: member.tanggal_lahir,
      terakhir_update_web: member.terakhir_update_web,
      nama_cabang: member.nama_cabang,
      // Point data from new API structure
      total_poin: poinData.total_poin || 0,
      history_poin: poinData.history_poin || [],
      // Map last transaction data (first transaction only)
      tgl_trans_terakhir: lastTransaksi.tanggal,
      kd_store_terakhir: lastTransaksi.cabang,
      nama_toko: lastTransaksi.toko,
      no_faktur_terakhir: lastTransaksi.no_faktur,
      belanja_terakhir: lastTransaksi.belanja,
      // Store all transactions for history
      riwayat_transaksi: lastTransaksiArray,
      // Use status_aktif from root level of API response
      status_aktif: statusAktif,
      status: statusAktif,
    };
  }

  highlightSelectedCard(selectedCard) {
    // Remove highlight from all cards
    document.querySelectorAll(".member-item").forEach((card) => {
      card.classList.remove("ring-2", "ring-blue-500", "bg-blue-50");
    });

    // Add highlight to selected card
    selectedCard.classList.add("ring-2", "ring-blue-500", "bg-blue-50");
  }

  showLoadingState(detailPanel) {
    detailPanel.innerHTML = `
            <div class="p-6">
                <div class="flex items-center justify-center mt-20">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                </div>
                <p class="text-center text-gray-500 mt-4">Memuat detail member...</p>
            </div>
        `;
  }

  showErrorState(detailPanel, errorMessage = "Gagal memuat detail member") {
    detailPanel.innerHTML = `
            <div class="p-6">
                <div class="text-center text-red-500 mt-20">
                    <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                    <p class="text-sm font-medium mb-2">${errorMessage}</p>
                    <p class="text-xs text-gray-500 mb-4">Silakan coba lagi atau hubungi administrator</p>
                    <button onclick="location.reload()" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200 text-sm">
                        <i class="fas fa-redo mr-2"></i>Muat Ulang
                    </button>
                </div>
            </div>
        `;
  }

  renderMemberDetail(detailPanel, memberData) {
    // Helper function to format date
    const formatDate = (dateString) => {
      if (
        !dateString ||
        dateString === "0000-00-00 00:00:00" ||
        dateString === ""
      )
        return "Tidak tersedia";
      try {
        return new Date(dateString).toLocaleDateString("id-ID");
      } catch (error) {
        return dateString;
      }
    };

    // Helper function to safely get value
    const safeValue = (value) => value || "Tidak tersedia";

    detailPanel.innerHTML = `
            <div class="p-6">
                <!-- Member Profile Header -->
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">
                        ${this.getInitials(
                          memberData.nama_lengkap || memberData.nama
                        )}
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">${safeValue(
                      memberData.nama_lengkap || memberData.nama
                    )}</h3>
                    <p class="text-sm text-gray-600">${safeValue(
                      memberData.alamat_email || memberData.email
                    )}</p>
                    <p class="text-xs text-gray-500 mt-1">Kode Member: ${safeValue(
                      memberData.kode_member || memberData.id
                    )}</p>
                    <div class="mt-2">
                        ${this.getStatusBadge(
                          memberData.status_aktif || memberData.status
                        )}
                    </div>
                </div>

                <!-- Member Info -->
                <div class="space-y-4">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-user text-blue-500"></i>
                            Informasi Dasar
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Nama Lengkap:</span>
                                <span class="text-sm font-medium">${safeValue(
                                  memberData.nama_lengkap || memberData.nama
                                )}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Jenis Kelamin:</span>
                                <span class="text-sm font-medium">${safeValue(
                                  memberData.jenis_kelamin
                                )}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Email:</span>
                                <span class="text-sm font-medium">${safeValue(
                                  memberData.alamat_email || memberData.email
                                )}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Kota Domisili:</span>
                                <span class="text-sm font-medium">${safeValue(
                                  memberData.kota_domisili
                                )}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Tanggal Lahir:</span>
                                <span class="text-sm font-medium">${formatDate(
                                  memberData.tanggal_lahir
                                )}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Cabang:</span>
                                <span class="text-sm font-medium">${safeValue(
                                  memberData.nama_cabang
                                )}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Membership Info -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-green-500"></i>
                            Informasi Membership
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Tanggal Registrasi:</span>
                                <span class="text-sm font-medium">${formatDate(
                                  memberData.tanggal_registrasi
                                )}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Terakhir Update Web:</span>
                                <span class="text-sm font-medium">${
                                  memberData.terakhir_update_web
                                    ? "Belum Update"
                                    : "Sudah Update"
                                }</span>
                            </div>
                           
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Status Member:</span>
                                <span class="text-sm font-medium">${this.formatStatus(
                                  memberData.status_aktif || memberData.status
                                )}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Points & Activity -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-star text-yellow-500"></i>
                            Poin & Aktivitas
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center bg-gradient-to-r from-yellow-50 to-amber-50 p-3 rounded-lg border border-yellow-100">
                                <div>
                                    <span class="text-sm text-gray-600">Total Poin</span>
                                    <p class="text-lg font-bold text-yellow-600">${(
                                      memberData.total_poin || 0
                                    ).toLocaleString(
                                      "id-ID"
                                    )} <span class="text-sm">poin</span></p>
                                </div>
                                <div class="bg-yellow-100 p-2 rounded-lg">
                                    <i class="fas fa-coins text-yellow-600 text-lg"></i>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h5 class="text-sm font-semibold text-gray-700 mb-2">Riwayat Poin</h5>
                                <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
                                    ${this.renderPointHistory(
                                      memberData.history_poin
                                    )}
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-100 mt-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Kode Member:</span>
                                    <span class="text-sm font-medium font-mono">${safeValue(
                                      memberData.kode_member || memberData.id
                                    )}</span>
                                </div>
                                <div class="flex justify-between mt-2">
                                    <span class="text-sm text-gray-600">Cabang Terdaftar:</span>
                                    <span class="text-sm font-medium">${safeValue(
                                      memberData.nama_cabang
                                    )}</span>
                                </div>
                                <div class="flex justify-between mt-2">
                                    <span class="text-sm text-gray-600">Store Terakhir:</span>
                                    <span class="text-sm font-medium">${safeValue(
                                      memberData.nama_toko
                                    )}</span>
                                </div>
                                 <div class="flex justify-between mt-2">
                                <span class="text-sm text-gray-600">Transaksi Terakhir:</span>
                                <span class="text-sm font-medium">${formatDate(
                                  memberData.tgl_trans_terakhir
                                )}</span>
                            </div>
                            <div class="flex justify-between mt-2">
                                <span class="text-sm text-gray-600">No. Faktur Terakhir:</span>
                                <span class="text-sm font-medium font-mono">${safeValue(
                                  memberData.no_faktur_terakhir
                                )}</span>
                            </div>
                            <div class="flex justify-between mt-2">
                                <span class="text-sm text-gray-600">Nilai Belanja Terakhir:</span>
                                <span class="text-sm font-bold text-green-600">${
                                  memberData.belanja_terakhir
                                    ? `Rp ${memberData.belanja_terakhir.toLocaleString(
                                        "id-ID"
                                      )}`
                                    : "Tidak ada data"
                                }</span>
                            </div>
                                <div class="flex justify-between mt-2">
                                    <span class="text-sm text-gray-600">Status Aktif:</span>
                                    <span class="text-sm font-medium">${this.getStatusText(
                                      memberData.status_aktif ||
                                        memberData.status
                                    )}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                   
                    <!-- Riwayat Transaksi -->
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4 border border-blue-100">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-history text-indigo-500"></i>
                            Riwayat Transaksi
                        </h4>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            ${this.renderTransactionHistory(
                              memberData.riwayat_transaksi
                            )}
                        </div>
                    </div>

                     <!-- Quick Actions -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-cogs text-gray-500"></i>
                            Aksi Cepat
                        </h4>
                        <div class="space-y-2">
                            ${
                              memberData.alamat_email
                                ? `
                            <button onclick="window.open('mailto:${memberData.alamat_email}')" class="w-full px-3 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-all duration-200 text-sm font-medium flex items-center gap-2">
                                <i class="fas fa-envelope"></i>
                                Kirim Email
                            </button>
                            `
                                : ""
                            }
                            <button onclick="contactMember('${
                              memberData.kode_member || memberData.id
                            }', '${
      memberData.nama_lengkap || memberData.nama
    }')" class="w-full px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-all duration-200 text-sm font-medium flex items-center gap-2">
                                <i class="fab fa-whatsapp"></i>
                                Hubungi WhatsApp
                            </button>
                            ${
                              this.isInactive(
                                memberData.status_aktif || memberData.status
                              )
                                ? `
                            <button onclick="activateMember('${
                              memberData.kode_member || memberData.id
                            }', '${
                                    memberData.nama_lengkap || memberData.nama
                                  }')" class="w-full px-3 py-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 transition-all duration-200 text-sm font-medium flex items-center gap-2">
                                <i class="fas fa-user-check"></i>
                                Aktifkan Member
                            </button>
                            `
                                : `
                            <button onclick="deactivateMember('${
                              memberData.kode_member || memberData.id
                            }', '${
                                    memberData.nama_lengkap || memberData.nama
                                  }')" class="w-full px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-all duration-200 text-sm font-medium flex items-center gap-2">
                                <i class="fas fa-user-times"></i>
                                Nonaktifkan Member
                            </button>
                            `
                            }
                            <button onclick="deleteMember('${
                              memberData.kode_member || memberData.id
                            }', '${
      memberData.nama_lengkap || memberData.nama
    }')" class="w-full px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200 text-sm font-medium flex items-center gap-2">
                                <i class="fas fa-trash"></i>
                                Hapus Member
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        `;

    // Add slide-in animation
    animationHandler.slideInPanel(detailPanel, "right");
  }

  parsePhoneAndBranch(phoneInfo) {
    if (!phoneInfo) return { no_hp: "N/A", cabang: "N/A" };

    const parts = phoneInfo.split(" • ");
    const no_hp = parts[0] || "N/A";
    const cabang = parts[1] || "N/A";

    return { no_hp, cabang };
  }

  getInitials(name) {
    if (!name) return "??";
    return name
      .split(" ")
      .map((word) => word.charAt(0))
      .join("")
      .toUpperCase()
      .substring(0, 2);
  }

  formatBranch(branch) {
    const branchMap = {
      "jakarta-pusat": "Jakarta Pusat",
      "jakarta-selatan": "Jakarta Selatan",
      bandung: "Bandung",
      surabaya: "Surabaya",
    };
    return branchMap[branch] || branch;
  }

  formatStatus(status) {
    // Return status exactly as received from backend without any modification
    return status || "Tidak Diketahui";
  }

  renderTransactionHistory(transactions) {
    // Helper function to format date
    const formatDate = (dateString) => {
      if (
        !dateString ||
        dateString === "0000-00-00 00:00:00" ||
        dateString === ""
      )
        return "Tidak tersedia";
      try {
        return new Date(dateString).toLocaleDateString("id-ID", {
          day: "2-digit",
          month: "short",
          year: "numeric",
        });
      } catch (error) {
        return dateString;
      }
    };

    // Helper function to format currency
    const formatCurrency = (amount) => {
      if (!amount || amount === 0) return "Rp 0";
      return `Rp ${amount.toLocaleString("id-ID")}`;
    };

    // Check if transactions data exists
    if (
      !transactions ||
      !Array.isArray(transactions) ||
      transactions.length === 0
    ) {
      return `
        <div class="text-center py-8">
          <i class="fas fa-receipt text-gray-300 text-3xl mb-2"></i>
          <p class="text-sm text-gray-500">Belum ada riwayat transaksi</p>
        </div>
      `;
    }

    return transactions
      .map(
        (transaction, index) => `
            <div class="flex justify-between items-start py-3 border-b border-gray-100 last:border-b-0 hover:bg-white hover:shadow-sm rounded-lg transition-all duration-200 px-2">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            #${index + 1}
                        </span>
                        <span class="text-xs text-gray-500">${
                          transaction.toko || "N/A"
                        }</span>
                    </div>
                    <p class="text-sm font-medium text-gray-800 font-mono">${
                      transaction.no_faktur || "N/A"
                    }</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        ${formatDate(transaction.tanggal)}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-bold text-green-600">${formatCurrency(
                          transaction.belanja
                        )}</p>
                        <p class="text-xs text-gray-500">Store: ${
                          transaction.cabang || "N/A"
                        }</p>
                    </div>
                    <button 
                        onclick="showShoppingList('${this.currentMember}', '${
          transaction.no_faktur || ""
        }', event)"
                        class="px-3 py-1 text-sm bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg flex items-center gap-2 transition-colors duration-200"
                        title="Lihat struk belanja"
                    >
                        <i class="fas fa-receipt"></i>
                        <span>Struk</span>
                    </button>
                </div>
            </div>
        `
      )
      .join("");
  }

  // Method to activate member
  async activateMember(memberId) {
    try {
      const result = await Swal.fire({
        title: "Aktifkan Member?",
        text: "Member akan diaktifkan dan mendapat notifikasi via email & WhatsApp",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#10b981",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Ya, Aktifkan!",
        cancelButtonText: "Batal",
      });

      if (result.isConfirmed) {
        // Call API to activate member
        const response = await api.updateMember(memberId, { status: "active" });

        if (response.success) {
          Swal.fire({
            title: "Berhasil!",
            text: "Member berhasil diaktifkan.",
            icon: "success",
            confirmButtonColor: "#3b82f6",
          });

          // Refresh member list and detail
          if (typeof window.refreshMemberList === "function") {
            window.refreshMemberList();
          }
        } else {
          throw new Error(response.message || "Gagal mengaktifkan member");
        }
      }
    } catch (error) {
      console.error("Error activating member:", error);
      Swal.fire({
        title: "Error!",
        text: error.message,
        icon: "error",
        confirmButtonColor: "#ef4444",
      });
    }
  }

  // Method to deactivate member
  async deactivateMember(memberId, memberName) {
    try {
      const result = await Swal.fire({
        title: "Nonaktifkan Member?",
        text: `Member "${memberName}" akan dinonaktifkan dan tidak dapat melakukan transaksi`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Ya, Nonaktifkan!",
        cancelButtonText: "Batal",
      });

      if (result.isConfirmed) {
        // Call API to deactivate member
        const response = await api.updateMembers(memberId, {
          status: "inactive",
        });

        if (response.success) {
          Swal.fire({
            title: "Berhasil!",
            text: "Member berhasil dinonaktifkan.",
            icon: "success",
            confirmButtonColor: "#3b82f6",
          });

          // Refresh member list and detail
          if (typeof window.refreshMemberList === "function") {
            window.refreshMemberList();
          }
        } else {
          throw new Error(response.message || "Gagal menonaktifkan member");
        }
      }
    } catch (error) {
      console.error("Error deactivating member:", error);
      Swal.fire({
        title: "Error!",
        text: error.message,
        icon: "error",
        confirmButtonColor: "#ef4444",
      });
    }
  }

  // Method to edit member
  editMember(memberId) {
    // TODO: Implement edit member functionality
    Swal.fire({
      title: "Edit Member",
      text: "Fitur edit member akan segera tersedia. Untuk saat ini, silakan hubungi administrator sistem.",
      icon: "info",
      confirmButtonColor: "#3b82f6",
    });
    console.log("Edit member:", memberId);
  }

  // Method to contact member
  contactMember(memberId, memberName) {
    // TODO: Implement contact member functionality via WhatsApp
    Swal.fire({
      title: "Hubungi Member",
      text: `Fitur kontak WhatsApp untuk member "${memberName}" akan segera tersedia.`,
      icon: "info",
      confirmButtonColor: "#3b82f6",
    });
    console.log("Contact member:", memberId, memberName);
  }

  // Method to hide member detail panel
  hideMemberDetail() {
    const detailPanel = document.getElementById("member-detail-panel");
    if (detailPanel) {
      animationHandler.slideOutPanel(detailPanel, "right");
    }
  }

  // Show shopping list modal
  async showShoppingListModal(memberId, noFaktur, event) {
    if (event) {
      event.stopPropagation();
    }

    // Create modal if it doesn't exist
    let modal = document.getElementById("shoppingListModal");
    if (!modal) {
      modal = document.createElement("div");
      modal.id = "shoppingListModal";
      modal.className =
        "fixed inset-0 bg-black/50 flex justify-center items-center z-50 hidden backdrop-blur-sm transition-all duration-300";
      document.body.appendChild(modal);
    }

    // Show loading state
    modal.classList.remove("hidden");
    modal.innerHTML = `
      <div class="list-shop-modal bg-white/95 backdrop-blur-md w-full max-w-7xl rounded-2xl shadow-2xl border border-blue-100 relative animate-fade-in-up max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-3 rounded-xl">
                <i class="fas fa-receipt text-white text-xl"></i>
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-800">Detail Transaksi</h2>
                <p class="text-sm text-gray-600">No. Faktur: ${noFaktur}</p>
              </div>
            </div>
            <button onclick="memberDetailHandler.closeShoppingListModal()" class="text-gray-500 hover:text-red-500 text-2xl">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="flex justify-center items-center p-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>
      </div>
    `;

    try {
      const response = await api.getMemberListShopping(memberId, noFaktur);
      if (response.status && response.transaction.length > 0) {
        this.renderShoppingList(modal, response.transaction);
      } else {
        throw new Error("Data transaksi tidak ditemukan");
      }
    } catch (error) {
      modal.querySelector(".list-shop-modal").innerHTML = `
        <div class="p-6">
          <div class="text-center text-red-500">
            <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
            <p class="text-sm font-medium mb-2">Gagal memuat data transaksi</p>
            <p class="text-xs text-gray-500">${error.message}</p>
             <button onclick="memberDetailHandler.closeShoppingListModal()" 
                class="mt-3 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all">
          <i class="fas fa-times mr-1"></i> Tutup
        </button>
          </div>
        </div>
      `;
    }
  }

  // Close shopping list modal
  closeShoppingListModal() {
    const modal = document.getElementById("shoppingListModal");
    if (modal) {
      modal.classList.add("hidden");
    }
  }

  // Render shopping list in modal
  renderShoppingList(modal, transactions) {
    const transaction = transactions[0]; // Get first transaction for header info

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
      })
        .format(amount)
        .replace("IDR", "Rp");
    };

    const formatDate = (dateStr) => {
      const date = new Date(dateStr);
      return date.toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      });
    };

    const content = `
    <div class="flex flex-col h-full max-h-[90vh] bg-white text-gray-900 text-sm">
  <!-- Invoice Header -->
  <div class="p-8 border-b-2 border-gray-800">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold tracking-wide">INVOICE</h1>
        <p class="text-base text-gray-600 mt-1">No. ${
          transaction.kode_transaksi
        }</p>
      </div>
      <button onclick="memberDetailHandler.closeShoppingListModal()" 
              class="text-gray-400 hover:text-red-600 text-2xl">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Company & Invoice Info -->
    <div class="grid grid-cols-2 gap-8">
      <!-- Dari -->
      <div>
        <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Dari</h2>
        <h3 class="text-lg font-bold">${transaction.nama_store}</h3>
        <p class="text-sm text-gray-700 leading-relaxed mt-1">${
          transaction.alamat_store
        }</p>
      </div>

      <!-- Detail Invoice -->
      <div class="text-right">
        <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Detail Invoice</h2>
        <p><span class="text-gray-500">Tanggal:</span> <span class="font-medium ml-2">${
          transaction.tanggal
        }</span></p>
        <p><span class="text-gray-500">Jam:</span> <span class="font-medium ml-2">${
          transaction.jam_trs
        }</span></p>
        <p><span class="text-gray-500">Kasir:</span> <span class="font-medium ml-2">${
          transaction.kasir
        }</span></p>
      </div>
    </div>
  </div>

  <!-- Invoice Items -->
  <div class="p-8">
    <h2 class="text-base font-semibold mb-4">Rincian Pembelian</h2>
    <div class="border border-gray-300">
      <table class="w-full border-collapse text-sm">
        <thead>
          <tr class="bg-gray-200 text-gray-800">
            <th class="py-2 px-3 text-left font-medium">Deskripsi Item</th>
            <th class="py-2 px-3 text-center font-medium">Qty</th>
            <th class="py-2 px-3 text-right font-medium">Harga Satuan</th>
            <th class="py-2 px-3 text-right font-medium">Diskon</th>
            <th class="py-2 px-3 text-right font-medium">Potongan</th>
            <th class="py-2 px-3 text-right font-medium">Jumlah</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          ${transactions
            .map(
              (item, index) => `
            <tr>
              <td class="py-2 px-3">${item.item}</td>
              <td class="py-2 px-3 text-center">${item.qty}</td>
              <td class="py-2 px-3 text-right">${formatCurrency(
                item.harga
              )}</td>
              <td class="py-2 px-3 text-right">${item.diskon}%</td>
              <td class="py-2 px-3 text-right">${formatCurrency(
                item.harga > item.hrg_promo ? item.harga - item.hrg_promo : 0
              )}</td>
              <td class="py-2 px-3 text-right font-medium">${formatCurrency(
                item.hrg_promo * item.qty
              )}</td>
            </tr>
          `
            )
            .join("")}
        </tbody>
      </table>

      <!-- Subtotal -->
      <div class="flex justify-between items-center px-3 py-2 border-t border-gray-300">
        <span class="text-xs text-gray-600">Total Item: ${
          transactions.length
        } produk</span>
        <span class="text-base font-semibold">Subtotal: ${formatCurrency(
          transactions.reduce((sum, item) => sum + item.hrg_promo * item.qty, 0)
        )}</span>
      </div>
    </div>
  </div>

  <!-- Invoice Summary -->
  <div class="border-t-2 border-gray-800">
    <div class="p-8 grid grid-cols-2 gap-8">
      
      <!-- Metode Pembayaran -->
      <div>
        <h3 class="text-base font-semibold mb-3">Metode Pembayaran</h3>
        <div class="space-y-2 text-sm">
          ${
            transaction.cash > 0
              ? `
            <p><span class="font-medium">Tunai:</span> ${formatCurrency(
              transaction.cash
            )}</p>
          `
              : ""
          }
          ${
            transaction.credit1 > 0
              ? `
            <p><span class="font-medium">${
              transaction.nm_kartu
            }:</span> ${formatCurrency(
                  transaction.credit1
                )} <span class="text-xs text-gray-500">(**** ${transaction.no_kredit1.slice(
                  -4
                )})</span></p>
          `
              : ""
          }
          ${
            transaction.voucher1 > 0
              ? `
            <p><span class="font-medium">Voucher:</span> ${formatCurrency(
              transaction.voucher1
            )} <span class="text-xs text-gray-500">${
                  transaction.no_voucher1
                }</span></p>
          `
              : ""
          }
        </div>
      </div>

      <!-- Ringkasan -->
      <div>
        <h3 class="text-base font-semibold mb-3">Ringkasan Pembayaran</h3>
        <div class="space-y-1 text-sm">
          <div class="flex justify-between">
            <span>Subtotal:</span>
            <span class="font-medium">${formatCurrency(
              transaction.belanja
            )}</span>
          </div>
          <div class="flex justify-between">
            <span>Total Potongan:</span>
            <span class="font-medium text-red-600">-${formatCurrency(
              transaction.total_diskon
            )}</span>
          </div>
          <div class="flex justify-between border-t border-gray-400 pt-2 mt-2">
            <span class="text-lg font-bold">TOTAL:</span>
            <span class="text-lg font-bold">${formatCurrency(
              transaction.bayar
            )}</span>
          </div>
          ${
            transaction.kembalian > 0
              ? `
            <div class="flex justify-between bg-green-50 px-3 py-1 mt-2">
              <span class="font-medium text-green-700">Kembalian:</span>
              <span class="font-bold text-green-700">${formatCurrency(
                transaction.kembalian
              )}</span>
            </div>
          `
              : ""
          }
        </div>
      </div>
    </div>
  </div>
</div>

  `;

    modal.querySelector(".list-shop-modal").innerHTML = content;
  }
}
// Create instance
const memberDetailHandler = new MemberDetailHandler();

// Expose to window object
window.memberDetailHandler = memberDetailHandler;

// Global functions for member actions
window.activateMember = (memberId, memberName) =>
  memberDetailHandler.activateMember(memberId);
window.deactivateMember = (memberId, memberName) =>
  memberDetailHandler.deactivateMember(memberId, memberName);
window.editMember = (memberId) => memberDetailHandler.editMember(memberId);
window.contactMember = (memberId, memberName) =>
  memberDetailHandler.contactMember(memberId, memberName);
window.deleteMember = (memberId, memberName) =>
  memberDetailHandler.deleteMember(memberId, memberName);
window.showShoppingList = (memberId, noFaktur, event) =>
  memberDetailHandler.showShoppingListModal(memberId, noFaktur, event);

export { memberDetailHandler };
