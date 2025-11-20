# Implementación del Módulo de Bebés

## 📋 Resumen

Se ha implementado completamente el sistema de gestión de bebés para el proyecto Amara, incluyendo la capa de datos (DAO), la API REST y la interfaz visual integrada en la vista de detalles de madres.

---

## 🗂️ Archivos Creados

### 1. **Capa de Datos (DAO)**

#### `dao/BebeDAO.php`
- ✅ Clase completa con patrón DAO
- ✅ Métodos implementados:
  - `getAll()` - Obtener todos los bebés con paginación
  - `getByMadreId()` - Obtener bebés por madre (usado en la vista)
  - `getByEmbarazoId()` - Obtener bebés por embarazo
  - `getById()` - Obtener un bebé específico
  - `create()` - Crear nuevo bebé
  - `update()` - Actualizar bebé existente
  - `delete()` - Eliminar bebé
  - `countAll()` - Contar total de bebés
  - `countByMadreId()` - Contar bebés por madre
  - `getEstadisticasPorEstado()` - Obtener estadísticas por estado

### 2. **API REST (Endpoints)**

#### `api/bebes/listar_por_madre.php`
- **Método**: GET
- **Parámetro**: `madre_id` (requerido)
- **Respuesta**: Lista de bebés de una madre específica
- **Uso**: Vista de detalles de madre

#### `api/bebes/listar.php`
- **Método**: GET
- **Parámetros**: `page`, `limit`
- **Respuesta**: Lista de todos los bebés con paginación

#### `api/bebes/obtener.php`
- **Método**: GET
- **Parámetro**: `id` (requerido)
- **Respuesta**: Datos de un bebé específico

#### `api/bebes/guardar.php`
- **Método**: POST
- **Body**: JSON con datos del bebé
- **Respuesta**: Confirmación de creación/actualización

#### `api/bebes/eliminar.php`
- **Método**: DELETE
- **Parámetro**: `id` (requerido)
- **Respuesta**: Confirmación de eliminación

### 3. **Modelo de Datos**

#### Actualización de `model/Bebe.php`
- ✅ Implementación de `JsonSerializable`
- ✅ Método `jsonSerialize()` para serialización automática

#### Actualización de `model/Embarazo.php`
- ✅ Implementación de `JsonSerializable`
- ✅ Método `jsonSerialize()` para serialización automática

### 4. **Interfaz Visual**

#### Actualización de `js/visualBehavior.js`
- ✅ `loadBebesByMadre(madreId)` - Cargar bebés de una madre
- ✅ `renderBebesList(bebes)` - Renderizar tarjetas de bebés
- ✅ Funciones auxiliares:
  - `getEstadoClass()` - Clase CSS según estado del bebé
  - `getSexoText()` - Texto legible para sexo
  - `formatDate()` - Formatear fechas
- ✅ Placeholders para funciones futuras:
  - `agregarBebe()` - Registrar nuevo bebé
  - `editarBebe()` - Editar bebé existente
  - `eliminarBebe()` - Eliminar bebé

#### Actualización de `css/styles.css`
- ✅ Sección completa de estilos para bebés
- ✅ Componentes estilizados:
  - `.bebes-header` - Cabecera de sección
  - `.btn-add-bebe` - Botón para agregar bebé
  - `.bebes-grid` - Grid responsivo
  - `.bebe-card` - Tarjetas de bebé
  - `.bebe-estado` - Estados visuales con colores
  - `.tag-mellizo` - Etiqueta para mellizos
- ✅ Diseño responsive para móviles

---

## 🎨 Características Visuales

### Estados de Bebés
Los bebés se muestran con diferentes colores según su estado:

