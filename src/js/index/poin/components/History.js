import { state } from "../state.js";
import {
  fetchExchangeHistory,
  generateQrCode,
  handleExpiredDate,
} from "../services/api.js";
import { isClaimed, getItemStatus } from "../services/historyHelpers.js";

// Auto-update expired status every minute
let updateInterval;
// Lazy loading state
let currentPage = 1;
let isLoading = false;
let hasMoreData = true;
let allHistoryData = [];
let scrollContainer = null;
let totalHistoryCount = 0; // Store total count from pagination

// Open history modal
export const openHistoryModal = () => {
  const modal = document.getElementById("historyModal");
  modal.classList.remove("hidden");
  modal.classList.add("slide-up");
  document.body.style.overflow = "hidden";
  // Reset lazy loading state
  currentPage = 1;
  isLoading = false;
  hasMoreData = true;
  allHistoryData = [];
  totalHistoryCount = 0;

  // Update modal content
  renderHistoryModal();

  // Start auto-update interval
  updateInterval = setInterval(async () => {
    await updateHistoryStatus();
  }, 1000);
};

// Close history modal
export const closeHistoryModal = () => {
  const modal = document.getElementById("historyModal");
  modal.classList.add("hidden");
  modal.classList.remove("slide-up");
  document.body.style.overflow = "auto";

  // Clear auto-update interval
  if (updateInterval) {
    clearInterval(updateInterval);
    updateInterval = null;
  }

  // Remove scroll listener
  if (scrollContainer) {
    scrollContainer.removeEventListener("scroll", handleScroll);
    scrollContainer = null;
  }

  // Reset state
  currentPage = 1;
  isLoading = false;
  hasMoreData = true;
  allHistoryData = [];
  totalHistoryCount = 0;
};

// Load more data for lazy loading
const loadMoreHistoryData = async () => {
  if (isLoading || !hasMoreData) return;


  isLoading = true;
  showLoadingSpinner();

  try {
    const response = await fetchExchangeHistory(currentPage, 10);
    const newData = response.data || [];
    const pagination = response.pagination;



    if (newData.length > 0) {
      allHistoryData = [...allHistoryData, ...newData];
      hasMoreData = pagination?.has_more || false;

      // Store total count from first page response
      if (currentPage === 1 && pagination?.total) {
        totalHistoryCount = pagination.total;
        // Update total transactions immediately
        const totalTransactions = document.getElementById("totalTransactions");
        if (totalTransactions) {
          totalTransactions.textContent = totalHistoryCount;
        }
      }

      currentPage++;
      renderHistoryItems();
    } else {
      hasMoreData = false;
    }
  } catch (error) {
    console.error("Error loading more history data:", error);
    hasMoreData = false;
  } finally {
    isLoading = false;
    hideLoadingSpinner();
  }
};

// Handle scroll event for infinite scroll
const handleScroll = () => {
  if (!scrollContainer) return;

  const { scrollTop, scrollHeight, clientHeight } = scrollContainer;
  const scrollPercentage = (scrollTop + clientHeight) / scrollHeight;

  // Load more when user scrolls to 80% of the content
  if (scrollPercentage > 0.8 && hasMoreData && !isLoading) {
    loadMoreHistoryData();
  }
};

// Show loading spinner
const showLoadingSpinner = () => {
  const spinner = document.getElementById("loadingSpinner");
  if (spinner) {
    spinner.classList.remove("hidden");
  }
};

// Hide loading spinner
const hideLoadingSpinner = () => {
  const spinner = document.getElementById("loadingSpinner");
  if (spinner) {
    spinner.classList.add("hidden");
  }
};

// Update status of existing history items without reloading
const updateHistoryStatus = async () => {
  if (allHistoryData.length === 0) return;
  // Update the status display for each item

  
  allHistoryData.forEach((item, index) => {
    const status = getItemStatus(item);
    const itemElement = document.querySelector(
      `[data-history-index="${index}"]`
    );
    
    if (itemElement) {
      updateItemStatusDisplay(itemElement, item, status, index);
    }
  });
};

