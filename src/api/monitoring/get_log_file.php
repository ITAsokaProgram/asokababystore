<?php
// Simple API to read a log file from the logs folder
header('Content-Type: text/plain; charset=utf-8');

$logsDir = realpath(__DIR__ . '/../../../logs');
if (!$logsDir) {
    http_response_code(500);
    echo 'Logs folder not found.';
    exit;
}

if (!isset($_GET['filename'])) {
    http_response_code(400);
    echo 'Missing filename parameter.';
    exit;
}

$filename = basename($_GET['filename']); // Prevent path traversal
$logPath = $logsDir . DIRECTORY_SEPARATOR . $filename;

if (!file_exists($logPath)) {
    http_response_code(404);
    echo 'Log file not found.';
    exit;
}

// Optional: limit file size to avoid huge responses
$maxSize = 2 * 1024 * 1024; // 2MB
if (filesize($logPath) > $maxSize) {
    http_response_code(413);
    echo 'Log file too large.';
    exit;
}

readfile($logPath);
