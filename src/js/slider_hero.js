fetch("public/slider.json?ca=" + Date.now())
  .then((res) => res.json())
  .then((data) => {
    const wrapper = document.getElementById("carousel-wrapper");

    const today = new Date().toISOString().split("T")[0];

    const filteredData = data.filter((item) => item.tanggal_selesai > today);

    filteredData.forEach((item) => {
      const slide = document.createElement("div");
      slide.className =
        "swiper-slide relative w-full flex items-center justify-center";

      slide.innerHTML = `
      <div class="relative w-full md:h-screen h-auto">
        <img src="https://asokababystore.com${encodeURI(item.path)}"
             class="w-full md:h-full h-auto object-cover md:object-contain" 
             alt="${item.filename}"
             onload="this.closest('.swiper-slide').style.height = 'auto'">
      </div>
    `;

      wrapper.appendChild(slide);
    });

    new Swiper(".mySwiper", {
      loop: true,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      effect: "fade",
      fadeEffect: { crossFade: true },
      autoHeight: true,
    });

    if (window.AOS) AOS.init();
  });
