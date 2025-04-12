# 🔐 Validador de Certificados Digitales (.pfx, .p12, .key, .cer)

Este proyecto es una API en PHP que permite validar archivos de certificados digitales usando un enfoque flexible por tipo de archivo. Está diseñado para funcionar especialmente con clientes como FileMaker Pro / WebDirect, que no pueden enviar archivos directamente desde el sistema de archivos, por lo que se usa **base64**.

---

## 🚀 Funcionalidad

Este validador permite:

| Acción (`accion`)   | Archivo necesario       | Contraseña | Resultado esperado                          |
|---------------------|-------------------------|------------|---------------------------------------------|
| `validar-pfx`       | `.pfx` o `.p12` en b64   | ✅         | Número de certificado, vigencia             |
| `validar-key`       | `.key` en b64            | ✅         | Validez de la llave y exportación PEM       |
| `leer-cer`          | `.cer` en b64            | ❌         | Número de certificado y vigencia            |

---

## 📥 Cómo usar

### 🔐 Entradas (POST)

Enviar los siguientes campos como `application/x-www-form-urlencoded`:

| Campo           | Tipo     | Requerido | Descripción                                  |
|------------------|----------|-----------|----------------------------------------------|
| `accion`         | string   | ✅        | `validar-pfx`, `validar-key` o `leer-cer`     |
| `archivo_b64`    | string   | ✅        | Archivo codificado en base64                  |
| `pass`           | string   | ❌        | Contraseña del archivo (si aplica)            |

---

## 📤 Respuestas (JSON)

### ✔️ Ejemplo exitoso `leer-cer` o `validar-pfx`:
```json
{
  "estatus": "valido",
  "numero_certificado": "30001000000400002415",
  "vigencia_inicio": "2022-03-01 00:00:00",
  "vigencia_fin": "2026-03-01 23:59:59"
}
```

### ✔️ Ejemplo exitoso `validar-key`:
```json
{
  "estatus": "valido",
  "mensaje": "Llave privada valida",
  "key_pkcs8_pem": "-----BEGIN PRIVATE KEY-----\nMIIEv...\n-----END PRIVATE KEY-----"
}
```

---

## 🔧 Ejemplo de uso con `curl`

```bash
curl -X POST https://validador-pfx.onrender.com/certix.php \
  -d "accion=validar-key" \
  -d "archivo_b64=$(base64 -w 0 archivo.key)" \
  -d "pass=12345678"
```

---

## 📡 Uso desde FileMaker

FileMaker puede hacer `Insert from URL` con el siguiente contenido:

- URL: `https://validador-pfx.onrender.com/certix.php`
- Método: `POST`
- Cabecera: `Content-Type: application/x-www-form-urlencoded`
- Cuerpo:
```text
accion=validar-key&
archivo_b64=[Base64 del archivo .key]&
pass=12345678
```

---

## 🐳 Docker (opcional)

Este proyecto usa PHP + Apache vía Docker. El `Dockerfile` ya viene configurado.

```bash
docker build -t validador-pfx .
docker run -p 8080:80 validador-pfx
```

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT.

---

## 🔁 Comparación de `.key` y `.cer`

### Acción: `comparar-key-cer`

Permite verificar si una llave privada `.key` y un certificado `.cer` corresponden al mismo par criptográfico.

#### 📥 Entradas:
- `accion=comparar-key-cer`
- `key_b64`: Llave privada codificada en base64
- `cer_b64`: Certificado codificado en base64
- `pass`: Contraseña de la llave (si aplica)

#### 📤 Respuestas:

- Coinciden:
```json
{
  "estatus": "coinciden",
  "mensaje": "La llave privada y el certificado corresponden al mismo par"
}
```

- No coinciden:
```json
{
  "estatus": "diferente",
  "mensaje": "La llave privada y el certificado no corresponden"
}
```

- Error:
```json
{
  "estatus": "error",
  "mensaje": "No se pudo leer alguno de los archivos"
}
```
