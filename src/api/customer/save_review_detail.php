<?php
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../auth/middleware_login.php");


ini_set('display_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');


set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan internal pada server.',
        'error_detail' => $exception->getMessage() 
    ]);
    exit;
});



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode(['success' => false, 'message' => 'Metode yang diizinkan hanya POST']);
    exit;
}

try {
    
    $verif = authenticate_request();
    $id_admin = $verif->id ?? $verif->kode ?? null;


    
    $data = json_decode(file_get_contents('php://input'), true);
    $review_id = $data['review_id'] ?? null;
    $status = $data['status'] ?? null;
    $prioritas = $data['prioritas'] ?? null;
    $kategori_masalah = $data['kategori_masalah'] ?? null;
    $deskripsi_penanganan = $data['deskripsi_penanganan'] ?? null;
    
    if (empty($review_id) || empty($status) || empty($prioritas) || empty($kategori_masalah) || empty($deskripsi_penanganan)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
        exit;
    }

    
    $conn->begin_transaction();

    
    $checkSql = "SELECT id FROM review_detail WHERE review_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) throw new Exception("Prepare Check SQL Gagal: " . $conn->error);
    
    $checkStmt->bind_param("s", $review_id);
    if (!$checkStmt->execute()) throw new Exception("Execute Check SQL Gagal: " . $checkStmt->error);
    
    $checkResult = $checkStmt->get_result();
    
    $stmt = null; 

    if ($checkResult->num_rows > 0) {
        
        $sql = "UPDATE review_detail SET status = ?, prioritas = ?, kategori_masalah = ?, deskripsi_penanganan = ?, id_admin = ? WHERE review_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare UPDATE Gagal: " . $conn->error);
        
        
        $stmt->bind_param("ssssss", $status, $prioritas, $kategori_masalah, $deskripsi_penanganan, $id_admin, $review_id);
    } else {
        
        $sql = "INSERT INTO review_detail (review_id, id_admin, status, prioritas, kategori_masalah, deskripsi_penanganan) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare INSERT Gagal: " . $conn->error);
        
        
        $stmt->bind_param("ssssss", $review_id, $id_admin, $status, $prioritas, $kategori_masalah, $deskripsi_penanganan);
    }
    
    
    if (!$stmt->execute()) {
        throw new Exception("Eksekusi Gagal: " . $stmt->error);
    }

    
    $updateReviewSql = "UPDATE review SET sudah_terpecahkan = 1 WHERE id = ?";
    $updateStmt = $conn->prepare($updateReviewSql);
    if (!$updateStmt) throw new Exception("Prepare Update Review Flag Gagal: " . $conn->error);
    
    $updateStmt->bind_param("s", $review_id);
    if (!$updateStmt->execute()) throw new Exception("Execute Update Review Flag Gagal: " . $updateStmt->error);
    
    
    $conn->commit();
    
    http_response_code(201); 
    echo json_encode(['success' => true, 'message' => 'Data penanganan berhasil disimpan']);

} catch (Exception $e) {
    if ($conn->ping()) { 
        $conn->rollback();
    }
    
    throw $e;
} finally {
    
    if (isset($checkStmt)) $checkStmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($conn)) $conn->close();
}