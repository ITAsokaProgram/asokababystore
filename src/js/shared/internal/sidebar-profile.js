document
  .getElementById("toggle-sidebar")
  .addEventListener("click", function () {
    document.getElementById("sidebar").classList.toggle("open");
  });
document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const closeBtn = document.getElementById("closeSidebar");

  closeBtn.addEventListener("click", function () {
    sidebar.classList.remove("open");
  });
});
document.getElementById("toggle-hide").addEventListener("click", function () {
  var sidebarTexts = document.querySelectorAll(".sidebar-text");
  let mainContent = document.getElementById("main-content");
  let sidebar = document.getElementById("sidebar");
  var toggleButton = document.getElementById("toggle-hide");
  var icon = toggleButton.querySelector("i");

  if (sidebar.classList.contains("w-64")) {
    sidebar.classList.remove("w-64", "px-5");
    sidebar.classList.add("w-16", "px-2");
    sidebarTexts.forEach((text) => text.classList.add("hidden"));
    mainContent.classList.remove("ml-64");
    mainContent.classList.add("ml-16");
    toggleButton.classList.add("left-20");
    toggleButton.classList.remove("left-64");
    icon.classList.remove("fa-angle-left");
    icon.classList.add("fa-angle-right");
  } else {
    sidebar.classList.remove("w-16", "px-2");
    sidebar.classList.add("w-64", "px-5");
    sidebarTexts.forEach((text) => text.classList.remove("hidden"));
    mainContent.classList.remove("ml-16");
    mainContent.classList.add("ml-64");
    toggleButton.classList.add("left-64");
    toggleButton.classList.remove("left-20");
    icon.classList.remove("fa-angle-right");
    icon.classList.add("fa-angle-left");
  }
});
document.addEventListener("DOMContentLoaded", function () {
  const profileImg = document.getElementById("profile-img");
  const profileCard = document.getElementById("profile-card");

  profileImg.addEventListener("click", function (event) {
    event.preventDefault();
    profileCard.classList.toggle("show");
  });

  document.addEventListener("click", function (event) {
    if (
      !profileCard.contains(event.target) &&
      !profileImg.contains(event.target)
    ) {
      profileCard.classList.remove("show");
    }
  });
});
