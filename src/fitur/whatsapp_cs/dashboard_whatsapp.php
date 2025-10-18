<?php
session_start();
include '../../../aa_kon_sett.php';

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
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    #chat-layout {
      display: grid;
      grid-template-columns: 280px 1fr;
      height: calc(100vh - 180px);
      gap: 0;
      overflow: hidden;
      transition: grid-template-columns 0.3s ease;
    }
    
    @media (max-width: 768px) {
      #chat-layout {
        grid-template-columns: 1fr;
        height: calc(100vh - 140px);
      }
      
      #conversation-list-container {
        display: none !important;
      }
      
      #conversation-list-container.mobile-show {
        display: flex !important;
        position: fixed;
        top: 64px;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 30;
        background: white;
      }
    }
    
    #conversation-list-container {
      border-right: 1px solid #e5e7eb;
      display: flex;
      flex-direction: column;
      background: #ffffff;
      transition: all 0.3s ease-in-out;
    }

    #conversation-list-container.collapsed {
      width: 64px;
      min-width: 64px;
    }

    #conversation-list-container.collapsed #conversation-list,
    #conversation-list-container.collapsed h2 {
      display: none;
    }

    #chat-layout.list-collapsed {
      grid-template-columns: 64px 1fr;
    }

    #toggle-conversation-list {
      transition: transform 0.3s ease, background-color 0.2s ease;
    }

    #toggle-conversation-list:hover {
      background-color: #f3f4f6;
      border-radius: 0.375rem;
    }

    #conversation-list-container.collapsed #toggle-conversation-list svg {
      transform: rotate(180deg);
    }
    
    #conversation-list {
      overflow-y: auto;
      flex: 1;
    }

    #conversation-list::-webkit-scrollbar {
      width: 6px;
    }
    
    #conversation-list::-webkit-scrollbar-track {
      background: #f9fafb;
    }
    
    #conversation-list::-webkit-scrollbar-thumb {
      background: #d1d5db;
      border-radius: 10px;
    }
    
    #conversation-list::-webkit-scrollbar-thumb:hover {
      background: #9ca3af;
    }
    
    .conversation-item {
      transition: all 0.2s ease;
      border-left: 3px solid transparent;
    }
    
    .conversation-item:hover {
      background-color: #eff6ff !important;
      border-left-color: #3b82f6;
    }
    
    .conversation-item.active {
      background-color: #dbeafe !important;
      border-left-color: #2563eb;
    }
    
    #chat-window {
      display: flex;
      flex-direction: column;
      overflow: hidden;
      background: #f9fafb;
    }

    #active-chat {
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    
    #chat-header {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      padding: 1.25rem 1.5rem;
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    #chat-header.show {
      display: flex; 
    }
    
    #chat-header i {
      color: white;
    }
    
    #chat-header span {
      color: white;
    }

    #message-container {
      flex: 1;
      overflow-y: auto;
      padding: 1.5rem;
      background: linear-gradient(to bottom, #f9fafb 0%, #ffffff 100%);
      min-height: 0;
      scroll-behavior: smooth;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    
    #message-container::-webkit-scrollbar {
      width: 6px;
    }
    
    #message-container::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 10px;
    }
    
    #message-container::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 10px;
    }
    
    #message-container::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    .date-separator {
      text-align: center;
      margin: 1.5rem auto 1rem;
      padding: 6px 14px;
      background-color: #e2e8f0;
      color: #475569;
      border-radius: 16px;
      font-size: 0.75rem; 
      font-weight: 600;
      width: fit-content;
      box-shadow: 0 1px 3px rgba(0,0,0,0.08);
      letter-spacing: 0.3px;
    }

    .message-bubble {
      display: inline-block;
      position: relative;
      max-width: 70%;
      word-wrap: break-word;
      padding: 0.875rem 1rem;
      border-radius: 1.125rem;
      margin-bottom: 0.625rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      animation: slideIn 0.3s ease;
      font-size: 0.9rem;
      line-height: 1.6;
      padding-bottom: 1.75rem;
    }
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .user-bubble {
      background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
      color: #1e293b;
      align-self: flex-start;
      border-bottom-left-radius: 0.375rem;
    }
    
    .admin-bubble {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      align-self: flex-end;
      border-bottom-right-radius: 0.375rem;
      margin-left: auto;
    }

    .message-content {
      display: block;
    }

    .text-content {
      min-width: 80px;
    }

    .media-content {
      padding: 0.5rem;
      padding-bottom: 2rem;
      min-width: 200px;
    }

    .media-bubble {
      background: transparent !important;
      box-shadow: none !important;
      padding: 0 !important;
    }

    .media-bubble .message-content {
      background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
      border-radius: 1.125rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .admin-bubble.media-bubble .message-content {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .media-item {
      max-width: 280px;
      width: 100%;
      height: auto;
      border-radius: 0.875rem;
      display: block;
    }

    .audio-content {
      padding: 0.75rem;
      padding-bottom: 2rem;
      min-width: 260px;
    }

    .audio-player {
      width: 100%;
      height: 36px;
      outline: none;
    }

    .message-time {
      position: absolute;
      bottom: 6px;
      right: 10px;
      font-size: 0.7rem;
      color: #64748b;
      line-height: 1;
      white-space: nowrap;
      pointer-events: none;
      z-index: 10;
      font-weight: 500;
    }

    .admin-bubble .message-time {
      color: rgba(255, 255, 255, 0.85);
    }

    .media-bubble .message-time {
      bottom: 8px;
      right: 12px;
      background: rgba(0, 0, 0, 0.6);
      color: white;
      padding: 3px 7px;
      border-radius: 6px;
      font-size: 0.65rem;
      font-weight: 600;
    }

    .admin-bubble.media-bubble .message-time {
      background: rgba(0, 0, 0, 0.5);
    }
    
    #message-input-area {
      background: white;
      border-top: 1px solid #e5e7eb;
      padding: 1rem 1.5rem;
      box-shadow: 0 -4px 12px rgba(0,0,0,0.06);
    }

    #media-preview-container {
      background: #f9fafb;
      border: 2px dashed #d1d5db;
      transition: all 0.2s ease;
    }

    #media-preview-container:hover {
      border-color: #3b82f6;
    }
    
    #message-input {
      resize: none;
      max-height: 120px;
      transition: all 0.2s ease;
      font-size: 0.95rem;
      border: 2px solid #e5e7eb;
    }
    
    #message-input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
      outline: none;
    }
    
    #send-button {
      flex-shrink: 0;
      min-width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
    }
    
    #send-button:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
    }

    #send-button:active:not(:disabled) {
      transform: translateY(0);
    }
    
    #send-button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      box-shadow: none;
    }
    
    #chat-placeholder {
      background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    }
    
    #chat-placeholder i {
      color: #cbd5e1;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
    }
    
    #end-chat-button {
      font-size: 0.875rem;
      padding: 0.5rem 1rem;
      transition: all 0.2s ease;
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }
    
    #end-chat-button:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    #end-chat-button:active {
      transform: translateY(0);
    }
    
    #mobile-back-button {
      display: none;
    }
    
    @media (max-width: 768px) {
      #mobile-back-button {
        display: flex;
      }
      
      #chat-header {
        padding: 0.875rem 1rem;
      }
      
      #message-container {
        padding: 1rem;
      }
      
      #message-input-area {
        padding: 0.75rem 1rem;
      }
      
      .message-bubble {
        max-width: 85%;
        font-size: 0.875rem;
      }
      
      .media-item {
        max-width: 240px;
      }
      
      .audio-content {
        min-width: 220px;
      }
      
      #end-chat-button {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
      }
    }
    
    .loading-spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255,255,255,.3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    .live-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
      0%, 100% {
        opacity: 1;
      }
      50% {
        opacity: .8;
      }
    }
    
    .live-badge::before {
      content: '';
      width: 7px;
      height: 7px;
      background-color: #dc2626;
      border-radius: 50%;
      animation: blink 1.5s ease-in-out infinite;
    }
    
    @keyframes blink {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.3; }
    }

    .header-card {
      background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
      border: 1px solid #e5e7eb;
    }

    .icon-wrapper {
      box-shadow: 0 2px 8px rgba(34, 197, 94, 0.15);
    }

    #remove-media-button {
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    #remove-media-button:hover {
      background-color: #dc2626;
      transform: scale(1.1);
    }

    label[for="media-input"] {
      transition: all 0.2s ease;
    }

    label[for="media-input"]:hover {
      background-color: #f3f4f6;
      border-radius: 0.5rem;
    }
  </style>
