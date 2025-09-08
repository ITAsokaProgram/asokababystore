// monitoring-log.js
// Modul utama untuk dashboard monitoring log
// Struktur: src/js/monitoring/monitoring-log.js

class AutoLogMonitor {
    constructor() {
        // State utama
        this.logs = [];
        this.filteredLogs = [];
        this.stats = { ERROR: 0, WARN: 0, INFO: 0, DEBUG: 0, total: 0 };
        this.autoRefreshInterval = null;
        this.isAutoRefresh = true;
        this.currentFiles = [];
        this.lastLogCount = 0;
        this.folderPath = './logs';

        this.initializeElements();
        this.bindEvents();
        this.setCurrentDate();
        this.startAutoRefresh();
    }

    // Inisialisasi semua elemen DOM yang dipakai
    initializeElements() {
        this.elements = {
            logFolderPath: document.getElementById('logFolderPath'),
            loadFolderBtn: document.getElementById('loadFolderBtn'),
            dateFilter: document.getElementById('dateFilter'),
            logLevel: document.getElementById('logLevel'),
            searchInput: document.getElementById('searchInput'),
            toggleAutoRefresh: document.getElementById('toggleAutoRefresh'),
            manualRefresh: document.getElementById('manualRefresh'),
            clearBtn: document.getElementById('clearBtn'),
            exportBtn: document.getElementById('exportBtn'),
            refreshInterval: document.getElementById('refreshInterval'),
            logContainer: document.getElementById('logContainer'),
            emptyState: document.getElementById('emptyState'),
            errorCount: document.getElementById('errorCount'),
            warnCount: document.getElementById('warnCount'),
            infoCount: document.getElementById('infoCount'),
            totalCount: document.getElementById('totalCount'),
            logCount: document.getElementById('logCount'),
            currentFile: document.getElementById('currentFile'),
            lastUpdate: document.getElementById('lastUpdate'),
            autoRefreshStatus: document.getElementById('autoRefreshStatus'),
            fileList: document.getElementById('fileList'),
            scrollToBottom: document.getElementById('scrollToBottom')
        };
    }

    // Binding event ke tombol dan input
    bindEvents() {
        this.elements.loadFolderBtn.addEventListener('click', () => this.loadLogFolder());
        this.elements.dateFilter.addEventListener('change', () => this.loadLogsForDate());
        this.elements.logLevel.addEventListener('change', () => this.filterLogs());
        this.elements.searchInput.addEventListener('input', () => this.filterLogs());
        this.elements.toggleAutoRefresh.addEventListener('click', () => this.toggleAutoRefresh());
        this.elements.manualRefresh.addEventListener('click', () => this.manualRefresh());
        this.elements.clearBtn.addEventListener('click', () => this.clearLogs());
        this.elements.exportBtn.addEventListener('click', () => this.exportLogs());
        this.elements.refreshInterval.addEventListener('change', () => this.updateRefreshInterval());
        this.elements.scrollToBottom.addEventListener('click', () => this.scrollToBottom());
    }

    // Fungsi untuk load folder log (trigger dari tombol)
    async loadLogFolder() {
        // Ambil path folder dari input (jika ada, default './logs')
        this.folderPath = this.elements.logFolderPath.value || './logs';
        try {
            // Panggil API untuk scan file log
            await this.scanLogFiles();
            // Setelah dapat file, langsung load log sesuai tanggal filter
            await this.loadLogsForDate();
            this.showNotification('Folder berhasil dimuat', 'success');
        } catch (error) {
            this.showNotification('Error memuat folder: ' + error.message, 'error');
        }
    }
    // Set tanggal hari ini di filter
    setCurrentDate() {
        const today = new Date().toISOString().split('T')[0];
        this.elements.dateFilter.value = today;
    }

    // Ambil daftar file log dari API PHP
    async scanLogFiles() {
        try {
            // Panggil API untuk ambil list file log
            const response = await fetch('/src/api/monitoring/list_log_files.php');
            if (!response.ok) throw new Error('Gagal mengambil daftar file log');
            const data = await response.json();
            if (!data.status) throw new Error(data.message || 'Gagal mengambil daftar file log');
            // Map hasil ke format frontend
            this.currentFiles = data.files.map(f => ({
                name: f.name,
                date: f.mtime.split(' ')[0],
                size: f.size,
                path: `/logs/${f.name}`
            }));
            this.updateFileList();
        } catch (err) {
            this.currentFiles = [];
            this.updateFileList();
            this.showNotification('Gagal mengambil daftar file log: ' + err.message, 'error');
        }
    }

