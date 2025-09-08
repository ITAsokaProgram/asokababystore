<?php
include '../../../aa_kon_sett.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$input = json_decode(file_get_contents("php://input"), true);



// Fungsi enkripsi & dekripsi

function decryptData($data)
{
    return openssl_decrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, ENCRYPTION_IV);
}
function maskPhoneNumber($phone)
{
    return substr($phone, 0, 5) . '*****' . substr($phone, -3);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kd_cust = $input['kode-member'];
    // Ambil data customer dan total poin
    $stmt = $conn->prepare("SELECT upd_from_web, kd_cust, nama_cust FROM customers  WHERE kd_cust = ? GROUP BY upd_from_web");

    if ($stmt) {
        $stmt->bind_param('s', $kd_cust);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response = [
                'message' => 'Data berhasil didapatkan',
                'data' => [
                    'customer' => [
                        'kode_member' => $row['kd_cust'],
                        'nama_customer' => $row['nama_cust'],
                        'update_profile' => (bool) $row['upd_from_web'],
                        'member' => "Terdaftar"
                    ],
                ]
            ];
           echo json_encode($response);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Data Customer Tidak ditemukan']);
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Gagal menyiapkan query']);
    }

    $conn->close();
    exit;
}
