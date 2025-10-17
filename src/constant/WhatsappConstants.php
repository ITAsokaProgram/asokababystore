<?php

namespace Asoka\Constant;

class WhatsappConstants {

    public const MEDIA_UNSUPPORTED_LIVE_CHAT = "Maaf, saat ini kami hanya mendukung pesan teks, gambar, video, dan pesan suara dalam sesi live chat.";
    public const MEDIA_SIZE_EXCEEDED = "Maaf, %s yang Anda kirim melebihi batas maksimal %s.";
    public const TEXT_ONLY_TO_START = "Halo Kak! Untuk memulai percakapan, silakan kirim pesan dalam bentuk teks ya.";
    public const INVALID_LOCATION_DATA = "Maaf, data lokasi untuk cabang %s saat ini belum tersedia.";
    public const INVALID_OPTION = "Maaf, pilihan Anda tidak valid. Silakan coba lagi.";
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
    public const HOW_TO_ORDER_WA = "Berikut adalah langkah-langkah untuk melakukan pemesanan melalui WhatsApp:\n\n" .
                                  "1. Hubungi nomor WhatsApp Asoka Baby Store sesuai dengan cabang terdekat.\n" .
                                  "2. Lakukan pemesanan barang via WhatsApp.\n" .
                                  "3. Anda akan menerima foto struk sebagai acuan pembayaran.\n" .
                                  "4. Lakukan pembayaran sesuai dengan foto struk ke rekening Asoka Baby Store.\n" .
                                  "5. Kirimkan bukti transfer kepada Asoka Baby Store.\n" .
                                  "6. Asoka Baby Store akan menyiapkan barang sesuai dengan permintaan Anda.\n" .
                                  "7. Pesan GoSend / Grab Express apabila telah menerima konfirmasi pengambilan barang dari Asoka Baby Store.";
    public const PROMO_INFO = "Untuk melihat semua promo menarik yang sedang berlangsung, silakan kunjungi halaman promo di website kami:\n\nhttps://asokababystore.com/";
    public const FEEDBACK_INFO = "Kami sangat menghargai masukan dari Anda. Silakan sampaikan kritik dan saran Anda melalui halaman kontak di website kami:\n\nhttps://asokababystore.com/kontak";
    public const POINT_HISTORY_INFO = "Untuk melihat riwayat transaksi dan total poin yang Anda miliki, silakan login ke akun Anda di sini:\n\nhttps://asokababystore.com/log_in";
    public const OPERATIONAL_HOURS_INFO = "Berikut adalah informasi jam operasional toko kami:\n\n" .
                                          "*Jam Buka Store:*\n" .
                                          "Senin s/d Minggu: Pukul 08:30 WIB\n\n" .
                                          "*Jam Tutup Store:*\n" .
                                          "Senin s/d Jum'at: Pukul 21:30 WIB\n" .
                                          "Sabtu: Pukul 22:00 WIB\n" .
                                          "Minggu: Pukul 21:30 WIB";

    public const CS_CONNECT_SUCCESS = "Anda sekarang terhubung dengan Customer Service kami. Silakan sampaikan pertanyaan Anda.";
    public const CS_OUTSIDE_HOURS = "Maaf Ayah/Bunda, Customer Service kami sedang di luar jam operasional (Senin - Sabtu, 09:00 - 16:30).\n\nPesan Anda akan kami terima dan akan kami balas pada jam operasional berikutnya. Terima kasih.";
    
    public const BRANCH_LIST_PROMPT = "Silakan pilih cabang yang Anda tuju.";
    public const BRANCH_LIST_TITLE = "PILIH CABANG (Hal %d)";
    public const BRANCH_LIST_HEADER_JABODETABEK = "Asoka Baby Store Jabodetabek";
    public const BRANCH_LIST_HEADER_BELITUNG = "Asoka Baby Store Bangka & Belitung";
    public const NEXT_PAGE_TEXT = '➡️ Halaman Berikutnya';
    public const PREV_PAGE_TEXT = '⬅️ Halaman Sebelumnya';
}