# Walkthrough - Navegación Dashboard Embarazos y Bebés (Vista Inline)

Se ha implementado la navegación desde la tarjeta "Embarazos y Bebés" del dashboard hacia una nueva vista inline (similar a la vista de Madres), reemplazando el enfoque anterior de modal. Además, se habilitó la edición de embarazos desde esta vista y se agregaron métricas generales en la tarjeta del dashboard.

## Cambios Realizados

### 1. Backend (DAOs y API)
- **`dao/EmbarazoDAO.php`**: Se actualizó `getAll` para incluir datos de la madre y la orientadora.
- **`dao/BebeDAO.php`**: Se actualizó `getAll` para incluir el nombre de la madre.
- **`api/embarazos/listar.php`**: Endpoint para listar embarazos con datos relacionados.
- **`api/bebes/listar.php`**: Actualizado para incluir nombre de la madre.
- **[NUEVO] `api/embarazos/estadisticas.php`**: Endpoint que devuelve el conteo total de embarazos y bebés nacidos.

### 2. Frontend (HTML/CSS)
- **`index.html`**: 
    - Se eliminó el modal `#embarazosBebesModal`.
    - Se añadió `#embarazos-bebes-screen` con la clase `.module-screen` dentro de la sección principal.
    - Se implementó una estructura de vista dual: Lista (`#embarazos-bebes-list-view`) y Detalle (`#embarazos-bebes-detail-view`).
    - **[NUEVO]** Se actualizaron los elementos de la tarjeta "Embarazos y Bebés" para mostrar contadores.
- **`css/styles.css`**: 
    - Se creó la clase genérica `.module-screen` para estandarizar las vistas de módulos.
    - Se añadieron estilos para `.embarazos-bebes-view` y `.detail-view-container`.

### 3. Lógica (JavaScript)
- **`js/visualBehavior.js`**:
    - Se refactorizó `openEmbarazosBebesModal` a `openEmbarazosBebesScreen`.
    - Se implementó la lógica para ocultar el dashboard y mostrar la pantalla del módulo.
    - Se añadieron funciones para navegar entre la lista y el detalle (`showEmbarazosBebesLists`, `showEmbarazosBebesDetail`).
    - Se implementaron `verDetalleEmbarazoGlobal` y `verDetalleBebeGlobal` para renderizar los detalles en la vista inline.
    - Se implementó `editarEmbarazo(id)` para abrir el modal `#embarazoModal` con los datos precargados.
    - **[NUEVO]** Se actualizó `loadDashboardStats()` para consumir el nuevo endpoint de estadísticas y actualizar la tarjeta.

## Verificación Manual

1.  **Abrir Dashboard**: Cargar la página principal.
2.  **Verificar Métricas**:
    - La tarjeta "Embarazos y Bebés" debe mostrar dos contadores: "Embarazos" y "Bebés Nacidos".
    - Los números deben corresponder a los registros en la base de datos.
3.  **Click en Tarjeta**: Hacer click en la tarjeta "Embarazos y Bebés".
4.  **Verificar Vista Inline**:
    - El dashboard debe ocultarse.
    - Debe aparecer la vista "Gestión de Embarazos y Bebés" ocupando el espacio principal.
5.  **Verificar Edición**:
    - Probar la edición de un embarazo desde la lista.

## Próximos Pasos
- Implementar paginación y filtros en la nueva vista inline.
