import { getCookie } from "../index/utils/cookies.js";
import { renderTableRewards } from "./handlerTable.js";
// Form validation utilities
const validateRequiredFields = (formData, requiredFields) => {
  const missingFields = [];

  requiredFields.forEach((field) => {
    const element = document.getElementById(field);
    if (!element?.value?.trim()) {
      missingFields.push(
        element?.previousElementSibling?.textContent?.replace(" *", "") || field
      );
    }
  });

  return missingFields;
};

const validateNumericValues = (poin, stok) => {
  return poin > 0 && stok > 0;
};

const showValidationError = (title, message) => {
  return Swal.fire({
    title,
    text: message,
    icon: "warning",
    confirmButtonColor: "#ec4899",
  });
};

const showSuccessMessage = (title, message) => {
  return Swal.fire({
    title,
    text: message,
    icon: "success",
    confirmButtonColor: "#ec4899",
  });
};

// Image Handler Configuration
const IMAGE_CONFIG = {
  maxSize: 5 * 1024 * 1024, // 5MB
  allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
  optimization: {
    maxWidth: 800,
    maxHeight: 600,
    quality: 0.8,
    format: 'jpeg'
  },
  saveEndpoint: '/src/api/rewards/insert_give'
};

// Image Validation Utilities
const ImageValidator = {
  validateSize(file) {
    if (file.size > IMAGE_CONFIG.maxSize) {
      throw new Error(`File terlalu besar. Maksimal ${IMAGE_CONFIG.maxSize / (1024 * 1024)}MB`);
    }
  },

  validateType(file) {
    if (!IMAGE_CONFIG.allowedTypes.includes(file.type)) {
      throw new Error('Format file tidak didukung. Gunakan JPG, PNG, atau GIF');
    }
  },

  validate(file) {
    this.validateSize(file);
    this.validateType(file);
  }
};

// Image Optimization Utilities
const ImageOptimizer = {
  calculateDimensions(originalWidth, originalHeight, maxWidth, maxHeight) {
    let { width, height } = { width: originalWidth, height: originalHeight };
    
    const aspectRatio = width / height;
    
    if (width > maxWidth) {
      width = maxWidth;
      height = width / aspectRatio;
    }
    
    if (height > maxHeight) {
      height = maxHeight;
      width = height * aspectRatio;
    }
    
    return { width: Math.round(width), height: Math.round(height) };
  },

  async optimize(file, options = IMAGE_CONFIG.optimization) {
    return new Promise((resolve, reject) => {
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      const img = new Image();

      img.onload = () => {
        try {
          const { width, height } = this.calculateDimensions(
            img.width, 
            img.height, 
            options.maxWidth, 
            options.maxHeight
          );

          canvas.width = width;
          canvas.height = height;
          ctx.drawImage(img, 0, 0, width, height);

          canvas.toBlob(
            (blob) => {
              if (blob) {
                const optimizedFile = new File([blob], file.name, {
                  type: `image/${options.format}`,
                  lastModified: Date.now()
                });
                resolve(optimizedFile);
              } else {
                reject(new Error('Gagal mengoptimalkan gambar'));
              }
            },
            `image/${options.format}`,
            options.quality
          );
        } catch (error) {
          reject(error);
        }
      };

      img.onerror = () => reject(new Error('Gagal memuat gambar'));
      img.src = URL.createObjectURL(file);
    });
  }
};

// UI State Manager
const UIStateManager = {
  showLoading(button, loadingIndicator, message = 'Memproses...') {
    if (button) {
      button.disabled = true;
      button.classList.add('opacity-50');
    }
    if (loadingIndicator) {
      loadingIndicator.classList.remove('hidden');
      loadingIndicator.querySelector('span').textContent = message;
    }
  },

  hideLoading(button, loadingIndicator) {
    if (button) {
      button.disabled = false;
      button.classList.remove('opacity-50');
    }
    if (loadingIndicator) {
      loadingIndicator.classList.add('hidden');
    }
  },

  showPreview(previewElement, uploadElement, imageSrc) {
    if (previewElement) {
      const img = previewElement.querySelector('img');
      if (img && imageSrc) {
        img.src = imageSrc;
      }
      previewElement.classList.remove('hidden');
    }
    if (uploadElement) {
      uploadElement.classList.add('hidden');
    }
  },

  hidePreview(previewElement, uploadElement) {
    if (previewElement) {
      previewElement.classList.add('hidden');
    }
    if (uploadElement) {
      uploadElement.classList.remove('hidden');
    }
  },

  updateButtonState(button, isLoading, loadingText = 'Memproses...', normalText = 'Submit') {
    if (!button) return;
    
    if (isLoading) {
      button.disabled = true;
      button.innerHTML = `
        <i class="fas fa-spinner fa-spin mr-2"></i>
        ${loadingText}
      `;
    } else {
      button.disabled = false;
      button.innerHTML = normalText;
    }
  }
};


// Lazy Loading Manager
const LazyLoadManager = {
  init() {
    const images = document.querySelectorAll('img[data-src]');
    if (images.length === 0) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove('lazy');
            observer.unobserve(img);
          }
        });
      },
      { rootMargin: '50px' }
    );

    images.forEach((img) => observer.observe(img));
  }
};

