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
        try {
            // 1. Ambil semua keyword aktif dari tabel KAMUS (Parent)
            $sql = "SELECT id, kata_kunci FROM wa_balasan_otomatis_kamus WHERE status_aktif = '1'";
            $result = $this->conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $isiPesanLower = strtolower(trim($isiPesanUser));
                $foundId = null;

                // 2. Loop untuk mencocokkan keyword (Partial Match)
                while ($row = $result->fetch_assoc()) {
                    $kataKunciDb = strtolower($row['kata_kunci']);

                    // Cek apakah pesan user mengandung kata kunci
                    if (strpos($isiPesanLower, $kataKunciDb) !== false) {
                        $foundId = $row['id'];
                        break; // Stop jika sudah ketemu match pertama
                    }
                }

                // 3. Jika ID ketemu, ambil daftar pesan dari tabel PESAN (Child)
                if ($foundId) {
                    $stmt = $this->conn->prepare("SELECT isi_pesan FROM wa_balasan_otomatis_pesan WHERE kamus_id = ? ORDER BY urutan ASC");
                    $stmt->bind_param("i", $foundId);
                    $stmt->execute();
                    $resPesan = $stmt->get_result();

                    $daftarPesan = [];
                    while ($msgRow = $resPesan->fetch_assoc()) {
                        $daftarPesan[] = $msgRow['isi_pesan'];
                    }
                    $stmt->close();

                    // Kembalikan array jika ada isinya
                    if (count($daftarPesan) > 0) {
                        return $daftarPesan;
                    }
                }
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error("AutoReply Error: " . $e->getMessage());
            }
            return null;
        }

        return null;
    }
}