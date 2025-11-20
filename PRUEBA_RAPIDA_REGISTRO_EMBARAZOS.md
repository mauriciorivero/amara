# 🚀 Prueba Rápida - Registro de Embarazos

## ✅ Implementación Completada

Se ha agregado el **formulario de registro de embarazos** desde la vista de detalles de madre.

---

## 🧪 Prueba en 5 Pasos

### 1️⃣ Abrir la aplicación
```
http://localhost:8888/amara/index.html
```

### 2️⃣ Ir a detalles de madre
- Click en "Nuestras Madres"
- Click en "Ver detalles" (👁️) de cualquier madre

### 3️⃣ Buscar sección de embarazos
- Scroll hacia abajo hasta ver:
```
🤰 Historial de Embarazos    [+ Registrar Embarazo]
```

### 4️⃣ Registrar embarazo
- Click en el botón **"+ Registrar Embarazo"**
- Se abre un modal morado con el formulario

### 5️⃣ Completar y guardar
- Dejar valor por defecto: **1 bebé**
- Click en **"💾 Guardar Embarazo"**
- ✅ Debe aparecer el nuevo embarazo en la lista

---

## 📸 Lo que verás

### Modal de Registro:
```
┌─────────────────────────────────────────┐
│  🤰 Registrar Nuevo Embarazo            │
│  Complete la información...              │
│                                          │
│  🤰 ¿Cuántos bebés espera? *            │
│  [  1  ] ← Ajusta aquí                  │
│                                          │
│  ☐ 👥 Embarazo múltiple                 │
│                                          │
│  ▶ Campos adicionales (opcional)        │
│                                          │
│  ℹ️ Los contadores se actualizarán...   │
│                                          │
│  [ Cancelar ]  [ 💾 Guardar Embarazo ]  │
└─────────────────────────────────────────┘
```

### Después de Guardar:
```
🤰 Historial de Embarazos
┌──────────────┐
│ Embarazo #1  │  ← ¡NUEVO!
│ [En Curso]   │
│ 👶 0 Nacidos │
│ 🤰 1 Por     │
│    Nacer     │
│ [Ver Bebés]  │
└──────────────┘
```

---

## 🎯 Pruebas Adicionales

### Embarazo Múltiple
1. Click "+ Registrar Embarazo"
2. Cambiar a: **2 bebés**
3. Marcar ✅ "Embarazo múltiple"
4. Guardar
✅ Debe aparecer con etiqueta "👥 Múltiple"

### Validación Inteligente
1. Click "+ Registrar Embarazo"
2. Cambiar a: **3 bebés**
3. **NO** marcar el checkbox
4. Guardar
✅ Pregunta si desea marcarlo como múltiple

### Campos Opcionales
1. Click "+ Registrar Embarazo"
2. Click en **"▶ Campos adicionales"**
3. Se expanden más campos
4. Completar si deseas
5. Guardar
✅ Se guardan todos los valores

---

## 🔍 Qué Revisar

### ✅ Modal
- [ ] Se abre al click en "+ Registrar Embarazo"
- [ ] Tiene diseño morado profesional
- [ ] Campos con iconos (🤰, 👥, 👶)
- [ ] Cierra con X, Cancelar o click fuera

### ✅ Formulario
- [ ] Input de bebés esperados (1-5)
- [ ] Checkbox de embarazo múltiple
- [ ] Botón para campos opcionales
- [ ] Mensaje informativo azul

### ✅ Guardado
- [ ] Envía datos a la API
- [ ] Muestra mensaje de éxito
- [ ] Cierra el modal
- [ ] Aparece nuevo embarazo en la lista

### ✅ Vista de Embarazo Nuevo
- [ ] Tarjeta con "En Curso"
- [ ] Contadores correctos
- [ ] Icono 🤰 
- [ ] Botón "Ver Bebés"

---

## 🎨 Características Visuales

### Diseño
- 🟣 **Color morado** para embarazos
- 🎨 **Gradientes** en botones
- 🖼️ **Iconos** en cada campo
- 📱 **Responsive** (funciona en móvil)

### Interacciones
- ✨ **Animación** al abrir modal
- 🔄 **Toggle** de campos opcionales
- ✅ **Checkbox** personalizado grande
- 💡 **Hints** bajo cada campo

---

## 📝 Datos de Prueba

### Ejemplo 1: Embarazo Simple
```
Bebés esperados: 1
Embarazo múltiple: No
```

### Ejemplo 2: Mellizos
```
Bebés esperados: 2
Embarazo múltiple: Sí ✓
```

### Ejemplo 3: Trillizos
```
Bebés esperados: 3
Embarazo múltiple: Sí ✓
```

---

## ❌ Si Algo No Funciona

### Modal no abre
1. Abrir consola (F12)
2. Verificar errores JavaScript
3. Refrescar página (Ctrl+R)

### No guarda
1. Verificar que MAMP esté corriendo
2. Verificar MySQL activo
3. Revisar consola por errores de API

### No aparece en lista
1. Refrescar página completa
2. Verificar en consola Network tab
3. Revisar respuesta de la API

---

## 📊 API Endpoint

**POST** `api/embarazos/guardar.php`

**Body enviado:**
```json
{
  "madreId": 149,
  "totalBebesNacidos": 0,
  "totalBebesPorNacer": 1,
  "bebesNoNacidos": 0,
  "bebesFallecidos": 0,
  "esMultiple": false
}
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Embarazo registrado exitosamente",
  "id": 66
}
```

---

## 🎓 Siguiente Paso

Después de probar esto, el siguiente paso será:
- ✨ Implementar formulario de **registro de bebés**
- 📝 Crear vista independiente de embarazos
- 🔄 Sincronización automática de contadores

---

## ✅ Checklist de Prueba

- [ ] Modal abre correctamente
- [ ] Formulario tiene todos los campos
- [ ] Validación funciona (más de 1 bebé)
- [ ] Se guarda en la API
- [ ] Aparece en la lista de embarazos
- [ ] Se puede cerrar el modal
- [ ] Campos opcionales se expanden
- [ ] Diseño se ve bien
- [ ] Funciona en móvil (si tienes dispositivo)
- [ ] Mensajes de éxito/error aparecen

---

**¡Listo para probar!** 🚀

Cualquier problema, revisa:
- Consola del navegador (F12)
- Network tab para ver llamadas API
- Documentación completa en `FUNCIONALIDAD_REGISTRO_EMBARAZOS.md`