</head>

<body class="bg-gray-50">

  <?php include '../../component/navigation_report.php' ?>
  <?php include '../../component/sidebar_report.php' ?>
  
  <main id="main-content" class="flex-1 p-4 md:p-6 ml-64">

    <div class="header-card p-4 md:p-6 rounded-2xl mb-4 md:mb-6 bg-white shadow-sm sm:block hidden">
      <div class="flex items-center gap-3 md:gap-4">
        <div class="icon-wrapper bg-green-100 p-2.5 md:p-3 rounded-full">
          <i class="fab fa-whatsapp text-2xl md:text-3xl text-green-600"></i>
        </div>
        <div>
          <h1 class="text-xl md:text-2xl font-bold text-gray-800 mb-1">Live Chat WhatsApp</h1>
          <p class="text-xs md:text-sm text-gray-600">Kelola percakapan pelanggan secara real-time</p>
        </div>
      </div>
    </div>
    <div class="p-3 border-b border-gray-200 bg-gray-50/50">
          <select id="status-filter" 
          class="w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
              <option value="semua">Semua Percakapan</option>
              <option value="live_chat">Live Chat</option>
              <option value="umum">Umum</option>
          </select>
      </div>
    <div id="chat-layout" class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-200">
      <div id="conversation-list-container" class="flex flex-col">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
          <button id="mobile-close-list" class="block md:hidden text-gray-600 hover:text-gray-900 transition-all p-2 rounded-lg -ml-2" title="Kembali ke Chat">
            <i class="fas fa-arrow-left h-5 w-5"></i>
          </button>
          <h2 class="text-base md:text-lg font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-comments text-blue-500"></i>
            Percakapan
          </h2>
          <button id="toggle-conversation-list" class="hidden md:block text-gray-600 hover:text-gray-900 transition-all p-2 rounded-lg" title="Collapse/Expand">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
        <div id="conversation-list" class="overflow-y-auto flex-1 bg-white">
          <div class="p-8 text-center text-gray-500">
            <div class="loading-spinner mx-auto" style="border-color: #cbd5e1; border-top-color: #3b82f6;"></div>
            <p class="mt-3 text-sm">Memuat percakapan...</p>
          </div>
        </div>
      </div>
      <div id="chat-window" class="flex flex-col">
        <div id="chat-header" class="flex justify-between items-center">
          <div class="flex items-center gap-2 md:gap-3 flex-1 min-w-0">
              <button id="mobile-list-toggle" class="block md:hidden text-white hover:bg-white hover:bg-opacity-20 transition-all p-2 rounded-lg mr-1" title="Tampilkan Daftar Obrolan">
                <i class="fas fa-bars text-lg"></i>
              </button>
              <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-user text-lg"></i>
              </div>
              <span id="chat-with-phone" class="font-semibold text-sm md:text-base truncate"></span>
          </div>
          <button id="end-chat-button" class="hidden bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all flex items-center gap-1.5">
              <i class="fas fa-times-circle"></i>
              <span class="hidden sm:inline">Akhiri</span>
          </button>
        </div>

        <div id="chat-placeholder" class="flex flex-col items-center justify-center h-full text-gray-500 p-4">
          <i class="fas fa-comments text-5xl md:text-6xl mb-4"></i>
          <p class="text-base md:text-lg font-semibold mb-1">Pilih percakapan untuk memulai</p>
          <p class="text-xs md:text-sm text-gray-400">Pesan akan muncul di sini</p>
        </div>

        <div id="active-chat" class="hidden flex-col flex-1 min-h-0">
          <div id="message-container" class="flex-1 overflow-y-auto flex flex-col">
          </div>

          <div id="message-input-area" class="hidden">
            <div id="media-preview-container" class="hidden mb-3 p-3 rounded-xl relative w-fit">
                <button id="remove-media-button" class="absolute -top-2 -right-2 bg-gray-700 text-white rounded-full h-6 w-6 flex items-center justify-center text-sm z-10 hover:bg-red-600 transition-all" title="Hapus Media">&times;</button>
                <img id="media-preview-image" class="hidden rounded-lg shadow-sm" style="max-height: 200px;">
                <video id="media-preview-video" class="hidden rounded-lg shadow-sm" controls style="max-height: 200px;"></video>
            </div>
            <div class="flex items-end gap-2 md:gap-3">
              <label for="media-input" class="cursor-pointer p-3 text-gray-500 hover:text-blue-500 transition-all">
                  <i class="fas fa-paperclip text-xl"></i>
              </label>
              <input type="file" id="media-input" class="hidden" accept="image/*,video/*">
              
              <textarea id="message-input" rows="1" placeholder="Ketik balasan Anda..." class="flex-1 p-3 md:p-3.5 border rounded-xl focus:outline-none resize-none text-sm md:text-base"></textarea>
              <button id="send-button" class="bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all disabled:bg-gray-400">
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
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>
</html>