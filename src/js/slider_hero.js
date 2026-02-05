window.addEventListener('load', function() {
  fetch("public/slider.json?ca=" + Date.now())
    .then((res) => res.json())
    .then((data) => {
      const wrapper = document.getElementById("carousel-wrapper");
      const homeSection = document.getElementById("home-section");
      const today = new Date().toISOString().split("T")[0];
      const filteredData = data.filter((item) => item.tanggal_selesai > today);
      const totalSlides = filteredData.length; 
      if (totalSlides === 0) {
        homeSection.style.display = "none";
        if (window.AOS) AOS.init();
        return;
      }
      homeSection.style.display = "block";
      filteredData.forEach((item) => {
        const slide = document.createElement("div");
        slide.className = "swiper-slide relative w-full flex items-center justify-center";
        slide.innerHTML = `
        <div class="relative w-full md:h-screen h-auto">
          <img src="https://asokababystore.com${encodeURI(item.path)}"
              class="w-full md:h-full h-auto object-cover md:object-contain" 
              alt="${item.filename}"
              fetchpriority="high" decoding="sync"
              >
        </div>
      `;
        wrapper.appendChild(slide);
      });
      const isSingleSlide = totalSlides === 1;
      new Swiper(".mySwiper", {
        loop: !isSingleSlide, 
        autoplay: isSingleSlide ? false : {
          delay: 3000,
          disableOnInteraction: false,
        },
        effect: "fade",
        fadeEffect: { crossFade: true },
        autoHeight: true,
        allowTouchMove: !isSingleSlide, 
      });
      if (window.AOS) AOS.init();
    })
    .catch(err => {
        console.error("Gagal memuat slider:", err);
        const homeSection = document.getElementById("home-section");
        if(homeSection) homeSection.style.display = "none";
    });
});