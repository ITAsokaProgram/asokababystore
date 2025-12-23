const showLoading = () => {
  document.getElementById("loading-state").classList.remove("hidden");
  document.getElementById("empty-state").classList.add("hidden");
  document.getElementById("top-sales-table-body").innerHTML = "";
};
const hideLoading = () => {
  document.getElementById("loading-state").classList.add("hidden");
  document.getElementById("empty-state").classList.add("hidden");
};
const showEmptyState = () => {
  document.getElementById("loading-state").classList.add("hidden");
  document.getElementById("empty-state").classList.remove("hidden");
  document.getElementById("top-sales-table-body").innerHTML = "";
};
const showGlobalLoading = () => {
  document.getElementById("global-loader").classList.remove("hidden");
};
const hideGlobalLoading = () => {
  document.getElementById("global-loader").classList.add("hidden");
};
const formatCurrency = (amount) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};
const formatNumber = (number) => {
  return new Intl.NumberFormat("id-ID").format(number);
};
const formatPercentage = (value) => {
  return `${parseFloat(value).toFixed(2)}%`;
};
const showToast = (message, type = "success") => {
  const colors = {
    success: "linear-gradient(to right, #00b09b, #96c93d)",
    error: "linear-gradient(to right, #ff5f6d, #ffc371)",
    warning: "linear-gradient(to right, #f093fb, #f5576c)",
    info: "linear-gradient(to right, #4facfe, #00f2fe)",
  };
  Toastify({
    text: message,
    duration: 3000,
    gravity: "top",
    position: "right",
    backgroundColor: colors[type],
    stopOnFocus: true,
  }).showToast();
};
const showConfirmation = (message, onConfirm) => {
  Swal.fire({
    title: "Konfirmasi",
    text: message,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      onConfirm();
    }
  });
};
export {
  showLoading,
  hideLoading,
  showEmptyState,
  showGlobalLoading,
  hideGlobalLoading,
  formatCurrency,
  formatNumber,
  formatPercentage,
  showToast,
  showConfirmation,
};
