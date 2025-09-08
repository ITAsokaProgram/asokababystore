    <?php

    include '../../aa_kon_sett.php';
    header("Content-Type:application/json");
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $data = json_decode(file_get_contents('php://input'), true);

    $noHp = trim($data['hp']);
    $name = trim($data['name']);
    $email = trim($data['email']);
    $judul = trim($data['subject']);
    $pesan = trim($data['message']);
    $date = new DateTime();
    $status = "open";

    if (!$noHp || !$name || !$email || !$judul || !$pesan) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'Semua isi formulir wajib di isi']);
        exit;
    }

    $validNohp = preg_match('/^[0-9]{10,13}$/', $noHp);
    $validName = preg_match('/^[a-zA-Z\s\.\-]+$/', $name);
    $validJudul = preg_match('/^[a-zA-Z\s]+$/', $name);
    $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL);


    $errors = [];

    // Validasi nama
    if (!$validName) {
        $errors[] = "Nama hanya boleh huruf, spasi, titik, dan strip";
    }

    // Validasi no HP
    if (!$validNohp) {
        $errors[] = "Nomor HP harus berupa angka 10–13 digit";
    }

    // Validasi email
    if (!$validEmail) {
        $errors[] = "Email tidak valid";
    }

    // Validasi judul
    if (!$validJudul) {
        $errors[] = "Judul hanya boleh huruf dan spasi";
    }

    // Jika ada error
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'Validasi gagal',
            'errors' => $errors
        ]);
        exit;
    }
    // Mengatur locale untuk bahasa Indonesia
    setlocale(LC_TIME, 'id_ID.utf8'); // Untuk menggunakan format Indonesia

    // Menampilkan hari dengan format lengkap (misalnya: "Senin, 02 Mei 2025")
    $formattedDate = $date->format('Y-m-d H:i:s'); // Simpan dalam format datetime
    $sql = $conn->prepare("INSERT INTO contact_us (no_hp,nama_lengkap,email,subject,message,dikirim,status) VALUES(?,?,?,?,?,?,?)");
    $sql->bind_param('sssssss', $noHp, $name, $email, $judul, $pesan, $formattedDate, $status);
    $sql->execute();
    echo json_encode(['status' => "Berhasil", "message" => "Berhasil mengirim pesan"]);
    $sql->close();
    $conn->close();
