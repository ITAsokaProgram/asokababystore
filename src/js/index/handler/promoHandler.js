import imagePromo from "../fetch/fetch_images.js";

export const imagePromoHandler = async () => {
  const data = await imagePromo();
  const container = document.getElementById("promo-container");
  const noPromoMessage = document.getElementById("no-promo");

  const today = new Date().toISOString().split("T")[0];

  const filteredData = data.filter((item) => item.tanggal_selesai > today);

  if (filteredData.length === 0) {
    if (noPromoMessage) noPromoMessage.classList.remove("hidden");
    if (container) container.classList.add("hidden");
    return;
  } else {
    if (noPromoMessage) noPromoMessage.classList.add("hidden");
    if (container) container.classList.remove("hidden");
  }

  container.innerHTML = filteredData
    .map(
      (item) => `
        <div class="p-2 border rounded-xl hover:shadow-md transition dark:border-gray-600">
            <img src="https://asokababystore.com${item.path}" alt="${item.filename}" class="rounded-lg w-full object-cover hover:scale-105 transition-transform preview-image" data-full="https://asokababystore.com${item.path}" />
        </div>
    `
    )
    .join("");

  document.querySelectorAll(".preview-image").forEach((img) => {
    img.addEventListener("click", () => {
      const modal = document.getElementById("imageModal");
      const modalImg = document.getElementById("modalImage");
      modalImg.src = img.dataset.full;
      modal.classList.remove("hidden");
    });
  });

  document.getElementById("imageModal").addEventListener("click", (e) => {
    if (e.target.id === "modalImage") {
      e.currentTarget.classList.add("hidden");
    }
  });
};

export const imagePromoHandlerHome = async () => {
  const data = await imagePromo();
  const container = document.getElementById("promo-container");

  const today = new Date().toISOString().split("T")[0];

  const filteredData = data.filter((item) => item.tanggal_selesai > today);

  container.innerHTML = filteredData
    .slice(0, 3)
    .map(
      (item) => `
        <div class="p-2 border rounded-xl hover:shadow-md transition dark:border-gray-600">
            <img src="https://asokababystore.com${item.path}" alt="${item.filename}" class="rounded-lg w-full object-cover hover:scale-105 transition-transform preview-image" data-full="https://asokababystore.com${item.path}" />
        </div>
    `
    )
    .join("");

  document.querySelectorAll(".preview-image").forEach((img) => {
    img.addEventListener("click", () => {
      const modal = document.getElementById("imageModal");
      const modalImg = document.getElementById("modalImage");
      modalImg.src = img.dataset.full;
      modal.classList.remove("hidden");
    });
  });

  document.getElementById("imageModal").addEventListener("click", (e) => {
    if (e.target.id === "modalImage") {
      e.currentTarget.classList.add("hidden");
    }
  });
};

export default { imagePromoHandler, imagePromoHandlerHome };
