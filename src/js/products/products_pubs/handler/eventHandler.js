export function setupEventListeners({ onFilterProducts, onHideProductDetail }) {
  // Category buttons
  document.querySelectorAll(".category-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      document
        .querySelectorAll(".category-btn")
        .forEach((b) => b.classList.remove("!text-white"));
      document
        .querySelectorAll(".category-btn")
        .forEach((b) => (b.style.background = ""));
      this.style.background = `linear-gradient(135deg, ${getComputedStyle(
        document.documentElement
      ).getPropertyValue("--primary")} 0%, ${getComputedStyle(
        document.documentElement
      ).getPropertyValue("--primary-dark")} 100%)`;
      this.classList.add("!text-white");
      const category = this.dataset.category;
      if (typeof onFilterProducts === 'function') onFilterProducts({ category });
    });
  });

  // Price filter select
  const priceSelect = document.getElementById('priceFilter');
  if (priceSelect) {
    priceSelect.addEventListener('change', () => {
      const val = priceSelect.value;
      if (typeof onFilterProducts === 'function') onFilterProducts({ sortPrice: val });
    });
  }

  // Modal close handlers
  const closeBtn = document.getElementById("closeModal");
  if (closeBtn) closeBtn.addEventListener("click", () => { if (typeof onHideProductDetail === 'function') onHideProductDetail(); });

  // Product modal close handler
  const productModal = document.getElementById("productModal");
  if (productModal) productModal.addEventListener("click", (e) => { if (e.target.id === "productModal" && typeof onHideProductDetail === 'function') onHideProductDetail(); });
}
