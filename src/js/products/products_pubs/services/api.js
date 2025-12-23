export const api = {
  async getData() {
    try {
      const response = await fetchWithTimeout(
        "/src/api/products/get_product_pubs",
        {
          timeout: 30000, // 30 detik
        }
      );
      if (!response.ok) throw new Error("Network response was not ok");
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching product data:", error);
      return {
        success: false,
        error: error.message || "Failed to fetch product data",
      };
    }
  },

  async getDetailData(id) {
    try {
      const response = await fetchWithTimeout(
        `/src/api/products/get_product_detail_pubs?id=${id}`,
        {
          timeout: 30000,
        }
      );
      if (!response.ok) throw new Error("Network response was not ok");
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching product detail:", error);
      return {
        success: false,
        error: error.message || "Failed to fetch product detail",
      };
    }
  },
};

async function fetchWithTimeout(resource, options = {}) {
  const { timeout = 30000 } = options; // default 30 detik
  const controller = new AbortController();
  const id = setTimeout(() => controller.abort(), timeout);

  try {
    const response = await fetch(resource, {
      ...options,
      signal: controller.signal,
    });
    clearTimeout(id);
    return response;
  } catch (error) {
    clearTimeout(id);
    // On timeout (AbortError) show a friendly notification (SweetAlert if available)
    if (error && error.name === 'AbortError') {
      try {
        // prefer SweetAlert2 (Swal.fire)
        if (typeof window !== 'undefined') {
          const win = window;
          if (win.Swal && typeof win.Swal.fire === 'function') {
            win.Swal.fire({
              icon: 'warning',
              title: 'Request timeout',
              text: 'Permintaan ke server melebihi batas waktu. Silakan coba lagi.',
            });
          } else if (win.swal && typeof win.swal === 'function') {
            // fallback for older sweetalert
            try { win.swal('Request timeout', 'Permintaan ke server melebihi batas waktu. Silakan coba lagi.', 'warning'); } catch (e) {}
          } else {
            // ultimate fallback
            try { window.alert('Request timeout - Permintaan ke server melebihi batas waktu. Silakan coba lagi.'); } catch (e) {}
          }
        }
      } catch (notifyErr) {
        // swallow notification errors
        console.warn('Notification error', notifyErr);
      }
      throw new Error('Request timeout');
    }
    throw error;
  }
}
