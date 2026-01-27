<?php
session_start();
include '../../../aa_kon_sett.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $kode = $input['kode'] ?? null;
    $nama = trim($input['nama'] ?? '');
    $email = trim($input['email'] ?? '');
    $no_telpon = trim($input['no_telpon'] ?? '');
    $wilayah = trim($input['wilayah'] ?? '');
    $password = $input['password'] ?? '';

    if (!$kode || empty($nama)) {
        throw new Exception("ID dan Nama wajib diisi.");
    }

    // --- CEK DUPLIKAT EMAIL (Kecuali Punya Sendiri) ---
    if (!empty($email)) {
        // Cari email yang sama TAPI kodenya BUKAN kode user yang sedang diedit
        $check_sql = "SELECT kode FROM user_supplier WHERE email = ? AND kode != ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("si", $email, $kode);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $stmt_check->close();
            throw new Exception("Email '$email' sudah digunakan oleh supplier lain.");
        }
        $stmt_check->close();
    }
    // --------------------------------------------------

    // Cek apakah password diisi
    if (!empty($password)) {
        // Update dengan password baru
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
        $sql = "UPDATE user_supplier SET nama=?, email=?, no_telpon=?, wilayah=?, password=? WHERE kode=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $nama, $email, $no_telpon, $wilayah, $hashed_password, $kode);
    } else {
        // Update tanpa password
        $sql = "UPDATE user_supplier SET nama=?, email=?, no_telpon=?, wilayah=? WHERE kode=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nama, $email, $no_telpon, $wilayah, $kode);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui.']);
    } else {
        throw new Exception("Gagal update: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>