<?php

namespace Asoka\Constant;

class WhatsappConstants {
    const LIVE_CHAT_END_MESSAGE = "Terima kasih telah menghubungi Customer Service ASOKA. Jika Ayah/Bunda memerlukan bantuan lebih lanjut, jangan ragu untuk menghubungi kami kembali ya. 😊";
    public const LIVE_CHAT_MENU_PROMPT = "Untuk memilih menu awal, silakan akhiri sesi chat ini terlebih dahulu.";
    public const MEDIA_UNSUPPORTED_LIVE_CHAT = "Maaf, saat ini kami hanya mendukung pesan teks, gambar, video, dan pesan suara dalam sesi live chat.";
    public const MEDIA_SIZE_EXCEEDED = "Maaf, %s yang Anda kirim melebihi batas maksimal %s.";
    public const TEXT_ONLY_TO_START = "Halo Ayah/Bunda! Untuk memulai percakapan, silakan kirim pesan dalam bentuk teks ya.";
    public const INVALID_LOCATION_DATA = "Maaf, data lokasi untuk cabang %s saat ini belum tersedia.";
    public const INVALID_OPTION = "Maaf, pilihan Ayah/Bunda tidak valid. Silakan coba lagi.";
    public const SENDING_LOCATION_NOTICE = "Mengirimkan lokasi: %s";
    public const SENDING_CONTACT_NOTICE = "Mengirimkan kontak: %s (%s)";

    public const REGEX_CHANGE_PHONE = '/https:\/\/asokababystore\.com\/verifikasi-wa\?token=([a-f0-9]{64})/';
    public const REGEX_RESET_PASSWORD = '/Token saya: (resetpw_[a-f0-9]{60})/';

    public const WELCOME_HEADER = "Asoka Baby Store";
    public const WELCOME_BODY = "Terimakasih telah menghubungi Asoka Baby Store.\n\n" .
                                "Jam Operasional:\n" .
                                "- Senin - Sabtu: 08.30 - 16.30 WIB\n" .
                                "- Hari Minggu dan Tanggal Merah: Tutup\n" .
                                "- Pesan yang masuk setelah pukul 16.30 WIB akan dibalas pada hari kerja berikutnya.\n\n" .
                                "Untuk informasi lainnya bisa diakses di website kami:\n" .
                                "asokababystore.com\n\n" .
                                "Silakan pilih menu di bawah ini untuk melanjutkan.";
    public const WELCOME_BUTTON_TEXT = "Lihat Pilihan Menu";

    public const CHOOSE_BRANCH_REGION_PROMPT = "Silakan pilih wilayah cabang yang ingin Anda hubungi.";
    public const CHOOSE_LOCATION_REGION_PROMPT = "Silakan pilih wilayah toko fisik yang ingin Anda lihat lokasinya.";
    
    public const HOW_TO_ORDER_WA_HEADER = "Cara Pesan via WhatsApp";
    public const HOW_TO_ORDER_WA_BODY = "Untuk melihat cara melakukan pemesanan melalui WhatsApp, silakan kunjungi halaman panduan pemesanan di website kami.";
    public const HOW_TO_ORDER_WA_BUTTON = "Lihat Panduan";
    public const HOW_TO_ORDER_WA_URL = "https://asokababystore.com/pesan_sekarang#cara-pesan";

    public const PROMO_INFO_HEADER = "Informasi Promo";
    public const PROMO_INFO_BODY = "Untuk melihat semua promo menarik yang sedang berlangsung, silakan kunjungi halaman promo di website kami.";
    public const PROMO_INFO_BUTTON = "Lihat Promo";
    public const PROMO_INFO_URL = "https://asokababystore.com/";

    public const FEEDBACK_INFO_HEADER = "Kritik & Saran";
    public const FEEDBACK_INFO_BODY = "Kami sangat menghargai masukan dari Ayah/Bunda. Silakan sampaikan kritik dan saran melalui halaman kontak di website kami.";
    public const FEEDBACK_INFO_BUTTON = "Beri Masukan";
    public const FEEDBACK_INFO_URL = "https://asokababystore.com/kontak";

    public const POINT_HISTORY_INFO_HEADER = "Cek Riwayat Poin";
    public const POINT_HISTORY_INFO_BODY = "Untuk melihat riwayat transaksi dan total poin yang Ayah/Bunda miliki, silakan login ke akun di sini.";
    public const POINT_HISTORY_INFO_BUTTON = "Login & Cek Poin";
    public const POINT_HISTORY_INFO_URL = "https://asokababystore.com/log_in";
    public const OPERATIONAL_HOURS_INFO = "Berikut adalah informasi jam operasional toko kami:\n\n" .
                                          "*Jam Buka Store:*\n" .
                                          "Senin s/d Minggu: Pukul 08:30 WIB\n\n" .
                                          "*Jam Tutup Store:*\n" .
                                          "Senin s/d Jum'at: Pukul 21:30 WIB\n" .
                                          "Sabtu: Pukul 22:00 WIB\n" .
                                          "Minggu: Pukul 21:30 WIB";

    public const CS_CONNECT_SUCCESS = "Anda sekarang terhubung dengan Customer Service kami. Silakan sampaikan pertanyaan Ayah/Bunda.";
    public const CS_OUTSIDE_HOURS = "Maaf Ayah/Bunda, Customer Service kami sedang di luar jam operasional (Senin - Sabtu, 09:00 - 16:30).\n\nPesan Ayah/Bunda akan kami terima dan akan kami balas pada jam operasional berikutnya. Terima kasih.";
    public const LIVE_CHAT_INACTIVITY_CLOSURE = "Mohon maaf Ayah/Bunda karena tidak ada respon yang Kami terima sampai dengan saat ini, maka chat ini akan kami akhiri. Jika ada hal lain yang ingin ditanyakan, Ayah/Bunda dapat menghubungi kami kembali. Dengan senang hati kami akan membantu. Terima kasih";

    public const BRANCH_LIST_PROMPT = "Silakan pilih cabang yang Ayah/Bunda tuju.";
    public const BRANCH_LIST_TITLE = "PILIH CABANG (Hal %d)";
    public const BRANCH_LIST_HEADER_JABODETABEK = "Asoka Baby Store Jabodetabek";
    public const BRANCH_LIST_HEADER_BELITUNG = "Asoka Baby Store Bangka & Belitung";
    public const NEXT_PAGE_TEXT = '➡️ Halaman Berikutnya';
    public const PREV_PAGE_TEXT = '⬅️ Halaman Sebelumnya';
}