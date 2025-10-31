<?php
session_start();
include '../../../aa_kon_sett.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('whatsapp_dashboard');
if (!$menuHandler->initialize()) {
  exit();
}
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
      height: calc(100vh - 90px);
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

      :fullscreen #conversation-list-container.mobile-show {

        top: 92px !important;
      }
    }

    #conversation-list-container {
      border-right: 1px solid #e5e7eb;
      display: flex;
      flex-direction: column;
      background: #ffffff;
      transition: all 0.3s ease-in-out;
      overflow: hidden;
    }

    #conversation-list.loading-disabled {
      pointer-events: none;
      opacity: 0.6;
      transition: opacity 0.2s ease-in-out;
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
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
      letter-spacing: 0.3px;
    }

    .message-bubble {
      display: inline-block;
      position: relative;
      max-width: 100%;
      word-wrap: break-word;
      padding: 0.875rem 1rem;
      border-radius: 1.125rem;
      margin-bottom: 0.625rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      animation: slideIn 0.3s ease;
      font-size: 0.9rem;
      line-height: 1.6;
      padding-bottom: 1rem;
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
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
      width: 260px;
      max-width: 100%;
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
      box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.06);
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
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.05));
    }

    #end-chat-button,
    #start-chat-button {
      font-size: 0.875rem;
      padding: 0.5rem 1rem;
      transition: all 0.2s ease;
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
        width: 220px;
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
      border: 3px solid rgba(255, 255, 255, .3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    .live-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {

      0%,
      100% {
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

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.3;
      }
    }

    .header-card {
      background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
      border: 1px solid #e5e7eb;
    }

    .icon-wrapper {
      box-shadow: 0 2px 8px rgba(34, 197, 94, 0.15);
    }

    #remove-media-button {
      background-color: #dc2626;

      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }



    label[for="media-input"] {
      transition: all 0.2s ease;
    }

    label[for="media-input"]:hover {
      background-color: #f3f4f6;
      border-radius: 0.5rem;
    }

    .swal2-input#branch-selector {
      font-size: 14px;
      padding: 10px 12px;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      transition: all 0.2s;
    }

    .swal2-input#branch-selector:focus {
      border-color: #3b82f6;
      outline: none;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Styling untuk optgroup */
    .swal2-input#branch-selector optgroup {
      font-weight: 700;
      color: #1f2937;
      background-color: #f3f4f6;
    }

    .swal2-input#branch-selector option {
      padding: 8px;
      color: #4b5563;
    }

    .swal2-input#branch-selector option:hover {
      background-color: #dbeafe;
    }

    /* Info box yang muncul setelah pilih cabang */
    #selected-info {
      animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Styling tombol quick contact */
    #quick-contact-button {
      position: relative;
      overflow: hidden;
    }

    #quick-contact-button::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }

    #quick-contact-button:hover::before {
      width: 300px;
      height: 300px;
    }

    /* Responsive untuk mobile */
    @media (max-width: 768px) {
      #quick-contact-button {
        justify-content: flex-start;
        padding-left: 1rem;
      }

      #quick-contact-button i {
        margin-right: 0.5rem;
      }
    }

    /* Custom Swal styles */
    .swal2-popup.swal2-modal {
      border-radius: 16px;
      padding: 1.5rem;
    }

    .swal2-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 1rem;
    }

    .swal2-html-container {
      margin: 0;
      padding: 0;
    }

    /* Toast notification untuk sukses */
    .swal2-toast {
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
    }

    .swal2-toast .swal2-title {
      font-size: 0.95rem;
      font-weight: 600;
    }
  </style>
</head>

