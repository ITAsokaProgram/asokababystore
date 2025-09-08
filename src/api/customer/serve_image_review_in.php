<?php
$path = $_GET['path'] ?? '';
$real_path = realpath($path);

$base_dir = '/var/www/SvrvFT/review_pubs/';

// Cegah akses file di luar folder yang diizinkan
if ($real_path && file_exists($real_path) && strpos($real_path, $base_dir) === 0) {
    header("Content-Type: " . mime_content_type($real_path));
    readfile($real_path);
    exit;
} else {
    http_response_code(404);
    echo "File not found";
}
