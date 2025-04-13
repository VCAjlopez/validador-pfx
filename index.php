<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['estatus' => 'error', 'message' => 'Metodo no permitido']);
    exit;
}

if (!isset($_POST['accion'])) {
    echo json_encode(['estatus' => 'error', 'message' => 'Falta parametro accion']);
    exit;
}

$accion = $_POST['accion'];

if ($accion === 'validar-pfx') {
    if (!isset($_POST['password']) || !isset($_POST['filedata'])) {
        echo json_encode(['estatus' => 'error', 'message' => 'Faltan parametros']);
        exit;
    }

    $password = $_POST['password'];
    $filedata = base64_decode($_POST['filedata']);
    $tempFile = __DIR__ . '/tmp_' . uniqid() . '.pfx';
    file_put_contents($tempFile, $filedata);

    $certs = [];
    if (openssl_pkcs12_read(file_get_contents($tempFile), $certs, $password)) {
        $info = openssl_x509_parse($certs['cert']);
        echo json_encode([
            'estatus' => 'valido',
            'numero_certificado' => $info['serialNumberHex'],
            'vigencia_inicio' => date('Y-m-d H:i:s', $info['validFrom_time_t']),
            'vigencia_fin' => date('Y-m-d H:i:s', $info['validTo_time_t']),
            'subject' => $info['subject'],
            'issuer' => $info['issuer']
        ]);
    } else {
        echo json_encode(['status' => 'invalido']);
    }

    unlink($tempFile);
    exit;
}

if ($accion === 'leer-cer') {
    if (!isset($_POST['filedata'])) {
        echo json_encode(['estatus' => 'error', 'message' => 'Falta archivo cer']);
        exit;
    }

    $contenido = base64_decode($_POST['filedata']);
    $cert = @openssl_x509_read($contenido);
    if (!$cert) {
        $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($contenido), 64, "\n") . "-----END CERTIFICATE-----\n";
        $cert = @openssl_x509_read($pem);
    }

    if (!$cert) {
        echo json_encode(['estatus' => 'invalido']);
        exit;
    }

    $info = openssl_x509_parse($cert);
    echo json_encode([
        'estatus' => 'valido',
        "numero_certificado" => isset($cert['serialNumberHex']) ? hex2bin($cer['serialNumberHex']) : '',
        'vigencia_inicio' => date('Y-m-d H:i:s', $info['validFrom_time_t']),
        'vigencia_final' => date('Y-m-d H:i:s', $info['validTo_time_t']),
        'subject' => $info['subject'],
        'issuer' => $info['issuer']
    ]);
    exit;
}

echo json_encode(['estatus' => 'error', 'message' => 'Accion no reconocida']);
