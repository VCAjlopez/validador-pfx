<?php

header('Content-Type: application/json');

function formatearFecha($timestamp) {
    return date('Y-m-d H:i:s', $timestamp);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["estatus" => "error", "mensaje" => "Solo se permite POST"]);
    exit;
}

$accion = $_POST['accion'] ?? null;
$pass = $_POST['pass'] ?? '';
$archivo_b64 = $_POST['archivo_b64'] ?? null;

if (!$accion || !$archivo_b64) {
    echo json_encode(["estatus" => "error", "mensaje" => "Faltan parametros"]);
    exit;
}

$contenido = base64_decode($archivo_b64);
if ($contenido === false) {
    echo json_encode(["estatus" => "error", "mensaje" => "El archivo base64 no es valido"]);
    exit;
}

switch ($accion) {
    case 'validar-pfx':
        $tempFile = tempnam(sys_get_temp_dir(), 'pfx_');
        file_put_contents($tempFile, $contenido);

        $certs = [];
        if (!openssl_pkcs12_read(file_get_contents($tempFile), $certs, $pass)) {
            unlink($tempFile);
            echo json_encode(["estatus" => "invalido", "mensaje" => "Contrasena incorrecta o archivo .pfx/.p12 invalido"]);
            exit;
        }

        $certData = openssl_x509_parse($certs['cert']);
        unlink($tempFile);

        echo json_encode([
            "estatus" => "valido",
            "numero_certificado" => isset($certData['serialNumberHex']) ? hex2bin($certData['serialNumberHex']) : '',
            "vigencia_inicio" => formatearFecha($certData['validFrom_time_t']),
            "vigencia_fin" => formatearFecha($certData['validTo_time_t'])
        ]);
        break;

    case 'leer-cer':
        $cert = @openssl_x509_read($contenido);
        if (!$cert) {
            $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($contenido), 64, "\n") . "-----END CERTIFICATE-----\n";
            $cert = @openssl_x509_read($pem);
        }
        if (!$cert) {
            echo json_encode(["estatus" => "invalido", "mensaje" => "Certificado .cer no valido"]);
            exit;
        }
        $certData = openssl_x509_parse($cert);
        echo json_encode([
            "estatus" => "valido",
            "numero_certificado" => isset($certData['serialNumberHex']) ? hex2bin($certData['serialNumberHex']) : '',
            "vigencia_inicio" => formatearFecha($certData['validFrom_time_t']),
            "vigencia_fin" => formatearFecha($certData['validTo_time_t'])
        ]);
        break;

    default:
        echo json_encode(["estatus" => "error", "mensaje" => "Accion no reconocida"]);
        break;
}