    // Update daftar file log di sidebar
    updateFileList() {
        const container = this.elements.fileList;
        container.innerHTML = '';
        if (this.currentFiles.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-gray-500 text-sm">Tidak ada file log ditemukan</div>';
            return;
        }
        this.currentFiles.forEach(file => {
            const fileElement = document.createElement('div');
            fileElement.className = 'flex items-center justify-between p-2 border rounded hover:bg-gray-50 cursor-pointer';
            fileElement.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-file-alt text-blue-500"></i>
                    <div>
                        <div class="text-sm font-medium">${file.name}</div>
                        <div class="text-xs text-gray-500">${this.formatFileSize(file.size)}</div>
                    </div>
                </div>
                <div class="text-xs text-gray-400">${file.date}</div>
            `;
            // Klik file log untuk load isinya
            fileElement.addEventListener('click', () => {
                this.loadLogByFilename(file.name);
            });
            container.appendChild(fileElement);
        });
    }

    // Ambil isi file log dari API PHP
    async loadLogByFilename(filename) {
        const apiUrl = `/src/api/monitoring/get_log_file.php?filename=${encodeURIComponent(filename)}`;
        try {
            const response = await fetch(apiUrl);
            if (!response.ok) throw new Error(await response.text());
            const logContent = await response.text();
            this.parseLogs(logContent);
            this.updateStats();
            this.filterLogs();
            this.elements.emptyState.style.display = 'none';
            this.elements.currentFile.innerHTML = `<i class="fas fa-file mr-1"></i>${filename}`;
            this.updateLastUpdate();
        } catch (error) {
            this.logs = [];
            this.filteredLogs = [];
            this.updateStats();
            this.filterLogs();
            this.elements.emptyState.style.display = 'block';
            this.elements.currentFile.innerHTML = `<i class='fas fa-file mr-1'></i>Tidak ada file aktif`;
            this.showNotification('Error membaca file log: ' + error.message, 'error');
        }
    }

    // Format ukuran file biar gampang dibaca
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // Load log berdasarkan tanggal filter
    async loadLogsForDate() {
        const selectedDate = this.elements.dateFilter.value;
        if (!selectedDate) return;

        // Sort: permission_access-... akan di depan
        const sortedFiles = [...this.currentFiles].sort((a, b) => {
            // permission_access- lebih prioritas
            if (a.name.startsWith('permission_access-') && !b.name.startsWith('permission_access-')) return -1;
            if (!a.name.startsWith('permission_access-') && b.name.startsWith('permission_access-')) return 1;
            return 0;
        });

        // Cari file log yang tanggalnya cocok
        const file = sortedFiles.find(f => f.date === selectedDate);
        if (file) {
            await this.loadLogByFilename(file.name);
        } else {
            this.logs = [];
            this.filteredLogs = [];
            this.updateStats();
            this.filterLogs();
            this.elements.emptyState.style.display = 'block';
            this.elements.currentFile.innerHTML = `<i class='fas fa-file mr-1'></i>Tidak ada file aktif`;
            this.showNotification('Tidak ada file log untuk tanggal tersebut', 'warning');
        }
    }

    // Parse isi log jadi array object
    parseLogs(text) {
        const lines = text.split('\n').filter(line => line.trim());
        const newLogs = lines.map((line, index) => {
            const logEntry = this.parseLogLine(line);
            return {
                id: Date.now() + index,
                raw: line,
                isNew: this.logs.length > 0 && index >= this.lastLogCount,
                ...logEntry
            };
        });
        // Deteksi log baru
        if (this.logs.length > 0 && newLogs.length > this.logs.length) {
            this.showNotification(`${newLogs.length - this.logs.length} log baru ditemukan`, 'info');
        }
        this.logs = newLogs;
        this.lastLogCount = this.logs.length;
    }

    // Parse satu baris log ke object (level, timestamp, message)
    parseLogLine(line) {
        const patterns = [
            /^\[(\w+)\]\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+-\s+(.+)$/,
            /^(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(\w+)\s+(.+)$/,
            /^(\w+):\s+(.+)$/
        ];
        for (let pattern of patterns) {
            const match = line.match(pattern);
            if (match) {
                if (match.length === 4) {
                    return {
                        level: match[1],
                        timestamp: match[2],
                        message: match[3]
                    };
                } else if (match.length === 3) {
                    return {
                        level: match[1],
                        timestamp: this.getJakartaTimestamp(),
                        message: match[2]
                    };
                }
            }
        }
        // Default jika format tidak cocok
        return {
            level: 'INFO',
            timestamp: this.getJakartaTimestamp(),
            message: line
        };
    }

    // Helper untuk ambil timestamp format yyyy-mm-dd HH:MM:SS zona Asia/Jakarta
    getJakartaTimestamp() {
        try {
            // Format ISO-like: yyyy-mm-dd HH:MM:SS, zona Asia/Jakarta
            // sv-SE menghasilkan "2025-08-07 15:04:05"
            return new Date().toLocaleString('sv-SE', { timeZone: 'Asia/Jakarta' });
        } catch (e) {
            // Fallback: pakai waktu lokal
            return new Date().toISOString().slice(0, 19).replace('T', ' ');
        }
    }

    // Update statistik log (jumlah error, info, dll)
    updateStats() {
        this.stats = { ERROR: 0, WARN: 0, INFO: 0, DEBUG: 0, total: this.logs.length };
        this.logs.forEach(log => {
            const level = log.level.toUpperCase();
            if (this.stats.hasOwnProperty(level)) {
                this.stats[level]++;
            }
        });
        this.elements.errorCount.textContent = this.stats.ERROR;
        this.elements.warnCount.textContent = this.stats.WARN;
        this.elements.infoCount.textContent = this.stats.INFO;
        this.elements.totalCount.textContent = this.stats.total;
    }

    // Filter log berdasarkan level dan pencarian
    filterLogs() {
        const levelFilter = this.elements.logLevel.value;
        const searchTerm = this.elements.searchInput.value.toLowerCase();
        this.filteredLogs = this.logs.filter(log => {
            const matchesLevel = !levelFilter || log.level.toUpperCase() === levelFilter;
            const matchesSearch = !searchTerm || 
                log.message.toLowerCase().includes(searchTerm) ||
                log.raw.toLowerCase().includes(searchTerm);
            return matchesLevel && matchesSearch;
        });
        this.renderLogs();
    }

    // Render log ke tampilan
    renderLogs() {
        const container = this.elements.logContainer;
        if (this.filteredLogs.length === 0 && this.logs.length > 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-search text-4xl mb-2"></i>
                    <p>Tidak ada log yang cocok dengan filter</p>
                </div>
            `;
            this.elements.logCount.textContent = '0 entries';
            return;
        }
        // Hanya update jika ada perubahan
        if (container.children.length !== this.filteredLogs.length + 1) {
            container.innerHTML = '';
            this.filteredLogs.forEach(log => {
                const logElement = this.createLogElement(log);
                container.appendChild(logElement);
            });
        }
        this.elements.logCount.textContent = `${this.filteredLogs.length} entries`;
    }

    // Buat elemen log satuan
    createLogElement(log) {
        const div = document.createElement('div');
        const baseClass = `log-entry p-4 rounded-lg border-l-4 ${this.getLogStyles(log.level)} bg-gray-50 hover:bg-gray-100`;
        div.className = log.isNew ? `${baseClass} new-log` : baseClass;
        const highlightedMessage = this.highlightSearchTerm(log.message);
        div.innerHTML = `
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="px-2 py-1 text-xs font-bold rounded ${this.getLevelBadge(log.level)}">
                            ${log.level.toUpperCase()}
                        </span>
                        <span class="text-sm text-gray-500 font-mono">
                            <i class="fas fa-clock mr-1"></i>${log.timestamp}
                        </span>
                        ${log.isNew ? '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded font-bold">NEW</span>' : ''}
                    </div>
                    <p class="text-gray-800 font-mono text-sm leading-relaxed">${highlightedMessage}</p>
                </div>
                <button onclick="this.parentElement.parentElement.classList.toggle('hidden')" 
                        class="text-gray-400 hover:text-gray-600 ml-4">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        return div;
    }

    // Style border log berdasarkan level
    getLogStyles(level) {
        const styles = {
            'ERROR': 'border-red-500',
            'WARN': 'border-yellow-500',
            'INFO': 'border-blue-500',
            'DEBUG': 'border-gray-500'
        };
        return styles[level.toUpperCase()] || 'border-gray-300';
    }

    // Badge warna level log
    getLevelBadge(level) {
        const badges = {
            'ERROR': 'bg-red-500 text-white',
            'WARN': 'bg-yellow-500 text-white',
            'INFO': 'bg-blue-500 text-white',
            'DEBUG': 'bg-gray-500 text-white'
        };
        return badges[level.toUpperCase()] || 'bg-gray-300 text-gray-700';
    }

    // Highlight hasil pencarian
    highlightSearchTerm(text) {
        const searchTerm = this.elements.searchInput.value;
        if (!searchTerm) return text;
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
    }

    // Mulai auto refresh polling log
    startAutoRefresh() {
        const interval = parseInt(this.elements.refreshInterval.value);
        this.stopAutoRefresh();
        if (this.isAutoRefresh) {
            this.autoRefreshInterval = setInterval(() => {
                this.loadLogsForDate();
            }, interval);
        }
    }

    // Stop auto refresh
    stopAutoRefresh() {
        if (this.autoRefreshInterval) {
            clearInterval(this.autoRefreshInterval);
            this.autoRefreshInterval = null;
        }
    }

    // Toggle auto refresh on/off
    toggleAutoRefresh() {
        this.isAutoRefresh = !this.isAutoRefresh;
        const btn = this.elements.toggleAutoRefresh;
        const status = this.elements.autoRefreshStatus;
        if (this.isAutoRefresh) {
            btn.innerHTML = '<i class="fas fa-pause"></i><span>Auto Refresh: ON</span>';
            btn.className = 'px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center space-x-2';
            status.innerHTML = '<div class="pulse-dot w-3 h-3 bg-green-400 rounded-full"></div><span class="text-sm">Auto-refresh: ON</span>';
            this.startAutoRefresh();
        } else {
            btn.innerHTML = '<i class="fas fa-play"></i><span>Auto Refresh: OFF</span>';
            btn.className = 'px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center space-x-2';
            status.innerHTML = '<div class="w-3 h-3 bg-red-400 rounded-full"></div><span class="text-sm">Auto-refresh: OFF</span>';
            this.stopAutoRefresh();
        }
    }

    // Update interval polling
    updateRefreshInterval() {
        if (this.isAutoRefresh) {
            this.startAutoRefresh();
        }
    }

    // Refresh manual
    manualRefresh() {
        this.loadLogsForDate();
        this.showNotification('Log berhasil di-refresh', 'success');
    }

    // Bersihkan tampilan log
    clearLogs() {
        this.logs = [];
        this.filteredLogs = [];
        this.elements.logContainer.innerHTML = '';
        this.elements.emptyState.style.display = 'block';
        this.updateStats();
        this.elements.logCount.textContent = '0 entries';
        this.elements.currentFile.innerHTML = '<i class="fas fa-file mr-1"></i>Tidak ada file aktif';
        this.showNotification('Log berhasil dibersihkan', 'info');
    }

    // Scroll ke bawah log
    scrollToBottom() {
        const container = this.elements.logContainer;
        container.scrollTop = container.scrollHeight;
    }

    // Export log ke CSV
    exportLogs() {
        if (this.filteredLogs.length === 0) {
            this.showNotification('Tidak ada log untuk diekspor', 'warning');
            return;
        }
        const csv = this.filteredLogs.map(log => 
            `"${log.level}","${log.timestamp}","${log.message.replace(/"/g, '""')}"`
        ).join('\n');
        const selectedDate = this.elements.dateFilter.value || 'unknown';
        const header = 'Level,Timestamp,Message\n';
        const blob = new Blob([header + csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `logs_${selectedDate}.csv`;
        a.click();
        URL.revokeObjectURL(url);
        this.showNotification('Log berhasil diekspor', 'success');
    }

    // Update waktu terakhir refresh (pakai zona waktu WIB/Asia/Jakarta)
    updateLastUpdate() {
        const now = new Date();
        let timeStr;
        try {
            // Pakai timeZone Asia/Jakarta supaya selalu WIB
            timeStr = now.toLocaleTimeString('id-ID', { timeZone: 'Asia/Jakarta', hour12: false });
        } catch (e) {
            // Fallback kalau browser tidak support
            timeStr = now.toLocaleTimeString('id-ID');
        }
        this.elements.lastUpdate.textContent = timeStr + ' WIB';
    }

    // Notifikasi toast
    showNotification(message, type = 'info') {
        // Buat elemen notifikasi
        const notification = document.createElement('div');
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas ${type === 'success' ? 'fa-check' : type === 'error' ? 'fa-times' : type === 'warning' ? 'fa-exclamation' : 'fa-info'}"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(notification);
        // Animasi masuk
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        // Hapus setelah 3 detik
        setTimeout(() => {
            notification.style.transform = 'translateX(full)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Inisialisasi dashboard monitoring log saat halaman siap
export function initMonitoringLog() {
    const monitor = new AutoLogMonitor();
    // Shortcut keyboard
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'r':
                    e.preventDefault();
                    monitor.manualRefresh();
                    break;
                case 'k':
                    e.preventDefault();
                    monitor.clearLogs();
                    break;
                case 'e':
                    e.preventDefault();
                    monitor.exportLogs();
                    break;
            }
        }
    });
    // Auto-scroll ke bawah saat ada log baru
    const observer = new MutationObserver(() => {
        const container = monitor.elements.logContainer;
        const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 1;
        if (isScrolledToBottom) {
            monitor.scrollToBottom();
        }
    });
    observer.observe(monitor.elements.logContainer, { childList: true });
}
