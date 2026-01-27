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

    // --- CEK DUPLIKAT EMAIL ---
    if (!empty($email)) {
        $check_sql = "SELECT kode FROM user_supplier WHERE email = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $stmt_check->close();
            throw new Exception("Email '$email' sudah terdaftar! Gunakan email lain.");
        }
        $stmt_check->close();
    }
    // --------------------------

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

    // Query Insert (Asumsi ada kolom created_at yang default CURRENT_TIMESTAMP, jadi tidak perlu di-insert manual)
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