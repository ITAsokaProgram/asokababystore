export const openModal = (modalId, contentId, btnId) => {
    const open = document.getElementById(btnId);
    const modal = document.getElementById(modalId);
    const modalContent = document.getElementById(contentId);

    open.addEventListener("click", () => {
        modal.classList.remove("hidden");
        gsap.fromTo(
            modalContent, {
            opacity: 0,
            scale: 0.9
        }, {
            opacity: 1,
            scale: 1,
            duration: 0.5,
            ease: "power2.out",
        }
        );
    });
}

export const closeModalProfile = (btnId, modalId, contentId) => {
    const closeModal = document.getElementById(btnId);
    const modal = document.getElementById(modalId);
    const modalContent = document.getElementById(contentId);
    closeModal.addEventListener("click", () => {
        gsap.to(modalContent, {
            opacity: 0,
            scale: 0.9,
            duration: 0.3,
            ease: "power2.in",
            onComplete: () => {
                modal.classList.add("hidden");
            },
        });
    });
}

export const closeModalTerms = (modalId, contentId) => {
    const modal = document.getElementById(modalId);
    const modalContent = document.getElementById(contentId);

    gsap.to(modalContent, {
        opacity: 0,
        scale: 0.9,
        duration: 0.3,
        ease: "power2.in",
        onComplete: () => {
            modal.classList.add("hidden");
        },
    });
}


export default {openModal,closeModalProfile,closeModalTerms}