import getCookie from '../../index/utils/cookies.js';

export async function initSelectCabang() {
  const sel = document.getElementById('filterCabang');
  if (!sel) return;

  // clear existing options
  sel.innerHTML = '';

  const token = getCookie('token');
  const url = '/src/api/cabang/get_kode.php';
  try {
    const res = await fetch(url, {
      method: 'GET',
      headers: token ? { Authorization: `Bearer ${token}` } : {}
    });
    if (!res.ok) throw new Error('Gagal memuat data cabang');
    const json = await res.json();
    const items = json.data || [];

    if (!items.length) {
      // fallback: keep a 'Semua Cabang' option
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = 'Semua Cabang';
      sel.appendChild(opt);
      // trigger initial render with no kd_store
      if (typeof window.renderAsetTable === 'function') window.renderAsetTable({kd_store: '', page: 1});
      return;
    }

    // populate with alias names; first item selected by default
    items.forEach((it, idx) => {
      const opt = document.createElement('option');
      opt.value = it.store || it.Kd_Store || it.kd_store || ''; // be flexible
      opt.textContent = it.nama_cabang || it.Nm_Alias || it.nm_alias || opt.value;
      sel.appendChild(opt);
    });

    // default select first alias (no 'Semua Cabang')
    const firstVal = sel.options[0].value;
    sel.value = firstVal;
    if (typeof window.renderAsetTable === 'function') window.renderAsetTable({kd_store: firstVal, page: 1});

    sel.addEventListener('change', (e) => {
      const v = e.target.value;
      if (typeof window.renderAsetTable === 'function') window.renderAsetTable({kd_store: v, page: 1});
    });

  } catch (err) {
    console.error('initSelectCabang error', err);
    // fallback
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = 'Semua Cabang';
    sel.appendChild(opt);
    if (typeof window.renderAsetTable === 'function') window.renderAsetTable({kd_store: '', page: 1});
  }
}

export default initSelectCabang;