<body class="bg-gray-50">

  <?php include '../../component/navigation_report.php' ?>
  <?php include '../../component/sidebar_report.php' ?>

  <main id="main-content" class="flex-1 p-4 md:p-6 ml-64">
    <div id="chat-layout" class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-200">
      <div id="conversation-list-container" class="flex flex-col">
        <div
          class="p-4 border-b border-gray-200 flex items-center md:justify-between  gap-3 bg-gradient-to-r from-gray-50 to-white">
          <button id="mobile-close-list"
            class="block md:hidden text-gray-600 hover:text-gray-900 transition-all p-2 rounded-lg -ml-2"
            title="Kembali ke Chat">
            <i class="fas fa-arrow-left h-5 w-5"></i>
          </button>

          <div id="status-filter-buttons" class="flex items-center gap-2 flex-wrap">
            <button data-filter="semua"
              class="filter-button relative active px-3 py-1 rounded-full text-xs font-medium bg-blue-500 text-white transition-all shadow-sm">
              <span>
                Semua
              </span>
              <span id="unread-all"
                class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold w-4 h-4 rounded-full flex items-center justify-center text-[10px]">0</span>
            </button>
            <button data-filter="live_chat"
              class="filter-button relative px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all">
              <span>Live Chat</span>
              <span id="unread-live_chat"
                class="hidden absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold w-4 h-4 rounded-full flex items-center justify-center text-[10px]">0</span>
            </button>

            <button data-filter="umum"
              class="filter-button relative px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all">
              <span>Umum</span>
              <span id="unread-umum"
                class="hidden absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold w-4 h-4 rounded-full flex items-center justify-center text-[10px]">0</span>
            </button>
          </div>
          <button id="toggle-conversation-list"
            class="hidden md:block text-gray-600 hover:text-gray-900 transition-all p-2 rounded-lg"
            title="Collapse/Expand">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                clip-rule="evenodd" />
            </svg>
          </button>
        </div>
        <div class="p-4 border-b border-gray-200 bg-white">
          <div class="relative">
            <input type="text" id="search-input" placeholder="Cari nama atau nomor..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-500">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <i class="fas fa-search"></i>
            </div>
          </div>
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
            <button id="mobile-list-toggle"
              class="block md:hidden text-white hover:bg-white hover:bg-opacity-20 transition-all p-2 rounded-lg mr-1"
              title="Tampilkan Daftar Obrolan">
              <i class="fas fa-bars text-lg"></i>
            </button>
            <div class="flex-1 min-w-0 space-y-1">
              <div class="flex items-center gap-1.5">
                <p id="chat-with-name" class="font-semibold text-base md:text-lg truncate"></p>
                <button id="edit-display-name-button"
                  class="hidden text-white hover:bg-white hover:bg-opacity-20 transition-all p-1.5 rounded-lg"
                  title="Ubah Nama Tampilan">
                  <i class="fas fa-pencil-alt text-xs"></i>
                </button>
                <button id="manage-labels-button"
                  class="hidden text-white hover:bg-white hover:bg-opacity-20 transition-all p-1.5 rounded-lg"
                  title="Kelola Label">
                  <i class="fas fa-tags text-xs"></i>
                </button>
              </div>
              <p id="chat-with-phone" class="text-sm md:text-base truncate"></p>
              <div id="active-chat-labels" class="flex flex-wrap gap-1 mt-1.5">
              </div>
            </div>
          </div>

          <div x-data="{ open: false, isDesktop: window.innerWidth >= 768 }"
            @resize.window="isDesktop = window.innerWidth >= 768" class="relative">

            <button @click="open = !open"
              class="block md:hidden text-white hover:bg-white hover:bg-opacity-20 transition-all p-2 rounded-lg"
              title="Opsi">
              <i class="fas fa-ellipsis-v"></i>
            </button>

            <div x-show="open || isDesktop" @click.away="open = false"
              x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="transform opacity-0 scale-95"
              x-transition:enter-end="transform opacity-100 scale-100"
              x-transition:leave="transition ease-in duration-75"
              x-transition:leave-start="transform opacity-100 scale-100"
              x-transition:leave-end="transform opacity-0 scale-95" class="absolute top-full right-0 mt-2 w-auto bg-white rounded-lg z-20 p-2 flex flex-col gap-2
                  md:flex md:flex-row md:items-center md:gap-1.5 md:gap-2 
                  md:static md:mt-0 md:w-auto md:bg-transparent md:rounded-none md:shadow-none md:z-auto md:p-0"
              style="display: none;" x-cloak>

              <button id="mobile-fullscreen-toggle" @click="open = false" class="flex w-full items-center justify-center gap-2 p-2 rounded-lg text-sm text-white bg-blue-500 hover:bg-blue-600 transition-all
                        md:hidden" title="Layar Penuh">
                <i id="fullscreen-icon" class="fas fa-expand"></i>
              </button>

              <!-- Tombol Quick Contact - BARU -->
              <button id="quick-contact-button" @click="open = false" class="hidden bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-all flex items-center justify-center gap-1.5 p-2 w-full
                        md:w-auto md:p-0" title="Kirim Kontak Cabang">
                <i class="fas fa-address-book md:p-2 md:pl-3"></i>
                <span class="inline md:p-2 md:pr-3">Kontak</span>
              </button>

              <button id="start-chat-button" @click="open = false" class="hidden bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all flex items-center justify-center gap-1.5 p-2 w-full
                        md:w-auto md:p-0">
                <i class="fas fa-play-circle md:p-2 md:pl-3"></i>
              </button>

              <button id="end-chat-button" @click="open = false" class="hidden bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all flex items-center justify-center gap-1.5 p-2 w-full
                        md:w-auto md:p-0">
                <i class="fas fa-times-circle md:p-2 md:pl-3"></i>
                <span class="inline sm:inline md:p-2 md:pr-3">Akhiri</span>
              </button>
            </div>
          </div>
        </div>

        <div id="chat-placeholder" class="flex flex-col items-center justify-center h-full text-gray-500 p-4">
          <i class="fas fa-comments text-5xl md:text-6xl mb-4"></i>
          <p class="text-base md:text-lg font-semibold mb-1 text-center">Pilih percakapan untuk memulai</p>
          <p class="text-xs md:text-sm text-gray-400 text-center">Pesan akan muncul di sini</p>
        </div>

        <div id="active-chat" class="hidden flex-col flex-1 min-h-0">
          <div id="message-container" class="flex-1 overflow-y-auto flex flex-col">
          </div>

          <div id="message-input-area" class="hidden">
            <div id="media-preview-container" class="hidden mb-3 p-3 rounded-xl relative w-fit">
              <button id="remove-media-button"
                class="absolute -top-2 -right-2 bg-gray-700 text-white rounded-full h-6 w-6 flex items-center justify-center text-sm z-10 hover:bg-red-600 transition-all"
                title="Hapus Media">&times;</button>
              <img id="media-preview-image" class="hidden rounded-lg shadow-sm" style="max-height: 200px;">
              <video id="media-preview-video" class="hidden rounded-lg shadow-sm" controls
                style="max-height: 200px;"></video>
            </div>
            <div class="flex items-end gap-2 md:gap-3">
              <label for="media-input" class="cursor-pointer p-3 text-gray-500 hover:text-blue-500 transition-all">
                <i class="fas fa-paperclip text-xl"></i>
              </label>
              <input type="file" id="media-input" class="hidden" accept="image/*,video/*">

              <textarea id="message-input" rows="1"
                class="flex-1 p-3 md:p-3.5 border rounded-xl focus:outline-none resize-none text-sm md:text-base"></textarea>
              <button id="send-button"
                class="bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all disabled:bg-gray-400">
                <i class="fas fa-paper-plane"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="/src/js/middleware_auth.js"></script>
  <script src="/src/js/whatsapp/cs_utils.js"></script>
  <script src="/src/js/whatsapp/cs_ui.js"></script>
  <script src="/src/js/whatsapp/cs_comms.js"></script>
  <script src="/src/js/whatsapp/cs_whatsapp.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    document.getElementById("toggle-sidebar").addEventListener("click", function () {
      document.getElementById("sidebar").classList.toggle("open");
    });
    document.addEventListener("DOMContentLoaded", function () {
      const sidebar = document.getElementById("sidebar");
      const closeBtn = document.getElementById("closeSidebar");

      closeBtn.addEventListener("click", function () {
        sidebar.classList.remove("open");
      });
    });
    document.getElementById("toggle-hide").addEventListener("click", function () {
      var sidebarTexts = document.querySelectorAll(".sidebar-text");
      let mainContent = document.getElementById("main-content");
      let sidebar = document.getElementById("sidebar");
      var toggleButton = document.getElementById("toggle-hide");
      var icon = toggleButton.querySelector("i");

      if (sidebar.classList.contains("w-64")) {
        sidebar.classList.remove("w-64", "px-5");
        sidebar.classList.add("w-16", "px-2");
        sidebarTexts.forEach((text) => text.classList.add("hidden"));
        mainContent.classList.remove("ml-64");
        mainContent.classList.add("ml-16");
        toggleButton.classList.add("left-20");
        toggleButton.classList.remove("left-64");
        icon.classList.remove("fa-angle-left");
        icon.classList.add("fa-angle-right");
      } else {
        sidebar.classList.remove("w-16", "px-2");
        sidebar.classList.add("w-64", "px-5");
        sidebarTexts.forEach((text) => text.classList.remove("hidden"));
        mainContent.classList.remove("ml-16");
        mainContent.classList.add("ml-64");
        toggleButton.classList.add("left-64");
        toggleButton.classList.remove("left-20");
        icon.classList.remove("fa-angle-right");
        icon.classList.add("fa-angle-left");
      }
    });
    document.addEventListener("DOMContentLoaded", function () {
      const profileImg = document.getElementById("profile-img");
      const profileCard = document.getElementById("profile-card");

      profileImg.addEventListener("click", function (event) {
        event.preventDefault();
        profileCard.classList.toggle("show");
      });

      document.addEventListener("click", function (event) {
        if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
          profileCard.classList.remove("show");
        }
      });
    });
  </script>
</body>

</html>