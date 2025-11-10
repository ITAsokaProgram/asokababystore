// Import all handlers
import { summaryHandler } from "./handlers/summaryHandler.js";
import { modalHandler } from "./handlers/modalHandler.js";
import { formHandler } from "./handlers/formHandler.js";
import { animationHandler } from "./handlers/animationHandler.js";
import { utilityHandler } from "./handlers/utilityHandler.js";
import { memberDetailHandler } from "./handlers/memberDetailHandler.js";
import { memberDataHandler } from "./handlers/memberDataHandler.js";

let currentPage = 1;
let totalPages = 1;
let pageSize = 10;

const init = async () => {
  try {
    // Initialize all handlers
    await summaryHandler();

    // Initialize animations
    animationHandler.addHoverAnimations();
  } catch (error) {
    console.error("Error initializing member management module:", error);
    animationHandler.showNotification("Gagal menginisialisasi modul", "error");
  }
};

// Modal member management functions
window.showMemberManagement = async (filter = "all") => {
  // Show modal
  modalHandler.showModal("modalMemberManagement");

  // Initialize member data handler
  await memberDataHandler.initialize();

  // Apply filter if specified
  if (filter !== "all") {
    memberDataHandler.filters.status = filter;
    memberDataHandler.renderMemberList();
  }
};

window.closeMemberManagement = () => {
  modalHandler.closeModal("modalMemberManagement");
};

// Member action functions - delegate to appropriate handlers
window.editMember = (memberId) => {
  utilityHandler.editMember(memberId);
};

window.contactMember = (phoneNumber) => {
  const whatsappUrl = `https://wa.me/${phoneNumber.replace(/[^0-9]/g, "")}`;
  window.open(whatsappUrl, "_blank");
};

window.activateMember = async (memberId, memberName) => {
  // This should be handled by memberDetailHandler
  memberDetailHandler.activateMember(memberId);
};

window.deactivateMember = async (memberId, memberName) => {
  // This should be handled by memberDetailHandler
  memberDetailHandler.deactivateMember(memberId, memberName);
};

window.deleteMember = async (memberId, memberName) => {
  // Delegate to utilityHandler - avoid duplication
  utilityHandler.deleteMember(memberId, memberName);
};

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  init();
});
