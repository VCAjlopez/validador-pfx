<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metodo no permitido']);
    exit;
}

if (!isset($_POST['password']) || !isset($_POST['filedata'])) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan parametros']);
    exit;
}

$password = $_POST['password'];
$filedata = base64_decode($_POST['filedata']);

$tempFile = __DIR__ . '/tmp_' . uniqid() . '.pfx';
file_put_contents($tempFile, $filedata);

$certs = [];
if (openssl_pkcs12_read(file_get_contents($tempFile), $certs, $password)) {
    echo json_encode(['status' => 'valid']);
} else {
    echo json_encode(['status' => 'invalid']);
}

unlink($tempFile);
?>
