function showProgressBar() {
    document.getElementById("progressOverlay").classList.remove("hidden");
    gsap.set("#progressBar", { width: "0%" });

    // Simulasi progress (karena fetch nggak punya progress bawaan)
    window.fakeProgress = gsap.to("#progressBar", {
        width: "90%", // stop di 90% biar kelihatan belum selesai
        duration: 2,
        ease: "power1.inOut"
    });
}

function completeProgressBar() {
    if (window.fakeProgress) window.fakeProgress.kill(); // Stop animasi awal
    gsap.to("#progressBar", {
        width: "100%",
        duration: 0.5,
        onComplete: () => {
            setTimeout(() => {
                document.getElementById("progressOverlay").classList.add("hidden");
            }, 300); // delay dikit biar smooth
        }
    });
}