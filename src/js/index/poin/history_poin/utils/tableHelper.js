// Format tanggal
export const formatDate = (dateString) => {
  const date = new Date(dateString);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  if (date.toDateString() === today.toDateString()) {
    return "Hari ini";
  } else if (date.toDateString() === yesterday.toDateString()) {
    return "Kemarin";
  } else {
    const options = {
      day: "numeric",
      month: "long",
      year: "numeric",
    };
    return date.toLocaleDateString("id-ID", options);
  }
};

// Enhanced render points list with better UI/UX
export const renderPointsList = (data) => {
  const pointsList = document.getElementById("pointsList");
  const emptyState = document.getElementById("emptyState");

  if (data.length === 0) {
    pointsList.innerHTML = "";
    emptyState.classList.remove("hidden");
    return;
  }

  emptyState.classList.add("hidden");

  pointsList.innerHTML = data
    .map((item, index) => {
      // Ambil data yang dibutuhkan
      const cabang = item.cabang || "-";
      const jam = item.jam || "-";
      const jumlahPoint = item.jumlah_point || 0;
      const noTrans = item.no_trans || "-";
      const tanggal = item.tanggal || "-";
      const category = item.keterangan_struk || "-";

      // Enhanced visual indicators berdasarkan category
      const isPositive = category === "Poin Masuk";
      const pointColor = isPositive ? "text-emerald-600" : "text-red-500";
      const bgGradient = isPositive
        ? "from-emerald-50 to-green-50 hover:from-emerald-100 hover:to-green-100 border-l-4 border-l-emerald-400"
        : "from-red-50 to-rose-50 hover:from-red-100 hover:to-rose-100 border-l-4 border-l-red-400";
      const iconBg = isPositive
        ? "bg-gradient-to-br from-emerald-500 to-green-600 shadow-emerald-200"
        : "bg-gradient-to-br from-red-500 to-rose-600 shadow-red-200";
      const icon = isPositive ? "fa-arrow-trend-up" : "fa-arrow-trend-down";
      const sign = isPositive ? "+" : "-";

      // Get category badge styling
      const getCategoryBadge = (cat) => {
        if (cat === "Poin Masuk") {
          return "bg-gradient-to-r from-emerald-600 to-green-600 text-white";
        } else {
          return "bg-gradient-to-r from-red-500 to-rose-500 text-white";
        }
      };

      // Format tanggal lebih baik
      const formatDate = (dateStr) => {
        try {
          const date = new Date(dateStr);
          return date.toLocaleDateString("id-ID", {
            weekday: "short",
            day: "numeric",
            month: "short",
            year: "numeric",
          });
        } catch {
          return dateStr;
        }
      };

      return `
        <div class="group relative bg-gradient-to-r ${bgGradient} rounded-2xl p-6 mb-4 border border-slate-200/60 hover:border-slate-300/80 transition-all duration-300 hover:shadow-xl hover:scale-[1.01] cursor-pointer animate-fadeIn" style="animation-delay: ${
        index * 50
      }ms">
          
         <div class="flex items-center justify-between">
            <div class="flex space-x-3">
              
              
              <div class="flex">
                <div class="${iconBg} w-10 h-10 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <i class="fas ${icon} text-white text-xl"></i>
                </div>
                <!-- Category status indicator -->
                <div class="absolute -bottom-2 -right-2 px-2 py-1 ${getCategoryBadge(
                  category
                )} rounded-full text-xs font-bold shadow-lg">
                  ${isPositive ? "IN" : "OUT"}
                </div>
              </div>

              
              <div class="space-y-1">
                <!-- Nama cabang sejajar dengan icon -->
                <h3 class="font-bold text-slate-800 text-sm group-hover:text-slate-900 transition-colors">
                  ${cabang}
                </h3>
                
                <!-- Transaction details with better spacing -->
                <div class="flex flex-wrap items-center gap-1 text-sm">
                  <div class="flex items-center space-x-2 bg-blue-50 px-3 py-1 rounded-lg">
                    <i class="fas fa-calendar-days text-blue-500"></i>
                    <span class="font-medium text-blue-700">${formatDate(
                      tanggal
                    )}</span>
                  </div>
                  <div class="flex items-center space-x-2 bg-purple-50 px-3 py-1 rounded-lg">
                    <i class="fas fa-clock text-purple-500"></i>
                    <span class="font-medium text-purple-700">${jam}</span>
                  </div>
                </div>
                
               
                <div class="inline-flex items-center space-x-2 bg-slate-800 px-4 py-2 rounded-xl text-white shadow-lg">
                  <i class="fas fa-receipt text-slate-300"></i>
                  <span class="font-mono text-xs">${noTrans}</span>
                </div>
              </div>
            </div>

           
            <div class="text-right space-y-2">
              <div class="${pointColor} text-xl font-black group-hover:scale-110 transition-all duration-300 flex items-center justify-end drop-shadow-lg">
                <span class="mr-1 text-2xl">${sign}</span>
                <span>${Math.abs(jumlahPoint).toLocaleString("id-ID")}</span>
              </div>
              
            
              <div class="text-xs font-bold uppercase tracking-widest ${
                isPositive ? "text-emerald-600" : "text-red-600"
              }">
                Poin
              </div>
              
             
              ${
                Math.abs(jumlahPoint) >= 100
                  ? `
              <div class="flex justify-end">
                <div class="px-3 py-1 bg-gradient-to-r ${
                  isPositive
                    ? "from-emerald-500 to-green-500"
                    : "from-red-500 to-rose-500"
                } text-white rounded-full text-xs font-bold shadow-lg">
                  <i class="fas ${
                    Math.abs(jumlahPoint) >= 200 ? "fa-fire" : "fa-star"
                  }"></i>
                  ${Math.abs(jumlahPoint) >= 200 ? "BESAR" : "SEDANG"}
                </div>
              </div>
              `
                  : ""
              }
              
            </div>
          </div>

          
          <div class="absolute inset-0 bg-gradient-to-r ${
            isPositive
              ? "from-emerald-500/5 via-green-500/10 to-emerald-500/5"
              : "from-red-500/5 via-rose-500/10 to-red-500/5"
          } opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl pointer-events-none"></div>
          
          <!-- Pulse effect untuk transaksi besar -->
          ${
            Math.abs(jumlahPoint) >= 200
              ? `
          <div class="absolute -inset-1 bg-gradient-to-r ${
            isPositive
              ? "from-emerald-400 to-green-400"
              : "from-red-400 to-rose-400"
          } rounded-2xl opacity-20 blur-sm group-hover:opacity-40 transition-opacity duration-300"></div>
          `
              : ""
          }
        </div>
      `;
    })
    .join("");
};
