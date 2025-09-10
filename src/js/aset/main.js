import { formInsertHandler } from "./handler/formInsertHandler.js";
import { initDataTable } from "./handler/dataTableHandler.js";
import initSelectCabang from "./handler/selectHandler.js";
import initGroupHandler from "./handler/groupHandler.js";

const init = async () => {
  try {
    formInsertHandler();
    initDataTable();
    // populate cabang select and trigger initial table render
    initSelectCabang();
    initGroupHandler();
  } catch (error) {
    console.error("Error", error);
  }
};

init();
