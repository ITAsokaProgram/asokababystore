<?php
session_start();
include '../../../aa_kon_sett.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $nama = trim($input['nama'] ?? '');
    $email = trim($input['email'] ?? '');
    $no_telpon = trim($input['no_telpon'] ?? '');
    $wilayah = trim($input['wilayah'] ?? '');
    $password = $input['password'] ?? '';

    // Validasi Dasar
    if (empty($nama) || empty($password)) {
        throw new Exception("Nama dan Password wajib diisi.");
    }

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

    // Query Insert
    $sql = "INSERT INTO user_supplier (nama, email, no_telpon, wilayah, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

    $stmt->bind_param("sssss", $nama, $email, $no_telpon, $wilayah, $hashed_password);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Supplier berhasil ditambahkan.']);
    } else {
        throw new Exception("Gagal menyimpan: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>