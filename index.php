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

if (!$accion || ($accion !== 'comparar-key-cer' && !$archivo_b64)) {
    echo json_encode(["estatus" => "error", "mensaje" => "Faltan parametros"]);
    exit;
}

$contenido = $archivo_b64 ? base64_decode($archivo_b64) : null;
if ($archivo_b64 && $contenido === false) {
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
            $pem = '';
            if (openssl_pkey_export($key, $pem, null)) {
                echo json_encode([
                    "estatus" => "valido",
                    "mensaje" => "Llave privada valida",
                    "key_pkcs8_pem" => $pem
                ]);
            } else {
                echo json_encode([
                    "estatus" => "valido",
                    "mensaje" => "Llave privada valida, pero no se pudo exportar en formato PEM"
                ]);
            }
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

    case 'comparar-key-cer':
        $key_b64 = $_POST['key_b64'] ?? null;
        $cer_b64 = $_POST['cer_b64'] ?? null;

        if (!$key_b64 || !$cer_b64) {
            echo json_encode(["estatus" => "error", "mensaje" => "Faltan archivos key_b64 o cer_b64"]);
            exit;
        }

        $key_bin = base64_decode($key_b64);
        $cer_bin = base64_decode($cer_b64);

        $key = openssl_pkey_get_private($key_bin, $pass);
        $cert = openssl_x509_read($cer_bin);

        if (!$key || !$cert) {
            echo json_encode(["estatus" => "error", "mensaje" => "No se pudo leer alguno de los archivos"]);
            exit;
        }

        $key_details = openssl_pkey_get_details($key);
        $cert_pubkey = openssl_get_publickey($cert);
        $cert_pubkey_details = openssl_pkey_get_details($cert_pubkey);

        if (!$key_details || !$cert_pubkey_details) {
            echo json_encode(["estatus" => "error", "mensaje" => "No se pudieron obtener los modulus"]);
            exit;
        }

        if ($key_details['rsa']['n'] === $cert_pubkey_details['rsa']['n']) {
            echo json_encode([
                "estatus" => "coinciden",
                "mensaje" => "La llave privada y el certificado corresponden al mismo par"
            ]);
        } else {
            echo json_encode([
                "estatus" => "diferente",
                "mensaje" => "La llave privada y el certificado no corresponden"
            ]);
        }
        break;

    default:
        echo json_encode(["estatus" => "error", "mensaje" => "Accion no reconocida"]);
        break;
}