- 🟢 **Nacido**: Verde (background: #d1fae5)
- 🔵 **Por nacer**: Azul (background: #dbeafe)
- 🔴 **Muerte gestacional**: Rojo (background: #fee2e2)
- 🟡 **Aborto**: Amarillo (background: #fef3c7)
- ⚫ **Fallecido**: Gris (background: #f3f4f6)

### Iconografía
- 👦 Masculino
- 👧 Femenino
- 👶 No especificado
- 👯 Mellizos (etiqueta especial)

---

## 🧪 Cómo Probar

### 1. **Verificar Base de Datos**
Asegúrate de que el servidor MySQL esté corriendo y la base de datos `amara` esté activa.

### 2. **Iniciar MAMP**
```bash
# Inicia MAMP y verifica que Apache y MySQL estén activos
```

### 3. **Abrir la Aplicación**
```
http://localhost:8888/amara/index.html
```

### 4. **Probar el Módulo de Bebés**

#### Paso 1: Abrir Gestión de Madres
- Click en la tarjeta "Nuestras Madres" en el dashboard
- Se abrirá el listado de madres

#### Paso 2: Ver Detalles de una Madre
- Click en el ícono de "Ver detalles" (👁️) de cualquier madre
- Ejemplo: Click en "Luz Marina Mosquera" (ID: 149)

#### Paso 3: Visualizar Bebés
- En la vista de detalles, desplázate hacia abajo
- Verás la sección "👶 Bebés Registrados"
- Los bebés se cargan automáticamente desde la API

#### Paso 4: Datos de Ejemplo
Según la base de datos, hay 48 bebés registrados. Ejemplos:
- **Madre ID 1**: Emmanuel Lopez Mercado (Nacido - 2022-04-10)
- **Madre ID 20**: Evelyn Andrea Imbet y Eliam Andres Imbet (Mellizos - Nacidos)
- **Madre ID 149**: Bebé de Luz Mosquera (Nacido - 2025-09-07)

### 5. **Probar API Directamente**

#### Listar bebés de una madre:
```
http://localhost:8888/amara/api/bebes/listar_por_madre.php?madre_id=149
```

#### Obtener un bebé específico:
```
http://localhost:8888/amara/api/bebes/obtener.php?id=48
```

#### Listar todos los bebés:
```
http://localhost:8888/amara/api/bebes/listar.php?page=1&limit=10
```

---

## 📊 Estructura de Datos

### Objeto Bebé (JSON)
```json
{
  "id": 48,
  "embarazoId": 65,
  "madreId": 149,
  "nombre": "Bebé de Luz Mosquera",
  "sexo": "M",
  "fechaNacimiento": "2025-09-07",
  "esMellizo": false,
  "estado": "Nacido",
  "fechaIncidente": null,
  "observaciones": null,
  "hasNacido": true,
  "createdAt": "2025-09-10 23:13:31",
  "updatedAt": "2025-09-10 23:13:31"
}
```

### Estados Posibles
- `"Por nacer"` - Bebé aún no ha nacido
- `"Nacido"` - Bebé nacido exitosamente
- `"Muerte gestacional"` - Fallecimiento durante gestación
- `"Aborto"` - Interrupción del embarazo
- `"Fallecido"` - Fallecimiento posterior al nacimiento

---

## 🔄 Flujo de Datos

```
Usuario → Click en Ver Detalles de Madre
    ↓
renderMadreDetail(madre)
    ↓
loadBebesByMadre(madreId)
    ↓
Fetch: api/bebes/listar_por_madre.php?madre_id=X
    ↓
BebeDAO::getByMadreId(madreId)
    ↓
Query SQL con JOIN (madres, embarazos, bebes)
    ↓
Mapeo a objetos Bebe
    ↓
JSON Response con array de bebés
    ↓
renderBebesList(bebes)
    ↓
Renderiza tarjetas HTML con estilos CSS
```

---

## ✅ Funcionalidades Implementadas

- [x] Modelo de datos `Bebe.php` con serialización JSON
- [x] DAO completo `BebeDAO.php` con todos los métodos CRUD
- [x] 5 endpoints de API REST
- [x] Integración visual en vista de detalles de madre
- [x] Diseño de tarjetas responsivo
- [x] Estados visuales con colores diferenciados
- [x] Identificación de mellizos
- [x] Formateo de fechas en español
- [x] Iconografía según sexo del bebé

---

## 🚀 Próximas Funcionalidades (Pendientes)

Las siguientes funciones están preparadas como placeholders:

### 1. Registrar Nuevo Bebé
- Implementar formulario modal
- Conectar con `api/bebes/guardar.php`
- Validaciones de campos

### 2. Editar Bebé
- Cargar datos del bebé en formulario
- Actualizar mediante API
- Refrescar vista automáticamente

### 3. Eliminar Bebé
- Confirmación de eliminación
- Llamada a `api/bebes/eliminar.php`
- Actualizar listado tras eliminación

### 4. Gestión de Embarazos
- Crear DAO y API para embarazos
- Relacionar bebés con embarazos
- Vista de historial de embarazos

---

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 8.3 (POO, PDO, Prepared Statements)
- **Base de Datos**: MySQL 8.0
- **Frontend**: JavaScript Vanilla, HTML5, CSS3
- **Patrón**: DAO (Data Access Object)
- **API**: REST con respuestas JSON
- **Diseño**: CSS Grid, Flexbox, Variables CSS

---

## 📝 Notas Técnicas

1. **Seguridad**: Se usan prepared statements en todas las consultas SQL
2. **Performance**: Consultas optimizadas con JOINs y índices
3. **Escalabilidad**: Paginación implementada en endpoints
4. **Mantenibilidad**: Código modular y bien documentado
5. **UX**: Diseño intuitivo con feedback visual inmediato

---

## 🐛 Debugging

### Si los bebés no se cargan:

1. **Verificar la consola del navegador** (F12 → Console)
   - Debe mostrar la respuesta de la API

2. **Verificar la conexión a la base de datos**
   - Revisar `config/env.php`
   - Verificar credenciales MySQL

3. **Verificar que existan bebés en la BD**
   ```sql
   SELECT * FROM bebes WHERE madre_id = 149;
   ```

4. **Verificar permisos de archivos**
   ```bash
   chmod 755 api/bebes/
   chmod 644 api/bebes/*.php
   ```

### Logs útiles:
- Errores PHP: `/Applications/MAMP/logs/php_error.log`
- Errores Apache: `/Applications/MAMP/logs/apache_error.log`

---

## ✨ Créditos

Implementación desarrollada siguiendo los estándares del proyecto Amara:
- Arquitectura consistente con módulos existentes
- Diseño visual coherente con el sistema de diseño
- API REST siguiendo convenciones establecidas

---

**Fecha de implementación**: Noviembre 2024
**Versión**: 1.0.0

