import { kodeCabang } from "../kode_cabang/kd.js";
import { cabangSelective } from "../kode_cabang/cabang_selective.js";
import { createImageHandler, createFormHandler } from "./handler.js";
import { renderTableRewards } from "./handlerTable.js";
import {
  fetchRewardById,
  updateReward,
  deleteReward,
  fetchCount,
} from "./fetch.js";
import CabangHandler from "./cabangHandler.js";
import FilterHandler from "./filterHandler.js";
import { getCookie } from "../index/utils/cookies.js";


let idReward = null;
let filterHandler = null;

async function fetchCurrentUser() {
    const token = getCookie("admin_token");
    if (!token) return null;

    try {
        const response = await fetch('/src/auth/decode_token.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        });
        const data = await response.json();
        if (data.status === 'success') {
            return data.data; 
        }
        return null;
    } catch (error) {
        console.error("Gagal mengambil data user:", error);
        return null;
    }
}

const init = async () => {
    const currentUser = await fetchCurrentUser();
    window.USER_ROLE = currentUser ? currentUser.role : null;

    filterHandler = new FilterHandler();
  try {
    
    const stats = await fetchCount();
    
    const totalHadiah = document.getElementById("totalHadiah");
    totalHadiah.textContent = stats.total;

    
    
    await cabangSelective("cabang");
    const cabang = new CabangHandler;
    cabang.selectCabang();

    
    const imageHandler = createImageHandler(
      "gambar_hadiah",
      "imagePreview",
      "uploadContent"
    );

    
    const formHandler = createFormHandler("formTambahHadiah", "submitBtn");
    
    
    if (filterHandler) {
        await filterHandler.applyFilters();
    } else {
        await renderTableRewards();
    }

    
    const removeBtn = document.getElementById("removeImageBtn");
    if (removeBtn) {
      removeBtn.addEventListener("click", () => {
        if (
          imageHandler &&
          typeof imageHandler.resetUploadState === "function"
        ) {
          imageHandler.resetUploadState();
        }
      });
    }

    
    document.addEventListener("click", async (e) => {
      const editButton = e.target.closest("button.edit-btn[data-id]");
      const deleteButton = e.target.closest("button.delete-btn[data-id]");
      const editFilterButton = e.target.closest("button.edit-filter[data-id]");
      const deleteFilterButton = e.target.closest("button.delete-filter[data-id]");

      if (editButton || editFilterButton) {
        try {
          const id = editButton?.dataset?.id || editFilterButton?.dataset?.id;
          idReward = id;

          
          Swal.fire({
            title: "Memuat Data",
            text: "Sedang mengambil data hadiah...",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });

          
          const response = await fetchRewardById(id);
          Swal.close();

          if (response && response.success && response.data) {
            
            const { value: formValues } = await Swal.fire({
              title: "Edit Hadiah",
              html: `<span class="text-sm text-gray-500 mb-2">Ubah nama hadiah berikut:</span>` +
                    `<input id="swal-input1" class="swal2-input" placeholder="Nama Hadiah" value="${
                    response.data.nama_hadiah || ""
                    }">`,
              focusConfirm: false,
              showCancelButton: true,
              confirmButtonText: "Simpan",
              cancelButtonText: "Batal",
              confirmButtonColor: "#ec4899",
              preConfirm: () => {
                const nama = document.getElementById("swal-input1").value.trim();
                if (!nama) {
                  Swal.showValidationMessage("Nama hadiah tidak boleh kosong");
                  return false;
                }
                return { nama_hadiah: nama };
              },
            });

            if (formValues) {
              try {
                Swal.fire({
                  title: "Menyimpan...",
                  text: "Mohon tunggu, data hadiah sedang diperbarui.",
                  allowOutsideClick: false,
                  didOpen: () => {
                    Swal.showLoading();
                  },
                });
                
                const fd = new FormData();
                fd.append("id", id);
                fd.append("nama_hadiah", formValues.nama_hadiah);


                const updateResponse = await updateReward(id, fd);
                if (updateResponse.success) {
                  await Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: updateResponse.message || "Data berhasil diperbarui",
                    confirmButtonColor: "#ec4899",
                    confirmButtonText: "Mengerti",
                  });
                  
                  if (filterHandler && (filterHandler.hasActiveFilters())) {
                    
                    await filterHandler.applyFilters();
                  } else {
                    
                    await renderTableRewards();
                  }
                } else {
                  throw new Error(
                    updateResponse.message || "Gagal memperbarui data"
                  );
                }
              } catch (error) {
                Swal.fire({
                  icon: "error",
                  title: "Gagal",
                  text:
                    error.message || "Terjadi kesalahan saat memperbarui data",
                  confirmButtonColor: "#ec4899",
                  confirmButtonText: "Mengerti",
                });
              }
            }
          } else {
            Swal.fire({
              icon: "error",
              title: "Gagal",
              text: "Gagal memuat data hadiah. Silakan coba lagi.",
              confirmButtonColor: "#ec4899",
              confirmButtonText: "Mengerti",
            });
          }
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Kesalahan",
            text: "Terjadi kesalahan saat memuat data hadiah.",
            confirmButtonColor: "#ec4899",
            confirmButtonText: "Mengerti",
          });
        }
      } else if (deleteButton || deleteFilterButton) {
        try {
          const id = deleteButton?.dataset?.id || deleteFilterButton?.dataset?.id;
          const result = await Swal.fire({
            title: "Hapus Hadiah",
            text: "Apakah Anda yakin ingin menghapus hadiah ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ec4899",
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            reverseButtons: true,
          });

          if (result.isConfirmed) {
            const deleteResult = await deleteReward(id);
            if (deleteResult.success) {
              await Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: deleteResult.message || "Data berhasil dihapus",
                confirmButtonColor: "#ec4899",
                confirmButtonText: "Mengerti",
              });
              
              if (filterHandler && (filterHandler.hasActiveFilters())) {
                
                await filterHandler.applyFilters();
              } else {
                
                await renderTableRewards();
              }
            } else {
              throw new Error(deleteResult.message || "Gagal menghapus data");
            }
          }
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Gagal",
            text: error.message || "Terjadi kesalahan saat menghapus data",
            confirmButtonColor: "#ec4899",
            confirmButtonText: "Mengerti",
          });
        }
      }
      
      const receiveStockBtn = e.target.closest("button.receive-stock-btn");
      const updatePointBtn = e.target.closest("button.update-point-btn");

      
      if (receiveStockBtn) {
        
        const { plu, kd_store, nama_hadiah } = receiveStockBtn.dataset;
        
        const { value: qty } = await Swal.fire({
            title: 'Terima Stok Hadiah',
            html: `Masukkan jumlah stok yang diterima untuk:<br><b>${nama_hadiah}</b> (PLU: ${plu})`, 
            input: 'number',
            inputPlaceholder: 'Contoh: 50',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value || parseInt(value) <= 0) {
                    return 'Jumlah harus lebih besar dari 0!';
                }
            }
        });

        if (qty) {
            handleApiCall(
                '/src/api/rewards/terima_hadiah.php', 
                
                { plu, kd_store, nama_hadiah, qty_rec: qty }, 
                'Menambahkan stok...'
            );
        }
    }

      
      if (updatePointBtn) {
        
        const { plu, kd_store, nama_hadiah } = updatePointBtn.dataset; 
        
        const { value: poin } = await Swal.fire({
            title: 'Update Poin Hadiah',
            html: `Masukkan nilai poin baru untuk:<br><b>${nama_hadiah}</b> (PLU: ${plu})`, 
            input: 'number',
            inputPlaceholder: 'Contoh: 150',
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value || parseInt(value) < 0) {
                    return 'Nilai poin tidak boleh kosong atau negatif!';
                }
            }
        });

        if (poin) {
            handleApiCall(
                '/src/api/rewards/update_poin_hadiah.php', 
                
                { plu, kd_store, nama_hadiah, new_poin: poin }, 
                'Memperbarui poin...'
            );
        }
    }

    });

    
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Terjadi kesalahan saat menginisialisasi manajemen hadiah.",
      confirmButtonColor: "#ec4899",
      confirmButtonText: "Mengerti",
    });
  }
};

async function handleApiCall(url, data, loadingMessage) {
    const token = getCookie("admin_token"); 
    const formData = new FormData();
    for (const key in data) {
        formData.append(key, data[key]);
    }

    Swal.fire({
        title: loadingMessage,
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false
    });

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message,
            });
            
            if (filterHandler && (filterHandler.hasActiveFilters())) {
                await filterHandler.applyFilters();
            } else {
                await renderTableRewards();
            }
        } else {
            throw new Error(result.message || 'Terjadi kesalahan pada server.');
        }

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: error.message,
        });
    }
}









document.addEventListener("DOMContentLoaded", init);
