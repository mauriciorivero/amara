# Walkthrough - Navegación Dashboard Embarazos y Bebés (Vista Inline)

Se ha implementado la navegación desde la tarjeta "Embarazos y Bebés" del dashboard hacia una nueva vista inline (similar a la vista de Madres), reemplazando el enfoque anterior de modal. Además, se habilitó la edición de embarazos desde esta vista.

## Cambios Realizados

### 1. Backend (DAOs y API)
- **`dao/EmbarazoDAO.php`**: Se actualizó `getAll` para incluir datos de la madre y la orientadora.
- **`dao/BebeDAO.php`**: Se actualizó `getAll` para incluir el nombre de la madre.
- **`api/embarazos/listar.php`**: Endpoint para listar embarazos con datos relacionados.
- **`api/bebes/listar.php`**: Actualizado para incluir nombre de la madre.

### 2. Frontend (HTML/CSS)
- **`index.html`**: 
    - Se eliminó el modal `#embarazosBebesModal`.
    - Se añadió `#embarazos-bebes-screen` con la clase `.module-screen` dentro de la sección principal.
    - Se implementó una estructura de vista dual: Lista (`#embarazos-bebes-list-view`) y Detalle (`#embarazos-bebes-detail-view`).
- **`css/styles.css`**: 
    - Se creó la clase genérica `.module-screen` para estandarizar las vistas de módulos.
    - Se añadieron estilos para `.embarazos-bebes-view` y `.detail-view-container`.

### 3. Lógica (JavaScript)
- **`js/visualBehavior.js`**:
    - Se refactorizó `openEmbarazosBebesModal` a `openEmbarazosBebesScreen`.
    - Se implementó la lógica para ocultar el dashboard y mostrar la pantalla del módulo.
    - Se añadieron funciones para navegar entre la lista y el detalle (`showEmbarazosBebesLists`, `showEmbarazosBebesDetail`).
    - Se implementaron `verDetalleEmbarazoGlobal` y `verDetalleBebeGlobal` para renderizar los detalles en la vista inline.
    - **[NUEVO]** Se implementó `editarEmbarazo(id)` para abrir el modal `#embarazoModal` con los datos precargados, permitiendo la edición desde la lista global.

## Verificación Manual

1.  **Abrir Dashboard**: Cargar la página principal.
2.  **Click en Tarjeta**: Hacer click en la tarjeta "Embarazos y Bebés".
3.  **Verificar Vista Inline**:
    - El dashboard debe ocultarse.
    - Debe aparecer la vista "Gestión de Embarazos y Bebés" ocupando el espacio principal.
    - Botón "Cerrar Módulo" debe regresar al dashboard.
4.  **Verificar Listas**:
    - Listas de embarazos y bebés cargadas correctamente en grid de dos columnas.
5.  **Verificar Detalle**:
    - Click en "Ver" de un embarazo o bebé.
    - La lista debe ocultarse y mostrarse la vista de detalle en el mismo contenedor.
    - Botón "Volver a listas" debe regresar a la vista de grid.
6.  **Verificar Edición de Embarazo**:
    - En la lista de embarazos, hacer click en el botón de editar (lápiz).
    - Debe abrirse el modal "Registrar Nuevo Embarazo" pero con el título "Editar Embarazo".
    - Los campos deben estar llenos con la información del embarazo seleccionado.
    - Al guardar, debe actualizarse la información y cerrarse el modal.

## Próximos Pasos
- Implementar paginación y filtros en la nueva vista inline.
