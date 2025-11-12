// ========================================
// MODAL ORIGINAL
// ========================================
function openModal() {
    document.getElementById('joinModal').classList.add('active');
}

function closeModal() {
    document.getElementById('joinModal').classList.remove('active');
}

// Cerrar modal al hacer clic fuera
document.getElementById('joinModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// ========================================
// PANTALLA DE MADRES - NUEVO DISEÑO
// ========================================

// Abrir pantalla de madres (muestra listado por defecto)
function openMadresModal() {
    const madresScreen = document.getElementById('madresModal');
    const infoSection = document.querySelector('.info-section');
    
    // Activar la pantalla de madres
    madresScreen.classList.add('active');
    // Agregar clase a la sección para ocultar las tarjetas
    infoSection.classList.add('madres-active');
    // Mostrar la vista de listado
    showListView();
}

// Cerrar pantalla de madres y volver a mostrar las tarjetas
function closeMadresModal() {
    const madresScreen = document.getElementById('madresModal');
    const infoSection = document.querySelector('.info-section');
    
    // Desactivar la pantalla de madres
    madresScreen.classList.remove('active');
    // Quitar clase de la sección para mostrar las tarjetas
    infoSection.classList.remove('madres-active');
}

// Gestión de Vistas
function showListView() {
    hideAllViews();
    document.getElementById('madresListView').classList.add('active');
}

function showDetailView() {
    hideAllViews();
    document.getElementById('madreDetailView').classList.add('active');
}

function showFormView() {
    hideAllViews();
    document.getElementById('madreFormView').classList.add('active');
    document.getElementById('formTitle').textContent = 'Registrar Nueva Madre';
}

function hideAllViews() {
    const views = document.querySelectorAll('.madres-view');
    views.forEach(view => view.classList.remove('active'));
}

// Ver detalles de una madre
function viewMadreDetails(madreId) {
    console.log('Ver detalles de madre ID:', madreId);
    showDetailView();
    // Aquí se cargarían los detalles de la madre desde el servidor
    loadMadreDetails(madreId);
}

// Cargar detalles de madre (placeholder)
function loadMadreDetails(madreId) {
    const detailContent = document.getElementById('madreDetailContent');
    detailContent.innerHTML = `
        <div class="info-message">
            Cargando información de la madre #${madreId}...
            <br><br>
            <small>Esta funcionalidad se implementará con conexión al backend</small>
        </div>
    `;
}

// Editar madre
function editMadre() {
    showFormView();
    document.getElementById('formTitle').textContent = 'Editar Información de Madre';
    console.log('Modo edición activado');
}

// Llamar a una madre
function callMadre(phoneNumber) {
    // Limpiar el número (quitar paréntesis, espacios, guiones)
    const cleanNumber = phoneNumber.replace(/[\s\(\)\-]/g, '');
    console.log('Llamando al número:', cleanNumber);
    
    // Intentar abrir el marcador del teléfono
    window.location.href = `tel:${cleanNumber}`;
}

// Limpiar filtros
function clearFilters() {
    document.getElementById('searchNombre').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterOrientadora').value = '';
    document.getElementById('filterEdad').value = '';
    document.getElementById('filterEps').value = '';
    console.log('Filtros limpiados');
    // Aquí se recargaría la tabla sin filtros
}

// Prevenir envío del formulario (por ahora)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('madreForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Formulario de madre enviado (funcionalidad pendiente)');
            // Aquí se implementará la lógica de guardar
        });
    }
});