// Update individual item status display
const updateItemStatusDisplay = (itemElement, item, status, index) => {


  // Update status badge
  const statusBadge = itemElement.querySelector(".status-badge");
  if (statusBadge) {
    statusBadge.className =
      "status-badge inline-block text-xs px-2 py-1 rounded-full mt-1";
    if (status.claimed) {
      statusBadge.className += " bg-green-100 text-green-600";
      statusBadge.textContent = "CLAIMED";
    } else if (status.expired) {
      statusBadge.className += " bg-red-100 text-red-600";
      statusBadge.textContent = "EXPIRED";
    } else if (status.expiringSoon) {
      statusBadge.className += " bg-orange-100 text-orange-600 animate-pulse";
      statusBadge.textContent = "SEGERA EXPIRED";
    } else {
      statusBadge.textContent = "";
    }
  }

  // Update expiry text (main countdown display)
  const expiryText = itemElement.querySelector(".expiry-text");
  if (expiryText && item.expired_at && !status.claimed) {
    expiryText.className = "expiry-text text-xs mt-1";
    if (status.expired) {
      expiryText.className += " text-red-500";
      expiryText.innerHTML = "⏰ Expired";
    } else if (status.expiringSoon) {
      expiryText.className += " text-orange-600 font-medium";
      expiryText.innerHTML = `⚠️ ${status.remainingTime} (Segera Expired!)`;
    } else {
      expiryText.className += " text-orange-500";
      expiryText.innerHTML = `⏱️ ${status.remainingTime}`;
    }
  }

  // Update countdown in code section (if exists)
  const codeCountdown = itemElement.querySelector(".code-countdown");
  if (codeCountdown && item.expired_at && !status.claimed && !status.expired) {
    codeCountdown.className = "code-countdown text-xs mt-2";
    if (status.expiringSoon) {
      codeCountdown.className += " text-orange-600 font-medium";
    } else {
      codeCountdown.className += " text-orange-600";
    }
    codeCountdown.innerHTML = `Berlaku sampai: ${status.expiredDateFormatted} (${status.remainingTime})`;
  }
};

// Render history modal content with lazy loading
const renderHistoryModal = async () => {
  const totalTransactions = document.getElementById("totalTransactions");
  const modalContent = document.getElementById("historyModalContent");

  // Load initial data
  await loadMoreHistoryData();
  await handleExpiredDate();
  // Update total count from pagination metadata instead of fetching all data
  if (allHistoryData.length === 0) {
    modalContent.innerHTML = `
            <div class="h-full flex items-center justify-center p-8">
                <div class="text-center text-gray-500">
                    <div class="text-6xl mb-4">📋</div>
                    <h3 class="text-lg font-medium mb-2">Belum Ada Riwayat</h3>
                    <p class="text-sm">Mulai tukar poin dengan hadiah menarik!</p>
                </div>
            </div>
        `;
    totalTransactions.textContent = "0";
    return;
  }

  renderHistoryContainer();
  renderHistoryItems();

  // Set up scroll listener for infinite scroll
  scrollContainer = modalContent.querySelector(".history-scroll-container");
  if (scrollContainer) {
    scrollContainer.addEventListener("scroll", handleScroll);
  }
};

// Render the container structure
const renderHistoryContainer = () => {
  const modalContent = document.getElementById("historyModalContent");

  modalContent.innerHTML = `
    <div class="flex flex-col h-full">
        <!-- Summary Stats - Fixed at top -->
        <div class="flex-shrink-0 p-4 pb-2">
            <div class="grid grid-cols-3 gap-2">
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
                    <div class="text-lg font-bold text-red-600 flex items-center justify-center gap-1">
                        <span class="text-yellow-500">⭐</span>
                        <span id="totalPointsUsed">0</span>
                    </div>
                    <p class="text-xs text-red-600 mt-1">Total Poin Terpakai</p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center">
                    <div class="text-lg font-bold text-green-600">
                        <span id="totalClaimed">0</span>
                    </div>
                    <p class="text-xs text-green-600 mt-1">Sudah Di-claim</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-center">
                    <div class="text-lg font-bold text-blue-600">
                        <span id="totalItems">0</span>
                    </div>
                    <p class="text-xs text-blue-600 mt-1">Total Pengembalian Poin</p>
                </div>
            </div>
        </div>
        
        <!-- History List - Takes remaining space -->
        <div class="flex-1 min-h-0 px-4">
            <div class="history-scroll-container h-full overflow-y-auto pb-4" id="historyList">
                <!-- History items will be inserted here -->
            </div>
        </div>
        
        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="hidden flex-shrink-0 text-center py-3">
            <div class="animate-spin inline-block w-6 h-6 border-2 border-gray-300 border-t-blue-600 rounded-full"></div>
            <p class="text-xs text-gray-500 mt-2">Memuat data...</p>
        </div>
        
        <!-- No More Data Message -->
        <div id="noMoreData" class="hidden flex-shrink-0 text-center py-3 text-gray-500">
            <p class="text-xs">Semua data telah dimuat</p>
        </div>
    </div>
  `;
};

