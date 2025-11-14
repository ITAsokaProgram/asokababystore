import { formatNumber, formatCurrency } from "./ui_helpers.js";
import { lazyLoadList } from "./lazy_load_list.js";
let charts = {};
const updateTopMembersPerformance = (memberData) => {
  window.topMembersData = memberData;
  lazyLoadList(memberData, "top-members-performance", (member, index) => {
    const getRankColors = (rank) => {
      if (rank === 1)
        return {
          border: "border-l-amber-400",
          bg: "bg-gradient-to-r from-amber-50 to-yellow-50",
          rankBg: "bg-gradient-to-r from-amber-400 to-yellow-500",
          rankText: "text-white",
          shadow: "shadow-amber-100",
        };
      if (rank === 2)
        return {
          border: "border-l-slate-400",
          bg: "bg-gradient-to-r from-slate-50 to-gray-50",
          rankBg: "bg-gradient-to-r from-slate-400 to-gray-500",
          rankText: "text-white",
          shadow: "shadow-slate-100",
        };
      if (rank === 3)
        return {
          border: "border-l-orange-400",
          bg: "bg-gradient-to-r from-orange-50 to-amber-50",
          rankBg: "bg-gradient-to-r from-orange-400 to-amber-500",
          rankText: "text-white",
          shadow: "shadow-orange-100",
        };
      if (rank <= 5)
        return {
          border: "border-l-emerald-400",
          bg: "bg-gradient-to-r from-emerald-50 to-green-50",
          rankBg: "bg-gradient-to-r from-emerald-400 to-green-500",
          rankText: "text-white",
          shadow: "shadow-emerald-100",
        };
      return {
        border: "border-l-blue-400",
        bg: "bg-gradient-to-r from-blue-50 to-indigo-50",
        rankBg: "bg-gradient-to-r from-blue-400 to-indigo-500",
        rankText: "text-white",
        shadow: "shadow-blue-100",
      };
    };
    const colors = getRankColors(index + 1);
    const rank = index + 1;
    return `
        <div class="cursor-pointer ${colors.bg} rounded-xl ${
      colors.border
    } border-l-4 ${
      colors.shadow
    } shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all duration-300 ease-in-out group overflow-hidden" 
             data-member="${member.kd_cust}" 
             data-cabang="${member.kd_store}" 
             id="member-${member.kd_cust}">
          <div class="p-3 relative"> <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 translate-x-16 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative">
              <div class="flex items-center min-w-0">
                <div class="relative">
                  <div class="w-10 h-10 ${colors.rankBg} ${
      colors.rankText
    } rounded-full flex items-center justify-center text-sm font-bold mr-2 shadow-lg group-hover:shadow-xl transition-shadow duration-300"> ${rank}
                  </div>
                </div>
                <div class="space-y-1">
                  <div class="font-bold text-gray-800 text-sm leading-tight group-hover:text-gray-900 transition-colors duration-200">
                    ${member.nama_cust}
                  </div>
                  <div class="text-sm text-gray-600 font-medium bg-gray-100 px-2 py-1 rounded-md inline-block">
                    ${member.kd_cust}
                  </div>
                  <div class="text-sm text-indigo-600 font-semibold bg-indigo-100 px-2 py-1 rounded-md inline-block">
                    📍 ${member.cabang}
                  </div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold text-emerald-600 text-sm mb-1 group-hover:text-emerald-700 transition-colors duration-200">
                  ${formatCurrency(member.total_penjualan)}
                </div>
                <div class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full"> 💰 Total Belanja
                </div>
                <div class="mt-1"> <div class="flex items-center justify-end space-x-1">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-2 bg-gray-200 rounded-full h-2"> <div class="${
              colors.rankBg
            } h-full rounded-full transition-all duration-1000 ease-out" 
                   style="width: ${Math.min(
                     100,
                     (member.total_penjualan /
                       Math.max(...memberData.map((m) => m.total_penjualan))) *
                       100
                   )}%"></div>
            </div>
          </div>
        </div>
      `;
  });
};
const updateTopNonMembersPerformance = (memberData) => {
  window.topNonMembersData = memberData;
  lazyLoadList(memberData, "top-non-member-performance", (member, index) => {
    const getRankColors = (rank) => {
      if (rank === 1)
        return {
          border: "border-l-purple-400",
          bg: "bg-gradient-to-r from-purple-50 to-violet-50",
          rankBg: "bg-gradient-to-r from-purple-400 to-violet-500",
          rankText: "text-white",
          shadow: "shadow-purple-100",
          accent: "text-purple-600",
        };
      if (rank === 2)
        return {
          border: "border-l-pink-400",
          bg: "bg-gradient-to-r from-pink-50 to-rose-50",
          rankBg: "bg-gradient-to-r from-pink-400 to-rose-500",
          rankText: "text-white",
          shadow: "shadow-pink-100",
          accent: "text-pink-600",
        };
      if (rank === 3)
        return {
          border: "border-l-cyan-400",
          bg: "bg-gradient-to-r from-cyan-50 to-teal-50",
          rankBg: "bg-gradient-to-r from-cyan-400 to-teal-500",
          rankText: "text-white",
          shadow: "shadow-cyan-100",
          accent: "text-cyan-600",
        };
      if (rank <= 5)
        return {
          border: "border-l-indigo-400",
          bg: "bg-gradient-to-r from-indigo-50 to-blue-50",
          rankBg: "bg-gradient-to-r from-indigo-400 to-blue-500",
          rankText: "text-white",
          shadow: "shadow-indigo-100",
          accent: "text-indigo-600",
        };
      return {
        border: "border-l-slate-400",
        bg: "bg-gradient-to-r from-slate-50 to-gray-50",
        rankBg: "bg-gradient-to-r from-slate-400 to-gray-500",
        rankText: "text-white",
        shadow: "shadow-slate-100",
        accent: "text-slate-600",
      };
    };
    const colors = getRankColors(index + 1);
    const rank = index + 1;
    const getPerformanceBadge = (rank) => {
      if (rank === 1) return { icon: "🌟", text: "Top Performer" };
      if (rank === 2) return { icon: "⭐", text: "Excellence" };
      if (rank === 3) return { icon: "✨", text: "Outstanding" };
      if (rank <= 5) return { icon: "🔥", text: "High Value" };
      return { icon: "💎", text: "Valued Customer" };
    };
    const badge = getPerformanceBadge(rank);
    const formatTransactionId = (transId) => {
      if (transId.length > 12) {
        return transId.substring(0, 12) + "...";
      }
      return transId;
    };
    return `
        <div class="cursor-pointer ${colors.bg} rounded-xl ${
      colors.border
    } border-l-4 ${
      colors.shadow
    } shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all duration-300 ease-in-out group" 
             data-non-member="${member.no_trans}" 
             data-cabang="${member.kd_store}" 
             id="non-member-${member.no_trans}">
          <div class="p-3 relative overflow-hidden"> <div class="absolute top-0 right-0 w-28 h-28 bg-white/10 rounded-full -translate-y-12 translate-x-12 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="absolute bottom-0 left-0 w-20 h-20 bg-white/5 rounded-full translate-y-8 -translate-x-8 group-hover:scale-110 transition-transform duration-700"></div>
            <div class="flex items-center justify-between relative">
              <div class="flex items-center flex-1">
                <div class="relative flex-shrink-0">
                  <div class="w-10 h-10 ${colors.rankBg} ${
      colors.rankText
    } rounded-full flex items-center justify-center text-sm font-bold mr-2 shadow-lg group-hover:shadow-xl transition-shadow duration-300"> ${rank}
                  </div>
                </div>
                <div class="space-y-1 flex-1 min-w-0">
                  <div class="font-mono text-gray-800 text-sm leading-tight group-hover:text-gray-900 transition-colors duration-200">
                    🧾 ${formatTransactionId(member.no_trans)}
                  </div>
                  <div class="text-sm ${
                    colors.accent
                  } font-semibold bg-white/70 px-2 py-1 rounded-md inline-block">
                    📍 ${member.cabang}
                  </div>
                  <div class="text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded-full inline-block">
                    ${badge.icon} ${badge.text}
                  </div>
                </div>
              </div>
              <div class="text-right flex-shrink-0 ml-2"> <div class="font-bold text-emerald-600 text-sm mb-1 group-hover:text-emerald-700 transition-colors duration-200">
                  ${formatCurrency(member.total_penjualan)}
                </div>
                <div class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full"> 💰 Total Belanja
                </div>
                <div class="mt-1"> <div class="flex items-center justify-end space-x-1">
                    <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-2 bg-gray-200 rounded-full h-2 overflow-hidden"> <div class="${
              colors.rankBg
            } h-full rounded-full transition-all duration-1000 ease-out group-hover:animate-pulse" 
                   style="width: ${Math.min(
                     100,
                     (member.total_penjualan /
                       Math.max(...memberData.map((m) => m.total_penjualan))) *
                       100
                   )}%"></div>
            </div>
          </div>
        </div>
      `;
  });
};
const destroyAllCharts = () => {
  Object.values(charts).forEach((chart) => {
    if (chart && typeof chart.destroy === "function") {
      chart.destroy();
    }
  });
  charts = {};
};
export {
  updateTopMembersPerformance,
  updateTopNonMembersPerformance,
  destroyAllCharts,
};
