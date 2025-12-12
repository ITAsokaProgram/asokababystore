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
            $sql = "SELECT id, kata_kunci FROM wa_balasan_otomatis_kamus WHERE status_aktif = '1'";
            $result = $this->conn->query($sql);
            if ($result && $result->num_rows > 0) {
                $isiPesanLower = strtolower(trim($isiPesanUser));
                $foundId = null;
                while ($row = $result->fetch_assoc()) {
                    $kataKunciDb = strtolower($row['kata_kunci']);
                    if (strpos($isiPesanLower, $kataKunciDb) !== false) {
                        $foundId = $row['id'];
                        break;
                    }
                }
                if ($foundId) {
                    $stmt = $this->conn->prepare("SELECT jenis_pesan, isi_pesan FROM wa_balasan_otomatis_pesan WHERE kamus_id = ? ORDER BY urutan ASC");
                    if (!$stmt) {
                        if ($this->logger)
                            $this->logger->error("AutoReply DB Error: " . $this->conn->error);
                        return null;
                    }
                    $stmt->bind_param("i", $foundId);
                    $stmt->execute();
                    $resPesan = $stmt->get_result();
                    $daftarPesan = [];
                    while ($msgRow = $resPesan->fetch_assoc()) {
                        $content = $msgRow['isi_pesan'];
                        if ($msgRow['jenis_pesan'] !== 'text') {
                            $decoded = json_decode($content, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $content = $decoded;
                            }
                        }
                        $daftarPesan[] = [
                            'type' => $msgRow['jenis_pesan'],
                            'content' => $content
                        ];
                    }
                    $stmt->close();
                    if (count($daftarPesan) > 0) {
                        return $daftarPesan;
                    }
                }
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error("AutoReply Exception: " . $e->getMessage());
            }
            return null;
        }
        return null;
    }
}