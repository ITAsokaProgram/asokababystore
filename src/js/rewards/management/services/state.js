// services/state.js
// Single source of truth + pub/sub sederhana

class Store extends EventTarget {
  /** @type {Array<Object>} */ 
  transaksiData = [];
  cartItems = [];
  currentMember = null;
  currentFilter = "all";
  dateFilter = null;
  filteredData = null;
  searchKeyword = '';
  pagination = {
    currentPage: 1,
    limit: 10,
    total: 0,
    totalPages: 1
  };

  getState() {
    return {
      transaksiData: this.transaksiData,
      cartItems: this.cartItems,
      currentMember: this.currentMember,
      currentFilter: this.currentFilter,
      dateFilter: this.dateFilter,
      filteredData: this.filteredData,
      searchKeyword: this.searchKeyword,
      pagination: this.pagination
    };
  }

  set(partial) {
    let changed = false;
    Object.entries(partial).forEach(([k, v]) => {
      if (this[k] !== v) {
        this[k] = v;
        changed = true;
      }
    });
    if (changed) this.dispatchEvent(new CustomEvent("state:change", { detail: this.getState() }));
  }

  update(fn) {
    const draft = this.getState();
    const next = fn({ ...draft });
    this.set(next);
  }
}

export const store = new Store();
