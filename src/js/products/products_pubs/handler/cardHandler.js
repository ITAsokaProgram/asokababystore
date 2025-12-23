// Clean implementation that uses API fields (nama_produk, harga_jual, deskripsi, image_url)
import { api } from "../services/api.js";

// Global flag to prevent multiple cards being clicked while one is loading
let GLOBAL_CARD_LOADING = false;
function createProductCard(product) {
  const totalStock = Array.isArray(product.stocks)
    ? product.stocks.reduce((a, s) => a + (Number(s.qty) || 0), 0)
    : (Number(product.qty) || 0);

  const card = document.createElement('div');
  card.className = 'bg-white rounded-2xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 overflow-hidden border border-gray-100';

  const imageHTML = product.image_url
    ? `<img src="${product.image_url}" alt="${(product.nama_produk||product.name||'produk')}" class="w-full h-full object-cover"/>`
    : `<i class="fas fa-shopping-bag text-6xl text-gray-400"></i>`;

  card.innerHTML = `
    <div class="bg-gradient-to-br from-[var(--primary-50)] via-white to-[var(--accent-50)] h-48 flex items-center justify-center text-6xl border-b border-sky-100">${imageHTML}</div>
    <div class="p-5">
      <h3 class="font-bold text-sm text-gray-800 mb-2 line-clamp-2">${product.nama_produk || product.name || ''}</h3>
      <p class="text-gray-600 text-sm mb-3 line-clamp-2">${product.deskripsi || product.description || ''}</p>
      <div class="flex items-center justify-between mb-3">
        <div class="text-sm font-bold" style="color:var(--primary)">Rp ${Number(product.harga_jual||product.price||0).toLocaleString('id-ID')}</div>
        <!-- stock/ids/barcode/plu intentionally omitted on card per request -->
      </div>
    </div>`;

  const btn = card.querySelector('.btn-detail');

  // Click handler with loading state to prevent spam clicks
  async function handleCardClick(e) {
    if (e) e.stopPropagation();
  // If any card is currently loading, ignore clicks on other cards
  if (card._loading || GLOBAL_CARD_LOADING) return; // already loading
  card._loading = true;
  GLOBAL_CARD_LOADING = true;

    // ensure relative positioning so overlay fits
    const prevPosition = card.style.position;
    if (!prevPosition || prevPosition === 'static') card.style.position = 'relative';

    const overlay = document.createElement('div');
    overlay.className = 'absolute inset-0 bg-white/70 flex items-center justify-center z-10 rounded-2xl';
    overlay.innerHTML = '<i class="fas fa-spinner fa-spin text-2xl text-gray-500"></i>';
    card.appendChild(overlay);

    try {
  // test delay: wait 10 seconds so clicks remain disabled and overlay is visible
  await new Promise((res) => setTimeout(res, 10000));
  await showProductDetail(product);
    } catch (err) {
      // keep console debug but don't block UI cleanup
      console.error('showProductDetail error:', err);
    } finally {
      card._loading = false;
      GLOBAL_CARD_LOADING = false;
      try { overlay.remove(); } catch (e) {}
      // restore previous position style if it was empty
      if (!prevPosition || prevPosition === 'static') card.style.position = prevPosition || '';
    }
  }

  if (btn) btn.addEventListener('click', handleCardClick);
  card.addEventListener('click', handleCardClick);
  return card;
}

// ====== RENDER GRID ======
export function renderProducts(products = []) {
  const grid = document.getElementById("productsGrid");
  const empty = document.getElementById("emptyState");
  grid.innerHTML = "";

  if (!Array.isArray(products) || products.length === 0) {
    empty.classList.remove("hidden");
    return;
  }

  empty.classList.add("hidden");

  const frag = document.createDocumentFragment();
  products.forEach((p) => frag.appendChild(createProductCard(p)));
  grid.appendChild(frag);
}

