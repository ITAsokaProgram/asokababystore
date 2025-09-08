<?php
// Load all utility classes
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/DataValidator.php';
require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/FileHelper.php';
require_once __DIR__ . '/MenuSync.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Global helper functions
function createLogger($logFileName = 'app.log') {
    return new AppLogger($logFileName);
}

function createDatabaseHelper($connection, $logger = null) {
    return new DatabaseHelper($connection, $logger);
}

function createFileHelper($logger = null) {
    return new FileHelper($logger);
}

function createMenuSync($connection) {
    return new MenuSync($connection);
}

// Error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $logger = new AppLogger('error.log');
    $logger->error("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return false;
}

// Set custom error handler
set_error_handler('customErrorHandler');

// Exception handler
function customExceptionHandler($exception) {
    $logger = new AppLogger('error.log');
    $logger->critical("Uncaught Exception: " . $exception->getMessage());
    $logger->critical("Stack trace: " . $exception->getTraceAsString());
}

// Set custom exception handler
set_exception_handler('customExceptionHandler');
?> 