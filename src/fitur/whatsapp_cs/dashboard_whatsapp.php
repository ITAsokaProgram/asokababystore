<?php
session_start();
include '../../../aa_kon_sett.php';
// Anda mungkin perlu menyertakan middleware untuk memastikan hanya admin yang bisa mengakses halaman ini.
// Contoh: include '../../auth/middleware_admin.php'; 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard CS WhatsApp</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
  <link rel="stylesheet" href="../../style/header.css">
  <link rel="stylesheet" href="../../style/sidebar.css">
  <link rel="stylesheet" href="../../style/animation-fade-in.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../style/default-font.css">
  <link rel="stylesheet" href="../../output2.css">
  <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
  <style>
    #chat-layout {
      display: grid;
      grid-template-columns: 350px 1fr;
      height: calc(100vh - 150px);
      overflow: hidden; 
    }
    
    #chat-window {
      display: flex;
      flex-direction: column;
      overflow: hidden; 
    }

    #active-chat {
      display: flex;
      flex-direction: column;
      height: 100%; 
    }

    #message-container {
      flex: 1; 
      overflow-y: auto;
      min-height: 0;
    }

    .conversation-item.active {
      background-color: #e0f2fe;
    }
    .message-bubble {
      max-width: 70%;
      word-wrap: break-word;
    }
    .user-bubble {
      background-color: #f1f5f9; 
      align-self: flex-start;
    }
    .admin-bubble {
      background-color: #dbeafe; 
      align-self: flex-end;
    }
  </style>
</head>

<body class="bg-gray-50">

  <?php include '../../component/navigation_report.php' ?>
  <?php include '../../component/sidebar_report.php' ?>
  
  <main id="main-content" class="flex-1 p-4 md:p-6 ml-64">
    <div class="header-card p-6 rounded-2xl mb-6 bg-white shadow-sm">
      <div class="flex items-center gap-4">
        <div class="icon-wrapper bg-green-100 p-3 rounded-full">
          <i class="fab fa-whatsapp text-3xl text-green-600"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-800 mb-1">Live Chat WhatsApp</h1>
          <p class="text-sm text-gray-600">Kelola percakapan pelanggan secara real-time</p>
        </div>
      </div>
    </div>

    <div id="chat-layout" class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-200">
      <div id="conversation-list-container" class="border-r border-gray-200 flex flex-col">
        <div class="p-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-700">Percakapan</h2>
        </div>
        <div id="conversation-list" class="overflow-y-auto flex-1">
          <div class="p-8 text-center text-gray-500">
            <i class="fas fa-spinner fa-spin text-2xl"></i>
            <p class="mt-2">Memuat percakapan...</p>
          </div>
        </div>
      </div>

      <div id="chat-window" class="flex flex-col">
        <div id="chat-placeholder" class="flex flex-col items-center justify-center h-full text-gray-500">
          <i class="fas fa-comments text-5xl"></i>
          <p class="mt-4 text-lg">Pilih percakapan untuk memulai</p>
          <p class="text-sm">Pesan akan muncul di sini.</p>
        </div>

        <div id="active-chat" class="hidden flex-col h-full">
          <div id="chat-header" class="p-4 border-b border-gray-200 flex items-center">
            <i class="fas fa-user-circle text-2xl text-gray-500 mr-3"></i>
            <span id="chat-with-phone" class="font-semibold text-gray-800"></span>
          </div>

          <div id="message-container" class="flex-1 p-4 overflow-y-auto flex flex-col space-y-4 chat-messages">
          </div>

          <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center space-x-3">
              <textarea id="message-input" rows="1" placeholder="Ketik balasan Anda..." class="flex-1 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
              <button id="send-button" class="px-5 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors shadow-sm disabled:bg-gray-400">
                <i class="fas fa-paper-plane"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  
  <script src="/src/js/middleware_auth.js"></script>
  <script src="/src/js/whatsapp/cs_whatsapp.js" type="module"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>