// Main Image Handler Class
class ImageHandler {
  constructor(imageInputId, previewId, uploadContentId) {
    this.imageInput = document.getElementById(imageInputId);
    this.preview = document.getElementById(previewId);
    this.uploadContent = document.getElementById(uploadContentId);
    this.loadingIndicator = document.getElementById('loadingIndicator');
    this.optimizedFile = null;
    
    this.init();
  }

  init() {
    if (this.imageInput) {
      this.imageInput.addEventListener('change', this.handleFileSelect.bind(this));
    }
    
    // Add click handler for upload content button
    if (this.uploadContent) {
      this.uploadContent.addEventListener('click', (e) => {
        e.preventDefault();
        this.imageInput.click();
      });
    }
    LazyLoadManager.init();
  }

  async handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    try {
      // Validate file
      ImageValidator.validate(file);
      
      // Show loading state
      UIStateManager.showLoading(this.uploadContent, this.loadingIndicator, 'Mengoptimalkan gambar...');

      try {
        // Optimize image
        this.optimizedFile = await ImageOptimizer.optimize(file);
        
        // Show preview
        await this.showPreview(this.optimizedFile);
        
      } finally {
        // Always hide loading state
        UIStateManager.hideLoading(this.uploadContent, this.loadingIndicator);
      }
      
    } catch (error) {
      console.error('Image processing error:', error);
      this.showError(error.message || 'Terjadi kesalahan saat memproses gambar');
      this.resetUploadState();
    }
  }

  async showPreview(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      
      reader.onload = (e) => {
        // Update the image source directly
        const previewImg = this.preview.querySelector('img');
        if (previewImg) {
          previewImg.src = e.target.result;
        }
        
        // Update UI state
        UIStateManager.showPreview(this.preview, this.uploadContent, e.target.result);
        
        // Store optimized file for later use
        this.imageInput.optimizedFile = this.optimizedFile;
        resolve();
      };
      
      reader.onerror = () => {
        this.showError('Gagal membaca file gambar');
        reject(new Error('Failed to read file'));
      };
      
      reader.readAsDataURL(file);
    });
  }

  showError(message) {
    Swal.fire({
      title: 'Error!',
      text: message,
      icon: 'error',
      confirmButtonColor: '#f97316'
    });
  }

  resetUploadState() {
    UIStateManager.hidePreview(this.preview, this.uploadContent);
    UIStateManager.hideLoading(this.uploadContent, this.loadingIndicator);
    this.imageInput.value = '';
    this.optimizedFile = null;
    delete this.imageInput.optimizedFile;
    
    // Reset any preview image
    const previewImg = this.preview?.querySelector('img');
    if (previewImg) {
      previewImg.src = '';
    }
  }

  getOptimizedFile() {
    return this.optimizedFile;
  }
}

// Enhanced Form Handler with Image Upload
class FormHandler {
  constructor(formId, submitButtonId) {
    this.form = document.getElementById(formId);
    this.submitButton = document.getElementById(submitButtonId);
    this.originalButtonText = this.submitButton?.innerHTML;
    
    this.init();
  }

  init() {
    if (!this.form || !this.submitButton) {
      console.error('Required form elements not found');
      return;
    }

    this.form.addEventListener('submit', this.handleSubmit.bind(this));
  }

  async handleSubmit(event) {
    event.preventDefault();
    
    try {
      UIStateManager.updateButtonState(this.submitButton, true, 'Menyimpan...');
      
      const formData = new FormData(this.form);
      const fileInput = document.getElementById('gambar_hadiah');
      
      // Use optimized image if available
      if (fileInput?.optimizedFile) {
        // Create a new Blob from the optimized file data
        const blob = new Blob([fileInput.optimizedFile], { type: fileInput.optimizedFile.type });
        // Create a new File from the Blob
        const optimizedFile = new File([blob], fileInput.files[0]?.name || 'optimized-image.jpg', {
          type: fileInput.optimizedFile.type,
          lastModified: new Date().getTime()
        });
        formData.set('gambar_hadiah', optimizedFile);
      }

      // Submit form data
      const token = getCookie("token");
      const response = await fetch(IMAGE_CONFIG.saveEndpoint, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });

      if (response.status === 201) {
        await this.showSuccess();
      } else {
        this.showError("Gagal Menyimpan Hadiah");
      }
      
    } catch (error) {
      console.error('Form submission error:', error);
      this.showError('Terjadi kesalahan saat menyimpan hadiah');
    } finally {
      UIStateManager.updateButtonState(this.submitButton, false, '', this.originalButtonText);
    }
  }

  async showSuccess() {
    return Swal.fire({
      title: 'Berhasil!',
      text: 'Hadiah berhasil disimpan',
      icon: 'success',
      confirmButtonColor: '#f97316'
    }).then(() => {
      if (typeof closeModal === 'function') {
        closeModal('modalTambahHadiah', 'modalContent');
      }
      location.reload();
    });
  }

  showError(message) {
    Swal.fire({
      title: 'Error!',
      text: message,
      icon: 'error',
      confirmButtonColor: '#f97316'
    });
  }
}

// Factory function for creating image handler
const createImageHandler = (imageInputId, previewId, uploadContentId = 'uploadContent') => {
  return new ImageHandler(imageInputId, previewId, uploadContentId);
};

// Factory function for creating form handler
const createFormHandler = (formId, submitButtonId) => {
  return new FormHandler(formId, submitButtonId);
};

// Export functions for use in other modules
export {
  createImageHandler,
  createFormHandler,
  validateRequiredFields,
  validateNumericValues,
  showValidationError,
  showSuccessMessage,
};
