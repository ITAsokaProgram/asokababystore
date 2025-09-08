// Minimal lazy-load handler for product grid (ready to wire later)
// Exports:
// - initLazyLoad(options): sets up an IntersectionObserver on a sentinel inside the grid
// - createSentinel(): returns a DOM element to use as the sentinel

/**
 * Simple debounce helper
 */
function debounce(fn, wait = 200) {
  let t;
  return function (...args) {
    clearTimeout(t);
    t = setTimeout(() => fn.apply(this, args), wait);
  };
}

/**
 * Create a sentinel element to insert at the end of the grid
 */
export function createSentinel() {
  const s = document.createElement('div');
  s.className = 'products-sentinel w-full h-6';
  s.setAttribute('aria-hidden', 'true');
  return s;
}

/**
 * Initialize lazy-loading for a product grid.
 * options:
 *  - gridSelector: selector or Element for product grid (default '#productsGrid')
 *  - onLoadMore: callback invoked when sentinel intersects (should load more products)
 *  - root: IntersectionObserver root (default: null)
 *  - rootMargin: observer rootMargin (default: '200px')
 *  - threshold: observer threshold (default: 0.1)
 *  - debounceMs: debounce calls to onLoadMore (default: 200)
 *
 * Returns a controller with { observer, sentinel, disconnect }
 */
export function initLazyLoad({
  gridSelector = '#productsGrid',
  onLoadMore = () => {},
  root = null,
  rootMargin = '200px',
  threshold = 0.1,
  debounceMs = 200
} = {}) {
  const grid = typeof gridSelector === 'string' ? document.querySelector(gridSelector) : gridSelector;
  if (!grid) {
    console.warn('lazyLoadHandler: grid element not found for', gridSelector);
    return null;
  }

  // Create or find sentinel
  let sentinel = grid.querySelector('.products-sentinel');
  if (!sentinel) {
    sentinel = createSentinel();
    grid.appendChild(sentinel);
  }

  const debouncedLoad = debounce(() => onLoadMore && onLoadMore(), debounceMs);

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        debouncedLoad();
      }
    });
  }, { root, rootMargin, threshold });

  observer.observe(sentinel);

  return {
    observer,
    sentinel,
    disconnect() {
      try { observer.unobserve(sentinel); } catch (e) {}
      try { observer.disconnect(); } catch (e) {}
      if (sentinel && sentinel.parentNode) sentinel.parentNode.removeChild(sentinel);
    }
  };
}

export default { initLazyLoad, createSentinel };
