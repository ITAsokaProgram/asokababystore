<?php
// API untuk menampilkan daftar file log di folder logs
header('Content-Type: application/json; charset=utf-8');

$logsDir = realpath(__DIR__ . '/../../../logs');
if (!$logsDir) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Logs folder not found.']);
    exit;
}

$files = array_values(array_filter(scandir($logsDir), function($f) use ($logsDir) {
    return is_file($logsDir . DIRECTORY_SEPARATOR . $f) && preg_match('/\.log$/i', $f);
}));

$fileList = array_map(function($f) use ($logsDir) {
    return [
        'name' => $f,
        'size' => filesize($logsDir . DIRECTORY_SEPARATOR . $f),
        'mtime' => date('Y-m-d H:i:s', filemtime($logsDir . DIRECTORY_SEPARATOR . $f)),
    ];
}, $files);

echo json_encode(['status' => true, 'files' => $fileList]);
