import {
  cardContainer,
  cardContainerAll,
  detailCabang,
  detailCabangAll,
} from "./card.js";
import { getTransCabang, getTransCabangDetail } from "./fetch.js";

const init = async () => {
  const params = new URLSearchParams(window.location.search);
  const cabang = params.get("cabang");

  if (cabang) {
    // Halaman DETAIL
    const responseDetail = await getTransCabangDetail(cabang);
    if (cabang === "all") {
      detailCabangAll(responseDetail.data);
    } else {
      detailCabang(responseDetail.data);
    }
  } else {
      const responseTrans = await getTransCabang();
      cardContainer(responseTrans.data);
      cardContainerAll(responseTrans.data_all);
  }
};

init();
