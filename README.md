# 🔐 Validador de Certificados Digitales (.pfx y .cer)

Este proyecto es una API en PHP que permite validar archivos de certificados digitales `.pfx` y `.cer` enviados como base64, útil para integraciones con FileMaker, WebDirect y clientes web.

---

## 🚀 Funcionalidad disponible

| Acción (`accion`)   | Archivo requerido  | Contraseña | Resultado esperado                           |
|---------------------|--------------------|------------|----------------------------------------------|
| `validar-pfx`       | `.pfx` / `.p12`    | ✅         | Número de certificado, vigencia              |
| `leer-cer`          | `.cer`             | ❌         | Número de certificado, vigencia              |

---

## 📥 Parámetros (POST)

Enviar como `application/x-www-form-urlencoded`:

- `accion`: `"validar-pfx"` o `"leer-cer"`
- `archivo_b64`: Contenido del archivo en base64
- `pass`: (solo para `validar-pfx`)

---

## 📤 Ejemplo de respuesta JSON

```json
{
  "estatus": "valido",
  "numero_certificado": "00001000000706831990",
  "vigencia_inicio": "2024-04-26 00:22:31",
  "vigencia_fin": "2028-04-26 00:22:31"
}