// Render history items in the list
const renderHistoryItems = () => {
  const historyList = document.getElementById("historyList");
  const totalPointsUsed = document.getElementById("totalPointsUsed");
  const totalClaimed = document.getElementById("totalClaimed");
  const totalItems = document.getElementById("totalItems");

  if (!historyList) return;

  // Update summary stats
  const totalPoints = allHistoryData.reduce(
    (total, item) => total + item.points,
    0
  );
  const claimedCount = allHistoryData.filter((item) =>
    isClaimed(item.ditukar_tanggal)
  ).length;

  const expiredPointsTotal = allHistoryData.filter((item)=>{
    const status = getItemStatus(item);
    return status.expired;
  }).reduce((total, item) => total + item.points, 0);

  totalPointsUsed.textContent = totalPoints.toLocaleString("id-ID");
  totalClaimed.textContent = claimedCount;
  // Total poin back
  totalItems.textContent = `+ ${expiredPointsTotal.toLocaleString("id-ID")}`;

  // Clear existing items
  historyList.innerHTML = "";

  // Render each history item
  allHistoryData.forEach((item, index) => {
    const status = getItemStatus(item);
    const itemHTML = createHistoryItemHTML(item, status, index);

    const itemElement = document.createElement("div");
    itemElement.innerHTML = itemHTML;
    
    // Set data-history-index on the actual item container (first child)
    const itemContainer = itemElement.firstElementChild;
    if (itemContainer) {
      itemContainer.setAttribute("data-history-index", index);
    }

    // Add margin bottom for spacing except for last item
    if (index < allHistoryData.length - 1) {
      itemContainer.classList.add("mb-3");
    }

    historyList.appendChild(itemContainer);
  });

  // Show "no more data" message if we've loaded everything
  if (!hasMoreData && allHistoryData.length > 0) {
    const noMoreData = document.getElementById("noMoreData");
    if (noMoreData) {
      noMoreData.classList.remove("hidden");
    }
  }
};

// Create HTML for a single history item
const createHistoryItemHTML = (item, status, index) => {
  return `
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden ${
      status.claimed
        ? "border-green-200 bg-green-50"
        : status.expired
        ? "opacity-75 border-red-200"
        : status.expiringSoon
        ? "border-orange-200 bg-orange-50"
        : ""
    }">
        <div class="p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0 pr-3">
                    <h4 class="font-bold text-gray-800 text-sm leading-tight mb-1">${
                      item.reward
                    }</h4>
                    <p class="text-xs text-gray-500 mb-1">${item.cabang}</p>
                    <p class="text-xs text-gray-500">${
                      item.date.split(" ")[0]
                    } • ${item.date.split(" ")[1]}</p>
                    ${
                      status.claimed
                        ? `
                        <p class="text-xs text-green-600 font-medium mt-1">
                            ✅ Sudah Di-claim pada ${status.claimedDateFormatted}
                        </p>
                    `
                        : item.expired_at
                        ? `
                        <p class="expiry-text text-xs ${
                          status.expired
                            ? "text-red-500"
                            : status.expiringSoon
                            ? "text-orange-600 font-medium"
                            : "text-orange-500"
                        } mt-1">
                            ${
                              status.expired
                                ? "⏰ Expired"
                                : status.expiringSoon
                                ? "⚠️ " +
                                  status.remainingTime +
                                  " (Segera Expired!)"
                                : "⏱️ " + status.remainingTime
                            }
                        </p>
                    `
                        : ""
                    }
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="font-bold text-red-500 flex items-center gap-1 text-sm">
                        -<span class="text-yellow-500">⭐</span>${item.points.toLocaleString(
                          "id-ID"
                        )}
                    </p>
                    <span class="status-badge inline-block text-xs px-2 py-1 rounded-full mt-1 ${
                      status.claimed
                        ? "bg-green-100 text-green-600"
                        : status.expired
                        ? "bg-red-100 text-red-600"
                        : status.expiringSoon
                        ? "bg-orange-100 text-orange-600 animate-pulse"
                        : ""
                    }">
                        ${
                          status.claimed
                            ? "CLAIMED"
                            : status.expired
                            ? "EXPIRED"
                            : status.expiringSoon
                            ? "SEGERA EXPIRED"
                            : ""
                        }
                    </span>
                </div>
            </div>
            
            ${createItemContentHTML(item, status, index)}
        </div>
    </div>
  `;
};


