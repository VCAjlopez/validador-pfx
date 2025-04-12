# 🔐 Validador de Certificados Digitales (.pfx, .key, .cer)

Este proyecto es una API en PHP que permite validar archivos de certificados digitales usando un enfoque flexible por tipo de archivo. Está diseñado para funcionar especialmente con clientes como FileMaker Pro / WebDirect, que no pueden enviar archivos directamente desde el sistema de archivos, por lo que se usa **base64**.

---

## 🚀 Funcionalidad

Este validador permite:

| Acción (`accion`)   | Archivo necesario       | Contraseña | Resultado esperado                          |
|---------------------|-------------------------|------------|---------------------------------------------|
| `validar-pfx`       | `.pfx` codificado en b64 | ✅         | Número de certificado, vigencia             |
| `validar-key`       | `.key` codificado en b64 | ✅         | Validez de la llave privada                 |
| `leer-cer`          | `.cer` codificado en b64 | ❌         | Número de certificado y vigencia            |

---

## 📥 Cómo usar

### 🔐 Entradas (POST)

Enviar los siguientes campos como `application/x-www-form-urlencoded`:

| Campo           | Tipo     | Requerido | Descripción                                  |
|------------------|----------|-----------|----------------------------------------------|
| `accion`         | string   | ✅        | `validar-pfx`, `validar-key` o `leer-cer`     |
| `archivo_b64`    | string   | ✅        | Archivo codificado en base64                  |
| `nombre_archivo` | string   | ✅        | Nombre original del archivo (ej. cert.pfx)    |
| `pass`           | string   | ❌        | Contraseña del archivo (si aplica)            |

---

## 📤 Respuestas (JSON)

### ✔️ Ejemplo exitoso:
```json
{
  "estatus": "válido",
  "numero_certificado": "30001000000400002415",
  "vigencia_inicio": "2022-03-01 00:00:00",
  "vigencia_fin": "2026-03-01 23:59:59"
}
```

### ❌ Ejemplo con error:
```json
{
  "estatus": "inválido",
  "mensaje": "Llave privada inválida o contraseña incorrecta"
}
```

---

## 🔧 Ejemplo de uso con `curl`

```bash
curl -X POST https://validador-pfx.onrender.com/certix.php \
  -d "accion=validar-pfx" \
  -d "archivo_b64=$(base64 -w 0 certificado.pfx)" \
  -d "nombre_archivo=certificado.pfx" \
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
accion=leer-cer&
archivo_b64=[Base64 del archivo .cer]&
nombre_archivo=archivo.cer
```

Puedes generar la base64 desde un contenedor con plugin o script.

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