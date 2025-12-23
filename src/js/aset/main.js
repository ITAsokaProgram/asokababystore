import { formInsertHandler } from "./handler/formInsertHandler.js";
import { initDataTable } from "./handler/dataTableHandler.js";
import initSelectCabang from "./handler/selectHandler.js";
import initGroupHandler from "./handler/groupHandler.js";
import { formEditHandler } from "./handler/formEditHandler.js";
import initSelectStore from "./handler/selectStoreHandler.js"; // <-- Tambahkan ini

const init = async () => {
  try {
    formInsertHandler();
    formEditHandler();
    initDataTable();
    // populate cabang select and trigger initial table render
    initSelectCabang();
    initGroupHandler();
    initSelectStore(); // <-- Panggil fungsi di sini
  } catch (error) {
    console.error("Error", error);
  }
};

init();