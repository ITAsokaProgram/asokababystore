<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('whatsapp_broadcast');
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Broadcast WhatsApp (Template Only)</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
  <link rel="stylesheet" href="../../style/header.css">
  <link rel="stylesheet" href="../../style/sidebar.css">
  <link rel="stylesheet" href="../../style/default-font.css">
  <link rel="stylesheet" href="../../output2.css">
  <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50">

  <?php include '../../component/navigation_report.php' ?>
  <?php include '../../component/sidebar_report.php' ?>

  <main class="flex-1 p-4 md:p-6 ml-64 transition-all duration-300">
    <div class="max-w-4xl mx-auto">

      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2"> <i class="fas fa-bullhorn text-blue-500 mr-2"></i> Broadcast
          Template</h1>
        <p class="text-gray-500 text-sm">Kirim pesan massal menggunakan <b>Template (Official)</b> via input manual CSV.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <form id="broadcastForm" onsubmit="handleBroadcast(event)">

            <input type="hidden" name="target_type" value="manual">
            <input type="hidden" name="message_type" value="template">

            <div class="mb-6">

              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-list-ol mr-1"></i> Daftar Penerima & Variabel
                </label>
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 mb-2">
                  <p class="text-xs text-blue-700">
                    <b>Format:</b> Nomor, Var1, Var2<br>
                    (Pisahkan dengan koma, satu data per baris)
                  </p>
                </div>
                <textarea name="manual_numbers" rows="6"
                  class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-300 focus:border-blue-500 text-sm font-mono"
                  placeholder="Contoh:
08123456789, VOUCHER_123, 01/12/2026
08129876543, VOUCHER_321, 04/12/2026"></textarea>
              </div>

              <hr class="border-gray-200 mb-6">

              <div class="space-y-4">
                <div class="flex items-center gap-2 mb-2">
                  <i class="fas fa-layer-group text-green-600"></i>
                  <h3 class="text-sm font-semibold text-gray-800">Konfigurasi Template</h3>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Template</label>
                    <input type="text" name="template_name"
                      class="w-full p-2 border border-gray-300 rounded-lg text-sm font-mono focus:border-green-500 focus:ring-1 focus:ring-green-500"
                      placeholder="contoh: promo_prenagen">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Bahasa</label>
                    <select name="template_lang" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                      <option value="id" selected>Indonesia (id)</option>
                      <option value="en_US">English (en_US)</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Header Gambar (Wajib)</label>
                  <input type="file" name="header_media"
                    class="block w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 border border-gray-300 rounded-lg cursor-pointer">
                </div>

                <div class="mt-2 text-[10px] text-gray-500 bg-gray-50 p-2 rounded border border-gray-200">
                  <p class="font-semibold"><i class="fas fa-info-circle"></i> Info Variabel Body:</p>
                  <p>Variabel body ({{1}}, {{2}}, dst) akan diambil secara otomatis dari input CSV di atas (teks setelah
                    koma pertama).</p>
                </div>

              </div>
            </div>

            <button type="submit" id="btnSubmit"
              class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
              <i class="fas fa-paper-plane"></i> Kirim Broadcast
            </button>

          </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 h-fit sticky top-6">
          <h3 class="font-semibold text-gray-800 mb-4">Status Pengiriman</h3>
          <div id="progressContainer" class="hidden">
            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
              <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                style="width: 0%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mb-4">
              <span id="progressText">0%</span>
              <span id="countText">0/0</span>
            </div>
            <div id="logContainer"
              class="bg-gray-50 rounded-lg p-3 h-64 overflow-y-auto text-xs font-mono border border-gray-100 space-y-1">
            </div>
          </div>
          <div id="defaultStatus" class="text-center text-gray-400 py-8">
            <i class="fas fa-satellite-dish text-4xl mb-2 opacity-30"></i>
            <p class="text-sm">Menunggu broadcast...</p>
          </div>
        </div>

      </div>
    </div>
  </main>

  <script src="/src/js/middleware_auth.js"></script>

  <script>
    function getToken() {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; admin_token=`);
      if (parts.length === 2) return parts.pop().split(";").shift();
      return null;
    }
    const wa_token = getToken();

    function addLog(message, type = 'info') {
      const container = document.getElementById('logContainer');
      const div = document.createElement('div');
      div.className = `flex items-center gap-2 ${type === 'success' ? 'text-green-600' : type === 'error' ? 'text-red-600' : 'text-gray-600'}`;
      div.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle w-4"></i> <span>${message}</span>`;
      container.prepend(div);
    }

    const delay = ms => new Promise(res => setTimeout(res, ms));

    async function handleBroadcast(e) {
      e.preventDefault();
      const form = document.getElementById('broadcastForm');
      const formData = new FormData(form);
      const btnSubmit = document.getElementById('btnSubmit');

      // Validasi Wajib Template
      if (!formData.get('template_name')) {
        Swal.fire('Error', 'Nama Template wajib diisi', 'error');
        return;
      }

      // Cek Gambar Header
      const headerFile = formData.get('header_media');
      if (!headerFile || headerFile.size === 0) {
        Swal.fire('Error', 'Header Gambar wajib diupload!', 'error');
        return;
      }

      btnSubmit.disabled = true;
      btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
      document.getElementById('defaultStatus').classList.add('hidden');
      document.getElementById('progressContainer').classList.remove('hidden');
      document.getElementById('logContainer').innerHTML = '';
      document.getElementById('progressBar').style.width = '0%';

      try {
        let recipientsData = [];

        // ==========================================
        // 1. PROSES PENERIMA (MANUAL CSV ONLY)
        // ==========================================
        addLog('Memproses daftar penerima CSV...', 'info');

        const manualText = formData.get('manual_numbers');
        if (!manualText.trim()) throw new Error('Input manual kosong.');

        const lines = manualText.split('\n');

        lines.forEach(line => {
          if (!line.trim()) return;

          // Pisahkan Nomor dan Variabel berdasarkan koma
          let parts = line.split(',');
          let rawPhone = parts[0].trim();

          // Ambil sisa parts sebagai variabel
          let specificVars = parts.slice(1).join(',').trim();

          // Bersihkan Nomor HP
          let cleanPhone = rawPhone.replace(/[^0-9]/g, '');
          if (cleanPhone.startsWith('0')) {
            cleanPhone = '62' + cleanPhone.slice(1);
          }

          if (cleanPhone) {
            recipientsData.push({
              phone: cleanPhone,
              vars: specificVars
            });
          }
        });

        const total = recipientsData.length;
        if (total === 0) throw new Error('Tidak ada nomor tujuan yang valid dalam input CSV.');
        addLog(`Siap mengirim ke ${total} nomor.`, 'info');

        // ==========================================
        // 2. UPLOAD HEADER IMAGE
        // ==========================================
        let headerUrl = '';

        addLog('Mengupload Gambar Header...', 'info');
        const mediaParams = new FormData();
        mediaParams.append('action', 'upload_media');
        mediaParams.append('media', headerFile);

        const upRes = await fetch('/src/api/whatsapp/send_broadcast.php', {
          method: 'POST', headers: { 'Authorization': `Bearer ${wa_token}` }, body: mediaParams
        });
        const upData = await upRes.json();
        if (!upData.success) throw new Error('Gagal upload header: ' + upData.message);

        headerUrl = upData.url;
        addLog('Gambar Header berhasil diupload.', 'success');

        // ==========================================
        // 3. SEND LOOP
        // ==========================================
        let successCount = 0;
        let failCount = 0;

        for (let i = 0; i < total; i++) {
          const target = recipientsData[i]; // { phone, vars }
          const sendParams = new FormData();

          sendParams.append('action', 'send_message');
          sendParams.append('phone', target.phone);
          // Paksa tipe template
          sendParams.append('message_type', 'template');

          // Parameter Template
          sendParams.append('template_name', formData.get('template_name'));
          sendParams.append('template_lang', formData.get('template_lang'));
          sendParams.append('template_body_vars', target.vars); // Variabel unik dari CSV

          if (headerUrl) sendParams.append('template_header_url', headerUrl);

          try {
            const res = await fetch('/src/api/whatsapp/send_broadcast.php', {
              method: 'POST', headers: { 'Authorization': `Bearer ${wa_token}` }, body: sendParams
            });
            const data = await res.json();

            // Format log info variabel (jika ada)
            const varLog = target.vars ? `[${target.vars}]` : '';

            if (data.success) {
              successCount++;
              addLog(`${target.phone} ${varLog}: Terkirim`, 'success');
            } else {
              failCount++;
              addLog(`${target.phone}: Gagal`, 'error');
            }
          } catch (err) {
            failCount++;
            addLog(`${target.phone}: Error Koneksi`, 'error');
          }

          // Update Progress
          const percent = Math.round(((i + 1) / total) * 100);
          document.getElementById('progressBar').style.width = `${percent}%`;
          document.getElementById('progressText').textContent = `${percent}%`;
          document.getElementById('countText').textContent = `${i + 1}/${total}`;

          await delay(500); // Jeda
        }

        Swal.fire('Selesai', `Sukses: ${successCount}, Gagal: ${failCount}`, 'success');

      } catch (error) {
        Swal.fire('Error', error.message, 'error');
        addLog(error.message, 'error');
      } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Broadcast';
      }
    }
  </script>
</body>

</html>