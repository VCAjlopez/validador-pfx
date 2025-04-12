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
