const SidebarToggle = (() => {
  const init = () => {
    const toggleBtn = document.getElementById("toggle-sidebar");
    const sidebar = document.getElementById("sidebar");
    const closeBtn = document.getElementById("closeSidebar");

    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("open");
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        sidebar.classList.remove("open");
      });
    }
  };

  return {
    init,
  };
})();

export default SidebarToggle;
