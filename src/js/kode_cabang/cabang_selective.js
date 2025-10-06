import getCookie from "./../index/utils/cookies.js";

export const cabangSelective = async (selectId) => {
  try {
    const token = getCookie("token");
    const originalSelect = document.getElementById(selectId);
    const response = await fetch("/src/api/cabang/get_kode", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    const data = await response.json();

    if (response.status === 200) {
      // Ganti select dengan custom dropdown
      createMultiSelectDropdown(originalSelect, data.data);
    } else if (response.status === 204) {
      console.log(data.message);
    }
  } catch (error) {
    console.log(error);
  }
};

const createMultiSelectDropdown = (originalSelect, cabangData) => {
  // Buat container untuk dropdown
  const container = document.createElement("div");
  container.className = "multi-select-container";
  container.style.cssText = `
    position: relative;
    display: inline-block;
    width: 100%;
    min-width: 200px;
  `;

  // Buat button untuk menampilkan dropdown
  const dropdownButton = document.createElement("button");
  dropdownButton.type = "button";
  dropdownButton.className = "multi-select-button";
  dropdownButton.style.cssText = `
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    background-color: white;
    text-align: left;
    cursor: pointer;
    border-radius: 4px;
    position: relative;
    padding-right: 30px;
  `;
  dropdownButton.innerHTML = `
    <span class="selected-text">Pilih Cabang</span>
    <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">▼</span>
  `;

  // Buat dropdown list
  const dropdownList = document.createElement("div");
  dropdownList.className = "multi-select-list";
  dropdownList.style.cssText = `
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background-color: white;
    border: 1px solid #ddd;
    border-top: none;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    border-radius: 0 0 4px 4px;
  `;

  // Array untuk menyimpan pilihan yang dipilih
  let selectedValues = [];

  // Fungsi untuk update tampilan button
  const updateButtonText = () => {
    const selectedText = dropdownButton.querySelector('.selected-text');
    if (selectedValues.length === 0) {
      selectedText.textContent = "Pilih Cabang";
    } else if (selectedValues.includes('all')) {
      selectedText.textContent = "Semua Cabang";
    } else if (selectedValues.length === 1) {
      const selectedItem = cabangData.find(item => item.store === selectedValues[0]);
      selectedText.textContent = selectedItem ? selectedItem.nama_cabang : selectedValues[0];
    } else {
      selectedText.textContent = `${selectedValues.length} Cabang Dipilih`;
    }
  };

  // Fungsi untuk membuat checkbox item
  const createCheckboxItem = (value, text, isSelectAll = false) => {
    const item = document.createElement("div");
    item.className = "multi-select-item";
    item.style.cssText = `
      padding: 8px 12px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      border-bottom: 1px solid #f0f0f0;
    `;

    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.value = value;
    checkbox.style.cssText = `
      margin: 0;
      cursor: pointer;
    `;

    const label = document.createElement("label");
    label.textContent = text;
    label.style.cssText = `
      cursor: pointer;
      flex: 1;
      margin: 0;
    `;

    // Event listener untuk checkbox
    checkbox.addEventListener("change", (e) => {
      if (isSelectAll) {
        // Jika "Semua Cabang" dipilih
        if (e.target.checked) {
          selectedValues = ['all'];
          // Check semua checkbox lainnya
          const allCheckboxes = dropdownList.querySelectorAll('input[type="checkbox"]');
          allCheckboxes.forEach(cb => cb.checked = true);
        } else {
          selectedValues = [];
          // Uncheck semua checkbox lainnya
          const allCheckboxes = dropdownList.querySelectorAll('input[type="checkbox"]');
          allCheckboxes.forEach(cb => cb.checked = false);
        }
      } else {
        // Jika checkbox individual dipilih
        const selectAllCheckbox = dropdownList.querySelector('input[type="checkbox"]');
        
        if (e.target.checked) {
          if (!selectedValues.includes(value)) {
            selectedValues.push(value);
          }
          // Hapus 'all' jika ada saat memilih individual
          if (selectedValues.includes('all')) {
            selectedValues = selectedValues.filter(v => v !== 'all');
            selectAllCheckbox.checked = false;
          }
        } else {
          selectedValues = selectedValues.filter(v => v !== value);
          selectAllCheckbox.checked = false;
        }

        // Jika semua cabang individual dipilih, check "Semua Cabang"
        const individualCheckboxes = dropdownList.querySelectorAll('input[type="checkbox"]:not([value="all"])');
        const checkedIndividual = Array.from(individualCheckboxes).filter(cb => cb.checked);
        
        if (checkedIndividual.length === individualCheckboxes.length && individualCheckboxes.length > 0) {
          selectAllCheckbox.checked = true;
          selectedValues = ['all'];
        }
      }

      updateButtonText();
      updateOriginalSelect();
    });

    // Event listener untuk item (klik pada area item)
    item.addEventListener("click", (e) => {
      if (e.target !== checkbox) {
        checkbox.checked = !checkbox.checked;
        checkbox.dispatchEvent(new Event('change'));
      }
    });

    // Hover effect
    item.addEventListener("mouseenter", () => {
      item.style.backgroundColor = "#f8f9fa";
    });

    item.addEventListener("mouseleave", () => {
      item.style.backgroundColor = "white";
    });

    item.appendChild(checkbox);
    item.appendChild(label);

    return item;
  };

  // Buat opsi "Semua Cabang"
  let allValue;
  if (cabangData === "Pusat") {
    allValue = "Pusat";
  } else {
    allValue = cabangData.map((item) => item.store).join(",");
  }
  
  const selectAllItem = createCheckboxItem("all", "Semua Cabang", true);
  dropdownList.appendChild(selectAllItem);

  // Buat opsi untuk setiap cabang
  if (cabangData !== "Pusat") {
    cabangData.forEach((item) => {
      const checkboxItem = createCheckboxItem(item.store, item.nama_cabang);
      dropdownList.appendChild(checkboxItem);
    });
  }

  // Event listener untuk button dropdown
  dropdownButton.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    const isVisible = dropdownList.style.display === "block";
    dropdownList.style.display = isVisible ? "none" : "block";
    
    // Ubah arrow
    const arrow = dropdownButton.querySelector('span:last-child');
    arrow.textContent = isVisible ? "▼" : "▲";
  });

  // Tutup dropdown saat klik di luar
  document.addEventListener("click", (e) => {
    if (!container.contains(e.target)) {
      dropdownList.style.display = "none";
      const arrow = dropdownButton.querySelector('span:last-child');
      arrow.textContent = "▼";
    }
  });

  // Fungsi untuk update original select (untuk kompatibilitas)
  const updateOriginalSelect = () => {
    // Clear existing options dan hidden inputs
    originalSelect.innerHTML = "";
    const existingHiddenInputs = container.querySelectorAll('input[type="hidden"][name^="cabang"]');
    existingHiddenInputs.forEach(input => input.remove());
    
    // Nonaktifkan original select untuk mencegah submit ganda
    originalSelect.disabled = true;
    
    if (selectedValues.includes('all')) {
      // Jika memilih Semua Cabang, buat satu option dengan nilai gabungan
      const option = document.createElement("option");
      option.value = allValue;
      option.selected = true;
      option.textContent = "Semua Cabang";
      originalSelect.appendChild(option);
      
      // Buat hidden input untuk semua nilai
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'cabang';
      hiddenInput.value = allValue;
      container.appendChild(hiddenInput);
    } else {
      // Gabungkan semua nilai yang dipilih menjadi satu string dipisahkan koma
      const combinedValue = selectedValues.join(',');
      
      // Buat satu hidden input untuk semua nilai
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'cabang';
      hiddenInput.value = combinedValue;
      container.appendChild(hiddenInput);
      
      // Tambahkan ke dropdown untuk tampilan (opsional)
      selectedValues.forEach(value => {
        const option = document.createElement("option");
        option.value = value;
        option.selected = true;
        const cabang = cabangData.find(item => item.store === value);
        option.textContent = cabang ? cabang.nama_cabang : value;
        originalSelect.appendChild(option);
      });
    }

    // Update multiple attribute
    originalSelect.multiple = !selectedValues.includes('all');
    
    // Trigger change event pada original select
    originalSelect.dispatchEvent(new Event('change'));
  };

  // Sembunyikan original select
  originalSelect.style.display = "none";

  // Append elements
  container.appendChild(dropdownButton);
  container.appendChild(dropdownList);

  // Replace original select dengan container
  originalSelect.parentNode.insertBefore(container, originalSelect);

  // Fungsi untuk mendapatkan nilai yang dipilih (untuk digunakan dari luar)
  container.getSelectedValues = () => {
    if (selectedValues.includes('all')) {
      return cabangData === "Pusat" ? ["Pusat"] : cabangData.map(item => item.store);
    }
    return selectedValues;
  };

  // Fungsi untuk set nilai dari luar
  container.setSelectedValues = (values) => {
    selectedValues = values;
    const checkboxes = dropdownList.querySelectorAll('input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
      checkbox.checked = values.includes(checkbox.value);
    });
    
    updateButtonText();
    updateOriginalSelect();
  };
};

export default { cabangSelective };