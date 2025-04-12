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
        $certs = [];
        if (!openssl_pkcs12_read($contenido, $certs, $pass)) {
            echo json_encode(["estatus" => "invalido", "mensaje" => "Contrasena incorrecta o archivo .pfx/.p12 invalido"]);
            exit;
        }
        $certData = openssl_x509_parse($certs['cert']);
        echo json_encode([
            "estatus" => "valido",
            "numero_certificado" => $certData['serialNumberHex'] ?? '',
            "vigencia_inicio" => formatearFecha($certData['validFrom_time_t']),
            "vigencia_fin" => formatearFecha($certData['validTo_time_t'])
        ]);
        break;

    case 'validar-key':
        $key = openssl_pkey_get_private($contenido, $pass);
        if (!$key) {
            echo json_encode(["estatus" => "invalido", "mensaje" => "Llave privada invalida o contrasena incorrecta"]);
        } else {
            echo json_encode(["estatus" => "valido", "mensaje" => "Llave privada valida"]);
        }
        break;

    case 'leer-cer':
        $cert = openssl_x509_read($contenido);
        if (!$cert) {
            echo json_encode(["estatus" => "invalido", "mensaje" => "Certificado .cer no valido"]);
            exit;
        }
        $certData = openssl_x509_parse($cert);
        echo json_encode([
            "estatus" => "valido",
            "numero_certificado" => $certData['serialNumberHex'] ?? '',
            "vigencia_inicio" => formatearFecha($certData['validFrom_time_t']),
            "vigencia_fin" => formatearFecha($certData['validTo_time_t'])
        ]);
        break;

    default:
        echo json_encode(["estatus" => "error", "mensaje" => "Accion no reconocida"]);
        break;
}
