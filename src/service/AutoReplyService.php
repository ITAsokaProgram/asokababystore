<?php

require_once __DIR__ . '/../config/Config.php';

class AutoReplyService
{
    private $conn;
    private $logger;

    public function __construct($dbConnection, $logger)
    {
        $this->conn = $dbConnection;
        $this->logger = $logger;
    }

    public function cariBalasan($isiPesanUser)
    {
        // PENGAMAN 1: Cek koneksi
        if (!$this->conn) {
            return null;
        }

        try {
            // Cek tabel ada atau tidak (biar gak crash kalau lupa create table)
            $checkTable = $this->conn->query("SHOW TABLES LIKE 'wa_balasan_otomatis'");
            if ($checkTable->num_rows == 0) {
                return null; 
            }

            $sql = "SELECT kata_kunci, isi_balasan FROM wa_balasan_otomatis WHERE status_aktif = '1'";
            $result = $this->conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $isiPesanLower = strtolower($isiPesanUser);
                while ($row = $result->fetch_assoc()) {
                    $kataKunciDb = strtolower($row['kata_kunci']);
                    
                    // Logic pencarian kata kunci
                    if (strpos($isiPesanLower, $kataKunciDb) !== false) {
                        return $row['isi_balasan'];
                    }
                }
            }
        } catch (Exception $e) {
            // PENGAMAN 2: Kalau error query, diam saja (return null), jangan matikan bot
            if ($this->logger) {
                $this->logger->error("AutoReply Error: " . $e->getMessage());
            }
            return null;
        }

        return null;
    }
}