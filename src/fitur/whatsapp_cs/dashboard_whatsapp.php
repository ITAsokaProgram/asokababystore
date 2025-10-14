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
    #chat-layout {
      display: grid;
      grid-template-columns: 320px 1fr;
      height: calc(100vh - 180px);
      gap: 0;
      overflow: hidden;
    }
    
    @media (max-width: 1024px) {
      #chat-layout {
        grid-template-columns: 280px 1fr;
        height: calc(100vh - 160px);
      }
    }
    
    @media (max-width: 768px) {
      #chat-layout {
        grid-template-columns: 1fr;
        height: calc(100vh - 140px);
      }
      
      #conversation-list-container {
        display: none;
      }
      
      #conversation-list-container.mobile-show {
        display: flex;
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
      background: #fafafa;
    }
    
    #conversation-list {
      overflow-y: auto;
      flex: 1;
    }
    
    .conversation-item {
      transition: all 0.2s ease;
      border-left: 3px solid transparent;
    }
    
    .conversation-item:hover {
      background-color: #f3f4f6;
      border-left-color: #3b82f6;
    }
    
    .conversation-item.active {
      background-color: #eff6ff;
      border-left-color: #2563eb;
    }
    
    #chat-window {
      display: flex;
      flex-direction: column;
      overflow: hidden;
      background: white;
    }

    #active-chat {
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    
    #chat-header {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      padding: 1rem 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
    }
    
    #message-container::-webkit-scrollbar {
      width: 6px;
    }
    
    #message-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }
    
    #message-container::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 10px;
    }
    
    #message-container::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    .message-bubble {
      max-width: 100%;
      word-wrap: break-word;
      padding: 0.75rem 1rem;
      border-radius: 1rem;
      margin-bottom: 0.75rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      animation: slideIn 0.3s ease;
      font-size: 0.9rem;
      line-height: 1.5;
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
      border-bottom-left-radius: 0.25rem;
    }
    
    .admin-bubble {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      align-self: flex-end;
      border-bottom-right-radius: 0.25rem;
      margin-left: auto;
    }
    
    #message-input-area {
      background: white;
      border-top: 1px solid #e5e7eb;
      padding: 1rem 1.5rem;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
    
    #message-input {
      resize: none;
      max-height: 120px;
      transition: all 0.2s ease;
      font-size: 0.95rem;
    }
    
    #message-input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    #send-button {
      flex-shrink: 0;
      min-width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }
    
    #send-button:hover:not(:disabled) {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    #send-button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    #chat-placeholder {
      background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    }
    
    #chat-placeholder i {
      color: #cbd5e1;
    }
    
    #end-chat-button {
      font-size: 0.875rem;
      padding: 0.5rem 1rem;
      transition: all 0.2s ease;
    }
    
    #end-chat-button:hover {
      transform: scale(1.02);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    
    #mobile-back-button {
      display: none;
    }
    
    @media (max-width: 768px) {
      #mobile-back-button {
        display: flex;
      }
      
      #chat-header {
        padding: 0.75rem 1rem;
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
      gap: 0.25rem;
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
      width: 6px;
      height: 6px;
      background-color: #dc2626;
      border-radius: 50%;
      animation: blink 1.5s ease-in-out infinite;
    }
    
    @keyframes blink {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.3; }
    }
    #conversation-list-container {
      transition: all 0.3s ease-in-out;
    }

    #conversation-list-container.collapsed {
      grid-column: 1;
      width: 60px;
      min-width: 60px;
    }

    #conversation-list-container.collapsed #conversation-list,
    #conversation-list-container.collapsed h2 {
      display: none;
    }

    #conversation-list-container.collapsed .p-3 {
      padding: 1rem 0.5rem;
      justify-content: center;
    }

    #chat-layout.list-collapsed {
      grid-template-columns: 60px 1fr;
    }

    #toggle-conversation-list i {
      transition: transform 0.3s ease;
    }

    #conversation-list-container.collapsed #toggle-conversation-list i {
      transform: rotate(180deg);
    }

    .media-bubble {
        padding: 0.5rem !important;
        background: transparent !important;
        box-shadow: none !important; 
    }

    .media-bubble img,
    .media-bubble video {
        max-width: 280px; 
        height: auto;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb; 
    }

    .admin-bubble.media-bubble {
        margin-left: auto;
    }

    .message-bubble {
        position: relative;
        padding-bottom: 20px;
        max-width: 75%;
    }

    .message-bubble.media-bubble {
        padding: 5px;
        padding-bottom: 22px; 
    }

    .message-time {
        position: absolute;
        bottom: 8px;
        right: 8px;
        font-size: 0.7rem;
        color: #9ca3af; 
        line-height: 1;
    }

    .admin-bubble .message-time {
        color: #e5e7eb; 
    }

    .message-bubble {
        display: inline-block;
        position: relative;
        max-width: 75%;
        word-wrap: break-word;
        border-radius: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .user-bubble {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #1e293b;
        align-self: flex-start;
        border-bottom-left-radius: 0.25rem;
    }

    .admin-bubble {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 0.25rem;
        margin-left: auto;
    }

    .message-content {
        display: block;
    }

    .text-content {
        min-width: 80px;
    }

    .media-content {
        padding: 0.35rem;
        padding-bottom: 1.75rem; 
        min-width: 200px;
    }

    .media-bubble {
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
    }

    .media-bubble .message-content {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .admin-bubble.media-bubble .message-content {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .media-item {
        max-width: 280px;
        width: 100%;
        height: auto;
        border-radius: 0.75rem;
        display: block;
    }

    .audio-content {
        padding: 0.5rem 0.75rem;
        padding-bottom: 1.75rem; 
        min-width: 250px;
    }

    .audio-player {
        width: 100%;
        height: 32px;
        outline: none;
    }

    .message-time {
        position: absolute;
        bottom: 4px;
        right: 8px;
        font-size: 0.7rem;
        color: #64748b;
        line-height: 1;
        white-space: nowrap;
        pointer-events: none;
        z-index: 10;
    }

    .admin-bubble .message-time {
        color: rgba(255, 255, 255, 0.8);
    }

    .media-bubble .message-time {
        bottom: 6px;
        right: 10px;
        background: rgba(0, 0, 0, 0.5);
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.65rem;
    }

    .admin-bubble.media-bubble .message-time {
        background: rgba(0, 0, 0, 0.4);
    }

    @media (max-width: 768px) {
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
        
        .text-content {
        }
    }
    


  </style>
</head>

<body class="bg-gray-50">

  <?php include '../../component/navigation_report.php' ?>
  <?php include '../../component/sidebar_report.php' ?>
  
  <main id="main-content" class="flex-1 p-4 md:p-6 ml-64">

    <div class="header-card p-4 md:p-6 rounded-2xl mb-4 md:mb-6 bg-white shadow-sm">
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



    <div id="chat-layout" class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-200">
      <div id="conversation-list-container" class="flex flex-col">
        <div class="p-3 md:p-4 border-b border-gray-200 flex items-center justify-between relative">
          <h2 class="text-base md:text-lg font-semibold text-gray-700">Percakapan</h2>
          <button id="toggle-conversation-list" class="md:block text-gray-600 hover:text-gray-900 transition-colors p-1" title="Collapse/Expand">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>

          <button id="mobile-close-list" class="hidden md:hidden text-gray-500 hover:text-gray-700">
            <i class="fas fa-times text-xl"></i>
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
        <div id="chat-placeholder" class="flex flex-col items-center justify-center h-full text-gray-500 p-4">
          <i class="fas fa-comments text-4xl md:text-5xl mb-3"></i>
          <p class="text-base md:text-lg font-medium">Pilih percakapan untuk memulai</p>
          <p class="text-xs md:text-sm text-gray-400 mt-1">Pesan akan muncul di sini</p>
        </div>

        <div id="active-chat" class="hidden flex-col h-full">
          <div id="chat-header" class="flex justify-between items-center">
             <div class="flex items-center gap-2 md:gap-3 flex-1 min-w-0">
                <button id="mobile-back-button" class="text-white hover:text-gray-200 transition-colors">
                  <i class="fas fa-arrow-left text-lg"></i>
                </button>
                <i class="fas fa-user-circle text-xl md:text-2xl"></i>
                <span id="chat-with-phone" class="font-semibold text-sm md:text-base truncate"></span>
             </div>
             <button id="end-chat-button" class="hidden bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors shadow-sm flex items-center gap-1.5">
                 <i class="fas fa-times-circle"></i>
                 <span class="hidden sm:inline">Akhiri</span>
             </button>
          </div>

          <div id="message-container" class="flex-1 overflow-y-auto flex flex-col">
          </div>

          <div id="message-input-area" class="hidden">
            <div id="media-preview-container" class="hidden mb-2 p-2 border rounded-lg relative w-fit">
                <button id="remove-media-button" class="absolute -top-2 -right-2 bg-gray-600 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs z-10 hover:bg-red-500 transition-colors" title="Hapus Media">&times;</button>
                <img id="media-preview-image" class="hidden max-h-28 rounded">
                <video id="media-preview-video" class="hidden max-h-28 rounded" controls></video>
            </div>
            <div class="flex items-end gap-2 md:gap-3">
              <label for="media-input" class="cursor-pointer p-3 text-gray-500 hover:text-blue-500 transition-colors">
                  <i class="fas fa-paperclip text-lg"></i>
              </label>
              <input type="file" id="media-input" class="hidden" accept="image/*,video/*">
              
              <textarea id="message-input" rows="1" placeholder="Ketik balasan" class="flex-1 p-2.5 md:p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none text-sm md:text-base px-4 py-3"></textarea>
              <button id="send-button" class="bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors shadow-sm disabled:bg-gray-400">
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