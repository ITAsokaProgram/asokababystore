import { API_ENDPOINTS } from "../config/constants.js";
class BranchService {
  constructor() {
    this.storeCodes = null;
    this.allBranchCodes = null;
    this.isLoaded = false;
    this.loadPromise = null;
  }
  _getAuthToken() {
    try {
      const value = document.cookie.match('(^|;)\\s*admin_token\\s*=\\s*([^;]+)');
      return value ? value[2] : null;
    } catch (error) {
      console.warn("⚠️ Cannot access cookies:", error.message);
      return null;
    }
  }

  async _fetchStoreCodes() {
    try {
      const token = this._getAuthToken();
      const headers = {
        "Content-Type": "application/json",
      };
      if (token) {
        headers.Authorization = `Bearer ${token}`;
      } else {
        console.warn("⚠️ No authorization token found, proceeding without auth");
      }
      const response = await fetch(API_ENDPOINTS.BRANCH_CODES, {
        method: "GET",
        headers: headers,
      });
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      if (!data || !data.data || !Array.isArray(data.data)) {
        throw new Error("Invalid API response structure");
      }
      const storeCodesMapping = {};
      data.data.forEach((item) => {
        if (item.nama_cabang && item.store) {
          storeCodesMapping[item.nama_cabang] = item.store;
        }
        else if (item.nama_cabang && item.store) {
          storeCodesMapping[item.nama_cabang] = item.store;
        }
        else if (item.name && item.code) {
          storeCodesMapping[item.name] = item.code;
        }
      });
      if (Object.keys(storeCodesMapping).length === 0) {
        throw new Error("No valid store codes found in API response");
      }
      return storeCodesMapping;
    } catch (error) {
      console.error("❌ Failed to fetch store codes from API:", error);
      throw error;
    }
  }

  async loadStoreCodes(forceRefresh = false) {
    if (this.isLoaded && !forceRefresh && this.storeCodes) {
      return this.storeCodes;
    }
    if (this.loadPromise && !forceRefresh) {
      return await this.loadPromise;
    }
    this.loadPromise = this._performLoad();
    return await this.loadPromise;
  }

  async _performLoad() {
    try {
      this.storeCodes = await this._fetchStoreCodes();
      this.allBranchCodes = Object.values(this.storeCodes);
      this.isLoaded = true;
      return this.storeCodes;
    } catch (error) {
      console.error("❌ Failed to load store codes from API:", error.message);
      this.storeCodes = null;
      this.allBranchCodes = null;
      this.isLoaded = false;
      throw new Error(`Cannot load branch data: ${error.message}`);
    } finally {
      this.loadPromise = null;
    }
  }

  async getStoreCodes(forceRefresh = false) {
    return await this.loadStoreCodes(forceRefresh);
  }

  async getAllBranchCodes(forceRefresh = false) {
    await this.loadStoreCodes(forceRefresh);
    return this.allBranchCodes || [];
  }

  async getStoreCodeForBranch(branchName, forceRefresh = false) {
    const storeCodes = await this.loadStoreCodes(forceRefresh);
    return storeCodes[branchName] || null;
  }

  async getBranchNames(forceRefresh = false) {
    const storeCodes = await this.loadStoreCodes(forceRefresh);
    return Object.keys(storeCodes);
  }

  async branchExists(branchName, forceRefresh = false) {
    const storeCodes = await this.loadStoreCodes(forceRefresh);
    return storeCodes.hasOwnProperty(branchName);
  }

  async refreshFromAPI() {
    return await this.loadStoreCodes(true);
  }

  isLoading() {
    return this.loadPromise !== null;
  }

  isDataLoaded() {
    return this.isLoaded;
  }

  getDataSourceInfo() {
    return {
      isLoaded: this.isLoaded,
      isLoading: this.isLoading(),
      totalBranches: this.storeCodes ? Object.keys(this.storeCodes).length : 0,
      dataSource: this.isLoaded ? "api" : "none",
    };
  }

  clearCache() {
    this.storeCodes = null;
    this.allBranchCodes = null;
    this.isLoaded = false;
    this.loadPromise = null;
  }

  async getSelectOptions(includeAll = true, forceRefresh = false) {
    const storeCodes = await this.loadStoreCodes(forceRefresh);
    const options = [];
    if (includeAll) {
      options.push({
        value: "SEMUA CABANG",
        text: "SEMUA CABANG",
        isAll: true,
      });
    }
    Object.keys(storeCodes).forEach((branchName) => {
      options.push({
        value: branchName,
        text: branchName,
        storeCode: storeCodes[branchName],
        isAll: false,
      });
    });
    return options;
  }
}
const branchService = new BranchService();
export default branchService;