const createItemContentHTML = (item, status, index) => {
  if (status.claimed) {
    return `
      <div class="bg-green-50 border-2 border-green-300 rounded-lg p-3">
          <div class="text-center">
              <div class="text-4xl mb-2">🎉</div>
              <p class="text-lg font-bold text-green-600 mb-1">Hadiah Berhasil Di-claim!</p>
              <p class="text-xs text-green-700">Terima kasih telah menggunakan layanan kami</p>
              <p class="text-xs text-green-600 mt-2">
                  Tanggal claim: ${status.claimedDateFormatted}
              </p>
          </div>
      </div>
    `;
  }

  if (item.code && !status.expired) {
    return `
      <div class="bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg p-3 ${
        status.expiringSoon ? "bg-yellow-50 border-yellow-300" : ""
      }">
          <div class="flex items-center justify-between mb-2">
              <span class="text-xs ${
                status.expiringSoon ? "text-yellow-600" : "text-blue-600"
              } font-medium">
                  📍 KODE PENGAMBILAN ${status.expiringSoon ? "⚠️" : ""}
              </span>
              <button onclick="copyToClipboard('${
                item.code
              }')" class="text-xs ${
      status.expiringSoon
        ? "text-yellow-600 hover:text-yellow-800"
        : "text-blue-600 hover:text-blue-800"
    } bg-white px-2 py-1 rounded">
                  📋 Salin
              </button>
          </div>
          <div class="text-center">
              <p class="text-xl font-bold ${
                status.expiringSoon ? "text-yellow-600" : "text-blue-600"
              } tracking-widest mb-1">${item.code}</p>
              <p class="text-xs ${
                status.expiringSoon ? "text-yellow-700" : "text-blue-700"
              }">
                  ${
                    status.expiringSoon
                      ? "SEGERA tunjukkan ke cabang untuk ambil hadiah!"
                      : "Tunjukkan ke cabang untuk ambil hadiah"
                  }
              </p>
              ${
                item.expired_at
                  ? `
                  <p class="code-countdown text-xs ${
                    status.expiringSoon
                      ? "text-orange-600 font-medium"
                      : "text-orange-600"
                  } mt-2">
                      Berlaku sampai: ${status.expiredDateFormatted} (${status.remainingTime})
                  </p>
              `
                  : ""
              }
              
              <!-- QR Code Section -->
              <div class="mt-3 border-t pt-3">
                  <button onclick="toggleQrCode('${
                    item.code
                  }', ${index})" class="text-xs ${
      status.expiringSoon
        ? "text-yellow-600 hover:text-yellow-800"
        : "text-blue-600 hover:text-blue-800"
    } bg-white px-3 py-2 rounded border">
                      <span id="qr-btn-text-${index}">📱 Tampilkan QR Code</span>
                  </button>
                  <div id="qr-container-${index}" class="hidden mt-3">
                      <div class="bg-white p-3 rounded-lg shadow-inner">
                          <div id="qr-loading-${index}" class="text-center text-gray-500 py-4">
                              <div class="animate-spin inline-block w-6 h-6 border-2 border-gray-300 border-t-blue-600 rounded-full"></div>
                              <p class="text-xs mt-2">Memuat QR Code...</p>
                          </div>
                          <img id="qr-image-${index}" class="hidden mx-auto w-32 h-32 border rounded" alt="QR Code">
                      </div>
                      <p class="text-xs text-gray-600 text-center mt-2">Scan QR Code ini untuk verifikasi</p>
                  </div>
              </div>
          </div>
      </div>
    `;
  }

  if (item.code && status.expired) {
    return `
      <div class="bg-red-50 border-2 border-dashed border-red-300 rounded-lg p-3">
          <div class="text-center">
              <p class="text-lg font-bold text-red-500 tracking-widest mb-1">${item.code}</p>
              <p class="text-xs text-red-600">Kode sudah expired pada: ${status.expiredDateFormatted}</p>
              <p class="text-xs text-red-500 mt-1">⚠️ Tidak dapat digunakan lagi</p>
          </div>
      </div>
    `;
  }

  return "";
};

