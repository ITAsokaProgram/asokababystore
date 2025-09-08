import { api } from "./services/api.js";
import { renderProducts } from "./handler/cardHandler.js";
import { setupEventListeners } from "./handler/eventHandler.js";

// Local state
let allProducts = [];
let currentCategory = 'all';

function applyFiltersAndRender({ category, sortPrice } = {}){
  if (category) currentCategory = category;
  let list = Array.isArray(allProducts) ? [...allProducts] : [];
  if (currentCategory && currentCategory !== 'all') {
    const want = String(currentCategory).toLowerCase();
    list = list.filter(p => String((p.kategori||p.category||'')).toLowerCase() === want);
  }
  if (sortPrice === 'termurah') {
    list.sort((a,b) => (Number(a.harga_jual||a.price||0) || 0) - (Number(b.harga_jual||b.price||0) || 0));
  } else if (sortPrice === 'termahal') {
    list.sort((a,b) => (Number(b.harga_jual||b.price||0) || 0) - (Number(a.harga_jual||a.price||0) || 0));
  }
  renderProducts(list);
}

function hideProductDetail(){
  const modal = document.getElementById('productModal');
  if (modal) { modal.classList.add('hidden'); document.body.style.overflow='auto'; }
}

// ====== INIT ======
const init = async () => {
  const res = await api.getData();
  // try to normalize response into array
  let products = [];
  if (Array.isArray(res)) products = res;
  else if (res && Array.isArray(res.data)) products = res.data;
  else if (res && Array.isArray(res.products)) products = res.products;
  // fallback: if res has success:true and data inside
  allProducts = products;
  applyFiltersAndRender();
  setupEventListeners({ onFilterProducts: applyFiltersAndRender, onHideProductDetail: hideProductDetail });
};

init();
// Utility: line clamp helper
const style = document.createElement("style");
style.textContent = `
      .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
      @keyframes fadeIn{from{opacity:0;transform:scale(0.95)}to{opacity:1;transform:scale(1)}}
      #productModal{animation:fadeIn .3s ease-out}
    `;
document.head.appendChild(style);
