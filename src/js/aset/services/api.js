export const api = {
  async insertDataAset(token, data) {
    try {
      const modal = document.getElementById('addAssetModal');
      const form = document.getElementById('assetForm');
      const imagePreview = document.getElementById('imagePreview');

      const response = await fetch("/src/api/aset/insert_aset.php", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
        },
        body: data,
      });

      const result = await response.json();

      if (result.status) {
        await Swal.fire({
          title: "Success!",
          text: result.message,
          icon: "success",
        });
        
        // Reset form dan tutup modal
        modal.classList.add("hidden");
        form.reset();
        imagePreview.classList.add("hidden");
        
        // Refresh table jika fungsi tersedia
        if (typeof window.renderAsetTable === 'function') {
          window.renderAsetTable();
        }
        
        return result;
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      await Swal.fire({
        title: "Error!",
        text: error.message,
        icon: "error",
      });
      throw error;
    }
  },
  async editDataAset(token, data) {
    try {
      const response = await fetch('/src/api/aset/edit_aset.php', {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` },
        body: data
      });
      const result = await response.json();
      if (!result.status) throw new Error(result.message || 'Failed to update');
      if (typeof window.renderAsetTable === 'function') window.renderAsetTable();
      return result;
    } catch (err) {
      throw err;
    }
  },
  async deleteDataAset(token, id) {
    try {
      const fd = new FormData();
      fd.append('idhistory_aset', id);
      const response = await fetch('/src/api/aset/delete_aset.php', {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` },
        body: fd
      });
      const result = await response.json();
      if (!result.status) throw new Error(result.message || 'Failed to delete');
      if (typeof window.renderAsetTable === 'function') window.renderAsetTable();
      return result;
    } catch (err) {
      throw err;
    }
  }
};
