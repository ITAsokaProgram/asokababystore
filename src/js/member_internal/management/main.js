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
    await summaryHandler();
    animationHandler.addHoverAnimations();
    const urlParams = new URLSearchParams(window.location.search);
    const kdCustFromUrl = urlParams.get("kd_cust");
    if (kdCustFromUrl) {
      console.log(
        `Mendeteksi kd_cust dari URL: ${kdCustFromUrl}. Membuka modal...`
      );
      modalHandler.showModal("modalMemberManagement");
      try {
        await memberDataHandler.initialize();
        await memberDetailHandler.loadMemberDetail(kdCustFromUrl);
        if (typeof memberDataHandler.highlightMemberInList === "function") {
          memberDataHandler.highlightMemberInList(kdCustFromUrl);
        }
      } catch (dataError) {
        console.error(
          "Gagal memuat data member dari parameter URL:",
          dataError
        );
        animationHandler.showNotification(
          "Gagal memuat detail member.",
          "error"
        );
      }
    }
  } catch (error) {
    console.error("Error initializing member management module:", error);
    animationHandler.showNotification("Gagal menginisialisasi modul", "error");
  }
};
window.showMemberManagement = async (filter = "all") => {
  modalHandler.showModal("modalMemberManagement");
  await memberDataHandler.initialize();
  if (filter !== "all") {
    memberDataHandler.filters.status = filter;
    memberDataHandler.renderMemberList();
  }
};
window.closeMemberManagement = () => {
  modalHandler.closeModal("modalMemberManagement");
};
window.editMember = (memberId) => {
  utilityHandler.editMember(memberId);
};
window.contactMember = (phoneNumber) => {
  const whatsappUrl = `https://wa.me/${phoneNumber.replace(/[^0-9]/g, "")}`;
  window.open(whatsappUrl, "_blank");
};
window.activateMember = async (memberId, memberName) => {
  memberDetailHandler.activateMember(memberId);
};
window.deactivateMember = async (memberId, memberName) => {
  memberDetailHandler.deactivateMember(memberId, memberName);
};
window.deleteMember = async (memberId, memberName) => {
  utilityHandler.deleteMember(memberId, memberName);
};
document.addEventListener("DOMContentLoaded", () => {
  init();
});