// ====== MODAL DETAIL + WA CTA ======
async function showProductDetail(product) {
  const modal = document.getElementById("productModal");
  const content = document.getElementById("modalContent");
  if (!product) return; // nothing to show

  // fetch fresh detail from API when possible and support multiple store rows
  const id = product.id || product.ID || product.product_id;
  let detail = null;
  let detailRows = [];
  try {
    const res = await api.getDetailData(id);
    if (Array.isArray(res) && res.length) {
      detailRows = res;
      detail = res[0];
    } else if (res && Array.isArray(res.data) && res.data.length) {
      detailRows = res.data;
      detail = res.data[0];
    } else if (res && Array.isArray(res.product) && res.product.length) {
      detailRows = res.product;
      detail = res.product[0];
    } else if (res && res.success && Array.isArray(res.data) && res.data.length) {
      detailRows = res.data;
      detail = res.data[0];
    } else if (res && typeof res === 'object') {
      // single-object response: could be top-level product or a single store row
      detail = res;
      if (Array.isArray(res.stocks) && res.stocks.length) {
        detailRows = res.stocks.slice();
      } else if (res.nm_store || res.kd_store) {
        detailRows = [res];
      }
    }
  } catch (err) {
    // fail silently and fallback to provided product
    detail = null;
    detailRows = [];
  }

  // prefer fields from detail (or merge with given product), but keep rows for branch select
  const p = Object.assign({}, product, detail || (detailRows[0] || {}));
  document.getElementById("modalTitle").textContent = p.nama_produk || p.name || '';

  // build branch chips / select
  let chipsLocal = '';
  let selectHTML = '<option value="" disabled>Stok habis di semua cabang</option>';
  // build stocks array from detailRows or p.stocks
  let stocks = [];
  if (Array.isArray(detailRows) && detailRows.length) {
    stocks = detailRows.map(r => ({
      branch: r.nm_store || '-',
      code: r.kd_store || '',
      qty: Number(r.qty) || 0,
      phone: r.store_phone || ''
    }));
  } else if (Array.isArray(p.stocks)) {
    stocks = p.stocks.map(s => ({ branch: s.branch || s.nm_store || '-', code: s.code || s.kd_store || '', qty: Number(s.qty) || 0, phone: s.phone || '' }));
  }

  // helper function to clean store names
  const cleanStoreName = (name) => {
    if (!name) return '-';
    return String(name).replace(/^(ASOKA\s+BABY\s+STORE|ASOKA\s+BABY)\s*/i, '').trim() || name;
  };

  if (stocks.length) {
    // Only include stocks with qty not null/undefined/empty and > 0
    const available = stocks.filter((s) => s.qty !== null && s.qty !== undefined && s.qty !== '' && Number(s.qty) > 0);
    chipsLocal = available.map((s) => {
      const cleanName = cleanStoreName(s.branch);
      return `<span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-map-marker-alt"></i> ${cleanName}</span>`;
    }).join(' ');
    selectHTML = available.map(s => {
      const cleanName = cleanStoreName(s.branch);
      const phoneAttr = (s.phone || '').toString().replace(/"/g, '&quot;');
      return `<option value="${s.code}" data-name="${cleanName}" data-phone="${phoneAttr}">${cleanName} - Stok: ${s.qty}</option>`;
    }).join('');
  } else if (p.nm_store || p.kd_store) {
    const q = Number(p.qty) || 0;
    const cleanName = cleanStoreName(p.nm_store || p.cabang);
    chipsLocal = `<span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-map-marker-alt"></i> ${cleanName}</span>`;
    const phoneAttr = (p.store_phone || p.tlp || p.phone || '')?.toString().replace(/"/g, '&quot;');
    selectHTML = `<option value="${p.kd_store||''}" data-name="${cleanName}" data-phone="${phoneAttr}">${cleanName} - Stok: ${q}</option>`;
  }

  const modalImage = p.image_url ? `<img src="${p.image_url}" alt="${p.nama_produk||''}" class="w-full h-full object-cover"/>` : `<i class="fas fa-shopping-bag text-6xl text-gray-400"></i>`;
  content.innerHTML = `
        <div class="grid md:grid-cols-2 gap-8">
          <div class="bg-gradient-to-br from-[var(--primary-50)] to-[var(--accent-50)] h-80 rounded-2xl flex items-center justify-center text-8xl border" style="border-color:rgba(20,184,166,0.08)">${modalImage}</div>
          <div>
            <div class="text-3xl font-bold mb-4" style="color:var(--primary)">Rp ${Number(p.harga_jual||p.price||0).toLocaleString('id-ID')}</div>
            
            <div class="bg-[var(--primary-50)] p-4 rounded-xl mb-4">
              <p class="text-gray-700 leading-relaxed">${p.deskripsi || p.details || p.description || ''}</p>
            </div>

            <div class="mb-4">
              <div class="text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-store mr-2"></i>Tersedia di cabang:</div>
              <div class="flex flex-wrap gap-1">${ chipsLocal || '<span class="text-sm text-gray-500 bg-gray-50 px-3 py-1 rounded-full"><i class="fas fa-times mr-1"></i>Belum ada stok</span>' }</div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="branchSelect"><i class="fas fa-map-marker-alt mr-2"></i>Pilih Cabang</label>
                <select id="branchSelect" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 shadow-sm focus:outline-none focus:ring-4 focus:ring-gray-200">${selectHTML}</select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="qtyInput"><i class="fas fa-shopping-cart mr-2"></i>Jumlah</label>
                <input id="qtyInput" type="number" min="1" class="w-full rounded-xl border border-orange-200 bg-white px-3 py-2.5 shadow-sm focus:outline-none focus:ring-4 focus:ring-orange-200" placeholder="1" />
              </div>
            </div>

            <div class="bg-blue-50 p-4 rounded-xl mb-4">
              <div class="text-sm font-semibold text-blue-800 mb-2"><i class="fas fa-gift mr-2"></i>Keuntungan Member Asoka:</div>
              <ul class="text-sm text-blue-700 space-y-1">
                <li><i class="fas fa-percentage mr-2"></i>Diskon khusus</li>
                <li><i class="fas fa-box mr-2"></i>Gratis bungkus kado</li>
                <li><i class="fas fa-star mr-2"></i>Tukar poin dengan hadiah menarik</li>
              </ul>
            </div>

            <p class="text-xs text-gray-500 mb-3"><i class="fab fa-whatsapp mr-2"></i>Klik tombol di bawah untuk chat langsung via WhatsApp</p>
            <a id="waButton" href="#" target="_blank" rel="noopener"
               class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-white font-semibold shadow-lg hover:opacity-95 transition-all transform hover:scale-105"
               style="background: linear-gradient(135deg, #25D366 0%, #20ba5a 100%)">
               <i class="fab fa-whatsapp"></i>Chat via WhatsApp</a>
          </div>
        </div>`;

  // Update WA link
  const branchSelect = content.querySelector("#branchSelect");
  const qtyInput = content.querySelector("#qtyInput");
  const waButton = content.querySelector("#waButton");

  function updateWa() {
    const DEFAULT_WHATSAPP = '';
    const selectedOption = branchSelect?.options[branchSelect.selectedIndex];
    const phoneFromOption = selectedOption?.dataset?.phone || '';
    const WHATSAPP_PHONE = phoneFromOption && phoneFromOption.trim() ? phoneFromOption.trim() : DEFAULT_WHATSAPP;
    const name = selectedOption?.dataset?.name || "-";
    const qty =
      qtyInput.value && Number(qtyInput.value) > 0 ? Number(qtyInput.value) : 1;
    const productName = p.nama_produk || p.name || '';
    const sku = p.sku || '';
    
    const lines = [
      "Halo Asoka Baby Store,",
      "",
      "Saya tertarik untuk memesan produk berikut:",
      "",
      `Nama Produk: ${productName}`,
      sku ? `SKU: ${sku}` : null,
      `Cabang: ${name}`,
      `Jumlah: ${qty} pcs`,
      "",
      "Mohon informasi ketersediaan stok dan cara pemesanannya.",
      "",
      "Terima kasih."
    ].filter(line => line !== null);
    
    const text = encodeURIComponent(lines.join("\n"));
  waButton.href = `https://wa.me/+62${WHATSAPP_PHONE}?text=${text}`;
  }
  branchSelect && branchSelect.addEventListener("change", updateWa);
  qtyInput &&
    qtyInput.addEventListener("input", () => {
      clearTimeout(qtyInput._t);
      qtyInput._t = setTimeout(updateWa, 150);
    });
  updateWa();

  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

export { createProductCard, showProductDetail };
