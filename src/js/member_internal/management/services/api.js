const API_URL = "/src/api/member/management/get_new_member";
const MEMBER_DATA_API_URL = "/src/api/redis/data_member.php";
const API_HEADERS = {
  "Content-Type": "application/json",
  Authorization: "Bearer " + localStorage.getItem("token"),
};

// Helper function for string hashing
String.prototype.hashCode = function () {
  let hash = 0;
  for (let i = 0; i < this.length; i++) {
    const char = this.charCodeAt(i);
    hash = (hash << 5) - hash + char;
    hash = hash & hash; // Convert to 32bit integer
  }
  return Math.abs(hash);
};

export const api = {
  // Member data from Redis
  getMemberData: async (page = 1, limit = 10) => {
    try {
      const response = await fetch(
        `${MEMBER_DATA_API_URL}?page=${page}&limit=${limit}`,
        {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching member data:", error);
      throw error;
    }
  },

  // Transform Redis data to modal format
  transformMemberData: (redisData) => {
    if (!redisData || !redisData.data) return [];

    return redisData.data.map((member, index) => {
      // Generate member ID from kode_member or use index
      const memberId =
        member.kode_member || `member_${redisData.page * 1000 + index + 1}`;

      // Map status
      let status = "inactive";
      if (member.status_aktif === "Aktif") status = "active";
      else if (member.status_aktif === "Pending") status = "pending";

      // Calculate member since
      let memberSince = "Baru";
      if (member.tanggal_registrasi) {
        const regDate = new Date(member.tanggal_registrasi);
        const now = new Date();
        const monthsDiff =
          (now.getFullYear() - regDate.getFullYear()) * 12 +
          now.getMonth() -
          regDate.getMonth();
        if (monthsDiff > 0) {
          memberSince = `${monthsDiff} bulan`;
        }
      }

      // Generate avatar URL
      const avatarName = member.nama_lengkap
        ? member.nama_lengkap.trim()
        : "Member";
      const avatarColors = [
        "3b82f6",
        "10b981",
        "f59e0b",
        "ef4444",
        "8b5cf6",
        "ec4899",
      ];
      const colorIndex =
        Math.abs(memberId.hashCode ? memberId.hashCode() : 0) %
        avatarColors.length;
      const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(
        avatarName
      )}&background=${avatarColors[colorIndex]}&color=fff`;

      return {
        id: memberId,
        nama: member.nama_lengkap || "Tidak ada nama",
        email: member.alamat_email || "Email tidak tersedia",
        nomor_hp: member.kode_member || "Nomor tidak tersedia",
        alamat: member.kota_domisili || "Alamat tidak tersedia",
        cabang: member.nama_cabang || "Cabang tidak tersedia",
        status: status,
        poin: parseInt(member.total_poin) || 0,
        bergabung: member.tanggal_registrasi || "",
        transaksi_terakhir: member.tgl_trans_terakhir || null,
        total_transaksi: Math.floor(Math.random() * 50) + 1, // Random since not in data
        foto_profil: avatarUrl,
        jenis_kelamin: member.jenis_kelamin || "Tidak diketahui",
        tanggal_lahir: member.tanggal_lahir || null,
        member_since: memberSince,
        terakhir_update: member.terakhir_update_web || 0,
      };
    });
  },

  // Existing methods
  getNewMembers: async (limit, page) => {
    try {
      const response = await fetch(API_URL, {
        method: "POST",
        headers: API_HEADERS,
        body: JSON.stringify({
          limit: limit,
          page: page,
        }),
      });
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching new members:", error);
      throw error;
    }
  },

  getSummary: async () => {
    try {
      const response = await fetch(
        "/src/api/member/management/get_summary.php",
        {
          method: "GET",
          headers: API_HEADERS,
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching member summary:", error);
      throw error;
    }
  },

  suspendMember: async (memberId, reason) => {
    try {
      const response = await fetch(
        "/src/api/member/management/suspend_member.php",
        {
          method: "POST",
          headers: API_HEADERS,
          body: JSON.stringify({
            member_id: memberId,
            reason: reason,
          }),
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error suspending member:", error);
      throw error;
    }
  },

  sendEmailBlast: async (emailData) => {
    try {
      const response = await fetch(
        "/src/api/member/management/send_email_blast.php",
        {
          method: "POST",
          headers: API_HEADERS,
          body: JSON.stringify(emailData),
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error sending email blast:", error);
      throw error;
    }
  },

  sendWhatsAppBlast: async (whatsappData) => {
    try {
      const response = await fetch(
        "/src/api/member/management/send_whatsapp_blast.php",
        {
          method: "POST",
          headers: API_HEADERS,
          body: JSON.stringify(whatsappData),
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error sending WhatsApp blast:", error);
      throw error;
    }
  },

  bulkActivateMembers: async (memberIds) => {
    try {
      const response = await fetch(
        "/src/api/member/management/bulk_activate.php",
        {
          method: "POST",
          headers: API_HEADERS,
          body: JSON.stringify({
            member_ids: memberIds,
          }),
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error bulk activating members:", error);
      throw error;
    }
  },

  getActivityLog: async (page = 1, filters = {}) => {
    try {
      const response = await fetch(
        "/src/api/member/management/get_activity_log.php",
        {
          method: "POST",
          headers: API_HEADERS,
          body: JSON.stringify({
            page: page,
            ...filters,
          }),
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching activity log:", error);
      throw error;
    }
  },

  getMembers: async (page = 1, filters = {}) => {
    try {
      const response = await fetch(
        "/src/api/member/management/get_members.php",
        {
          method: "POST",
          headers: API_HEADERS,
          body: JSON.stringify({
            page: page,
            ...filters,
          }),
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching members:", error);
      throw error;
    }
  },

  getMemberDetail: async (memberId) => {
    try {
      const response = await fetch(
        `/src/api/member/management/get_member_detail?kd_cust=${memberId}`,
        {
          method: "GET",
          headers: API_HEADERS,
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching member detail:", error);
      throw error;
    }
  },

  updateMember: async (memberId, memberData) => {
    try {
      const response = await fetch(
        "/src/api/member/management/update_member.php",
        {
          method: "POST",
          headers: API_HEADERS,
          body: JSON.stringify({
            member_id: memberId,
            ...memberData,
          }),
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error updating member:", error);
      throw error;
    }
  },

  deleteMember: async (memberId) => {
    try {
      const response = await fetch(
        "/src/api/member/management/delete_member.php",
        {
          method: "POST",
          headers: API_HEADERS,
          body: JSON.stringify({
            member_id: memberId,
          }),
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error deleting member:", error);
      throw error;
    }
  },

  getMemberListShopping: async (memberId, memberNoFaktur) => {
    try {
      const response = await fetch(
        `/src/api/member/management/get_list_shopping?kd_cust=${memberId}&no_bon=${memberNoFaktur}`,
        {
          method: "GET",
          headers: API_HEADERS,
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching member detail:", error);
      throw error;
    }
  },

  searchMember: async (keyword) => {
    try {
      const response = await fetch(
        `/src/api/redis/data_member?keyword=${keyword}`,
        {
          method: "GET",
          headers: API_HEADERS,
        }
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching member detail:", error);
      throw error;
    }
  },
};