// Toggle QR Code display
window.toggleQrCode = async function (code, index) {
  const container = document.getElementById(`qr-container-${index}`);
  const btnText = document.getElementById(`qr-btn-text-${index}`);
  const loading = document.getElementById(`qr-loading-${index}`);
  const qrImage = document.getElementById(`qr-image-${index}`);

  if (container.classList.contains("hidden")) {
    // Show QR Code
    container.classList.remove("hidden");
    btnText.textContent = "❌ Sembunyikan QR Code";

    // Show loading
    loading.classList.remove("hidden");
    qrImage.classList.add("hidden");

    try {
      // Generate QR Code
      const qrUrl = await generateQrCode(code);

      // Hide loading, show QR
      loading.classList.add("hidden");
      qrImage.src = qrUrl;
      qrImage.classList.remove("hidden");
    } catch (error) {
      console.error("Error generating QR code:", error);
      loading.innerHTML = `
                <div class="text-red-500 text-xs">
                    <p>❌ Gagal memuat QR Code</p>
                    <p>Silakan coba lagi</p>
                </div>
            `;
    }
  } else {
    // Hide QR Code
    container.classList.add("hidden");
    btnText.textContent = "📱 Tampilkan QR Code";

    // Reset state
    loading.classList.remove("hidden");
    qrImage.classList.add("hidden");
    qrImage.src = "";
    loading.innerHTML = `
            <div class="animate-spin inline-block w-6 h-6 border-2 border-gray-300 border-t-blue-600 rounded-full"></div>
            <p class="text-xs mt-2">Memuat QR Code...</p>
        `;
  }
};

// Copy to clipboard function (if not already available globally)
window.copyToClipboard = function (text) {
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard
      .writeText(text)
      .then(() => {
        // Show temporary feedback
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = "✅ Tersalin!";
        button.style.backgroundColor = "#10b981";
        button.style.color = "white";

        setTimeout(() => {
          button.textContent = originalText;
          button.style.backgroundColor = "";
          button.style.color = "";
        }, 2000);
      })
      .catch((err) => {
        console.error("Failed to copy: ", err);
        fallbackCopyTextToClipboard(text);
      });
  } else {
    fallbackCopyTextToClipboard(text);
  }
};

// Fallback copy function for older browsers
function fallbackCopyTextToClipboard(text) {
  const textArea = document.createElement("textarea");
  textArea.value = text;
  textArea.style.top = "0";
  textArea.style.left = "0";
  textArea.style.position = "fixed";

  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();

  try {
    const successful = document.execCommand("copy");
    if (successful) {
      const button = event.target;
      const originalText = button.textContent;
      button.textContent = "✅ Tersalin!";

      setTimeout(() => {
        button.textContent = originalText;
      }, 2000);
    }
  } catch (err) {
    console.error("Fallback: Unable to copy", err);
    alert("Gagal menyalin kode. Silakan salin manual: " + text);
  }

  document.body.removeChild(textArea);
}
