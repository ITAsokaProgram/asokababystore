
const qs = s => document.querySelector(s);

export function initGroupHandler() {
  const input = qs('#filter_group_aset');
  const suggestionsBox = qs('#group_suggestions');
  const clearBtn = qs('#clearFilters');

  if (input) {
    let t;
    input.addEventListener('input', (e) => {
      clearTimeout(t);
      const v = e.target.value.trim();
      t = setTimeout(async () => {
        try {
          const url = `/src/api/aset/get_group_suggestions.php?q=${encodeURIComponent(v)}`;
          const res = await fetch(url);
          const json = await res.json();
          const items = json.data || [];
          suggestionsBox.innerHTML = '';
          if (items.length === 0) {
            suggestionsBox.classList.add('hidden');
            return;
          }
          items.forEach(it => {
            const div = document.createElement('div');
            div.className = 'px-3 py-2 hover:bg-slate-100 cursor-pointer text-sm';
            div.textContent = it;
            div.addEventListener('click', () => {
              input.value = it;
              suggestionsBox.classList.add('hidden');
              // trigger filter
              if (typeof window.renderAsetTable === 'function') window.renderAsetTable({group_aset: it, page: 1});
            });
            suggestionsBox.appendChild(div);
          });
          suggestionsBox.classList.remove('hidden');
        } catch (err) {
          console.error('group suggestion error', err);
        }
      }, 250);
    });

    // hide suggestions on blur
    input.addEventListener('blur', () => {
      setTimeout(() => suggestionsBox.classList.add('hidden'), 150);
      const v = input.value.trim();
      if (typeof window.renderAsetTable === 'function') window.renderAsetTable({group_aset: v, page: 1});
    });
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const v = input.value.trim();
        if (typeof window.renderAsetTable === 'function') window.renderAsetTable({group_aset: v, page: 1});
        suggestionsBox.classList.add('hidden');
      }
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      // clear filters: search, dates, cabang, group
      const ids = ['#filterSearch','#filter_tanggal_beli_from','#filter_tanggal_beli_to','#filter_tanggal_perbaikan_from','#filter_tanggal_perbaikan_to','#filter_tanggal_rusak_from','#filter_tanggal_rusak_to','#filter_tanggal_mutasi_from','#filter_tanggal_mutasi_to','#filter_group_aset'];
      ids.forEach(id => {
        const el = document.querySelector(id);
        if (!el) return;
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
      });
      // trigger table render with cleared filters
      if (typeof window.renderAsetTable === 'function') window.renderAsetTable({search: '', kd_store: document.getElementById('filterCabang')?.value || '', group_aset: '', page: 1, tanggal_beli_from: '', tanggal_beli_to: '', tanggal_perbaikan_from: '', tanggal_perbaikan_to: '', tanggal_rusak_from: '', tanggal_rusak_to: '', tanggal_mutasi_from: '', tanggal_mutasi_to: ''});
    });
  }
}

export default initGroupHandler;
