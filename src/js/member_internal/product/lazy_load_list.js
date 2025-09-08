// Fungsi lazy load list reusable
export const lazyLoadList = (data, containerId, renderItem, batchSize = 10) => {
  const container = document.getElementById(containerId);
  if (!container) return;

  let currentIndex = 0;
  let loading = false;

  // Wrapper untuk list
  let listWrapper = document.createElement('div');
  listWrapper.className = 'space-y-3';
  container.innerHTML = '';
  container.appendChild(listWrapper);

  // Render batch berikutnya
  const renderNextBatch = () => {
    if (loading) return;
    loading = true;
    const nextItems = data.slice(currentIndex, currentIndex + batchSize);
    nextItems.forEach((item, idx) => {
      const itemHTML = renderItem(item, currentIndex + idx);
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = itemHTML.trim();
      // Ambil elemen pertama (karena itemHTML adalah satu root)
      listWrapper.appendChild(tempDiv.firstChild);
    });
    currentIndex += nextItems.length;
    loading = false;
  };

  // Initial render
  renderNextBatch();

  // Scroll event handler
  const onScroll = () => {
    // Jika sudah habis, tidak perlu load lagi
    if (currentIndex >= data.length) return;
    // Jika scroll mendekati bawah (100px dari bawah)
    if (container.scrollTop + container.clientHeight >= listWrapper.clientHeight - 100) {
      renderNextBatch();
    }
  };

  // Set overflow agar bisa discroll
  container.style.overflowY = 'auto';
  container.style.maxHeight = '500px'; // Atur sesuai kebutuhan UI

  container.removeEventListener('scroll', onScroll); // Pastikan tidak double
  container.addEventListener('scroll', onScroll);
}; 