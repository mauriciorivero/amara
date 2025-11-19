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
document.getElementById('joinModal').addEventListener('click', function (e) {
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

    // Cargar datos reales
    loadMadres();
    // Cargar opciones de selectores para el formulario y filtros
    loadFormOptions();

    // Configurar listeners si no se ha hecho
    if (!window.filtersInitialized) {
        setupFilterListeners();
        window.filtersInitialized = true;
    }
}

// ... (resto de funciones de modal)

// Estado global de filtros y paginación
let currentFilters = {
    page: 1,
    limit: 25,
    search: '',
    estado: '',
    orientadora: '',
    eps: '',
    edad: ''
};

// Cargar listado de madres desde la API
async function loadMadres() {
    const tableBody = document.getElementById('madresTableBody');
    tableBody.innerHTML = '<tr><td colspan="11" class="td-center">Cargando datos...</td></tr>';

    // Construir Query String
    const params = new URLSearchParams({
        page: currentFilters.page,
        limit: currentFilters.limit,
        search: currentFilters.search,
        estado: currentFilters.estado,
        orientadora: currentFilters.orientadora,
        eps: currentFilters.eps,
        edad: currentFilters.edad
    });

    try {
        const response = await fetch(`api/madres/listar.php?${params.toString()}`);
        const result = await response.json();

        if (result.success) {
            renderMadresTable(result.data);
            updatePaginationInfo(result.pagination);
        } else {
            tableBody.innerHTML = `<tr><td colspan="11" class="td-center error">Error: ${result.error}</td></tr>`;
        }
    } catch (error) {
        console.error('Error cargando madres:', error);
        tableBody.innerHTML = '<tr><td colspan="11" class="td-center error">Error de conexión al cargar datos</td></tr>';
    }
}

function renderMadresTable(madres) {
    const tableBody = document.getElementById('madresTableBody');
    tableBody.innerHTML = '';

    if (madres.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="11" class="td-center">No se encontraron registros</td></tr>';
        return;
    }

    madres.forEach(madre => {
        const orientadoraNombre = madre.orientadora ? madre.orientadora.nombre : 'Sin asignar';
        const epsNombre = madre.eps ? madre.eps.nombre : 'Sin asignar';
        const estadoClass = madre.activa ? 'status-active' : 'status-inactive';
        const estadoText = madre.activa ? 'Activa' : 'Desvinculada';

        // Iniciales para avatar
        const iniciales = madre.nombreCompleto
            .split(' ')
            .map(n => n[0])
            .slice(0, 2)
            .join('')
            .toUpperCase();

        const row = `
            <tr>
                <td class="td-id">${madre.id}</td>
                <td class="td-name">
                    <div class="contact-info">
                        <div class="avatar">${iniciales}</div>
                        <span>${madre.nombreCompleto}</span>
                    </div>
                </td>
                <td>${madre.edad || 'N/A'}</td>
                <td>${madre.numeroDocumento || 'N/A'}</td>
                <td class="td-phone">${madre.numeroTelefono || 'N/A'}</td>
                <td>${orientadoraNombre}</td>
                <td>${epsNombre}</td>
                <td>${madre.fechaIngreso}</td>
                <td class="td-center"><span class="badge-count">${madre.numeroHijos}</span></td>
                <td><span class="status-badge ${estadoClass}">${estadoText}</span></td>
                <td class="td-actions">
                    <button class="btn-action btn-view" onclick="viewMadreDetails(${madre.id})" title="Ver detalles">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                    <button class="btn-action btn-call" onclick="callMadre('${madre.numeroTelefono}')" title="Llamar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}

function updatePaginationInfo(pagination) {
    const pageCount = document.querySelector('.page-count');
    const pageButtons = document.querySelector('.page-buttons');

    if (!pagination) return;

    const start = ((pagination.page - 1) * pagination.limit) + 1;
    const end = Math.min(start + pagination.limit - 1, pagination.total);

    if (pagination.total === 0) {
        pageCount.textContent = '0 registros';
    } else {
        pageCount.textContent = `${start} - ${end} de ${pagination.total}`;
    }

    // Renderizar botones de paginación
    let buttonsHtml = '';

    // Botón Anterior
    buttonsHtml += `<button class="page-btn" ${pagination.page <= 1 ? 'disabled' : ''} onclick="changePage(${pagination.page - 1})">←</button>`;

    // Páginas
    for (let i = 1; i <= pagination.pages; i++) {
        // Mostrar siempre primera, última, y cercanas a la actual
        if (i === 1 || i === pagination.pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
            buttonsHtml += `<button class="page-btn ${i === pagination.page ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
        } else if (i === pagination.page - 3 || i === pagination.page + 3) {
            buttonsHtml += `<span class="page-dots">...</span>`;
        }
    }

    // Botón Siguiente
    buttonsHtml += `<button class="page-btn" ${pagination.page >= pagination.pages ? 'disabled' : ''} onclick="changePage(${pagination.page + 1})">→</button>`;

    pageButtons.innerHTML = buttonsHtml;
}

function changePage(newPage) {
    currentFilters.page = newPage;
    loadMadres();
}

// Configurar listeners de filtros
function setupFilterListeners() {
    // Búsqueda (con debounce)
    const searchInput = document.getElementById('searchNombre');
    let timeout = null;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            currentFilters.search = e.target.value;
            currentFilters.page = 1; // Resetear a página 1
            loadMadres();
        }, 500);
    });

    // Selectores
    ['filterEstado', 'filterOrientadora', 'filterEps', 'filterEdad'].forEach(id => {
        document.getElementById(id).addEventListener('change', (e) => {
            const key = id.replace('filter', '').toLowerCase();
            currentFilters[key] = e.target.value;
            currentFilters.page = 1;
            loadMadres();
        });
    });

    // Paginación por página
    document.getElementById('perPage').addEventListener('change', (e) => {
        currentFilters.limit = parseInt(e.target.value);
        currentFilters.page = 1;
        loadMadres();
    });
}

// ... (resto de funciones sin cambios o adaptadas)

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

// Cargar detalles de madre
async function loadMadreDetails(madreId) {
    const detailContent = document.getElementById('madreDetailContent');
    detailContent.innerHTML = '<div class="loading-spinner">Cargando información...</div>';

    try {
        const response = await fetch(`api/madres/obtener.php?id=${madreId}`);
        const result = await response.json();

        if (result.success) {
            renderMadreDetail(result.data);
        } else {
            detailContent.innerHTML = `<div class="error-message">Error: ${result.error}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        detailContent.innerHTML = '<div class="error-message">Error de conexión al cargar detalles</div>';
    }
}

function renderMadreDetail(madre) {
    const detailContent = document.getElementById('madreDetailContent');



    const orientadora = madre.orientadora ? madre.orientadora.nombre : 'Sin asignar';
    const aliado = madre.aliado ? madre.aliado.nombre : 'Sin asignar';
    const eps = madre.eps ? madre.eps.nombre : 'Sin asignar';
    const esVirtual = madre.esVirtual ? 'Sí' : 'No';
    const activa = madre.activa ? 'Activa' : 'Desvinculada';

    detailContent.innerHTML = `
        <div class="detail-header-card">
            <div class="detail-avatar">${madre.nombreCompleto.charAt(0)}</div>
            <div class="detail-title">
                <h2>${madre.nombreCompleto}</h2>
                <span class="status-badge ${madre.activa ? 'status-active' : 'status-inactive'}">${activa}</span>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-section">
                <h3>Información Personal</h3>
                <div class="detail-row"><label>Documento:</label> <span>${madre.tipoDocumento} ${madre.numeroDocumento}</span></div>
                <div class="detail-row"><label>Edad:</label> <span>${madre.edad} años</span></div>
                <div class="detail-row"><label>Fecha Nacimiento:</label> <span>${madre.fechaNacimiento || 'N/A'}</span></div>
                <div class="detail-row"><label>Estado Civil:</label> <span>${madre.estadoCivil || 'N/A'}</span></div>
                <div class="detail-row"><label>Ocupación:</label> <span>${madre.ocupacion || 'N/A'}</span></div>
                <div class="detail-row"><label>Nivel Estudio:</label> <span>${madre.nivelEstudio || 'N/A'}</span></div>
            </div>

            <div class="detail-section">
                <h3>Contacto</h3>
                <div class="detail-row"><label>Teléfono:</label> <span>${madre.numeroTelefono}</span></div>
                <div class="detail-row"><label>Otro Contacto:</label> <span>${madre.otroContacto || 'N/A'}</span></div>
                <div class="detail-row"><label>Modalidad:</label> <span>${madre.esVirtual ? 'Virtual' : 'Presencial'}</span></div>
                <div class="detail-row"><label>Pareja:</label> <span>${madre.nombrePareja || 'N/A'} (${madre.telefonoPareja || ''})</span></div>
            </div>

            <div class="detail-section">
                <h3>Salud y Seguridad Social</h3>
                <div class="detail-row"><label>EPS:</label> <span>${eps}</span></div>
                <div class="detail-row"><label>Sisbén:</label> <span>${madre.sisben || 'N/A'}</span></div>
                <div class="detail-row"><label>Enfermedades:</label> <span>${madre.enfermedadesMedicamento || 'Ninguna'}</span></div>
            </div>

            <div class="detail-section">
                <h3>Programa Amara</h3>
                <div class="detail-row"><label>Fecha Ingreso:</label> <span>${madre.fechaIngreso}</span></div>
                <div class="detail-row"><label>Orientadora:</label> <span>${orientadora}</span></div>
                <div class="detail-row"><label>Aliado:</label> <span>${aliado}</span></div>
                <div class="detail-row"><label>Hijos:</label> <span>${madre.numeroHijos}</span></div>
                <div class="detail-row"><label>Pérdidas:</label> <span>${madre.perdidas}</span></div>
            </div>
        </div>
        
        ${madre.novedades ? `
        <div class="detail-section full-width">
            <h3>Novedades / Observaciones</h3>
            <p>${madre.novedades}</p>
        </div>` : ''}
    `;

    // Actualizar el botón de editar con el ID correcto
    const editBtn = document.querySelector('#madreDetailView .btn-edit-primary');
    if (editBtn) {
        editBtn.onclick = () => editMadre(madre.id);
    }
}

// Editar madre
async function editMadre(madreId) {
    showFormView();
    document.getElementById('formTitle').textContent = 'Editar Información de Madre';

    // Asegurar que las opciones estén cargadas
    await loadFormOptions();

    try {
        // Obtener datos frescos
        const response = await fetch(`api/madres/obtener.php?id=${madreId}`);
        const result = await response.json();

        if (result.success) {
            const madre = result.data;
            const form = document.getElementById('madreForm');

            // Llenar campos
            form.madreId.value = madre.id;
            form.primerNombre.value = madre.primerNombre || '';
            form.segundoNombre.value = madre.segundoNombre || '';
            form.primerApellido.value = madre.primerApellido || '';
            form.segundoApellido.value = madre.segundoApellido || '';
            form.tipoDocumento.value = madre.tipoDocumento || 'CC';
            form.numeroDocumento.value = madre.numeroDocumento || '';
            form.fechaNacimiento.value = madre.fechaNacimiento || '';
            form.edad.value = madre.edad || '';
            form.numeroTelefono.value = madre.numeroTelefono || '';
            form.otroContacto.value = madre.otroContacto || '';
            form.esVirtual.value = madre.esVirtual ? '1' : '0';

            form.estadoCivil.value = madre.estadoCivil || 'Soltera';
            form.ocupacion.value = madre.ocupacion || '';
            form.nivelEstudio.value = madre.nivelEstudio || '';
            form.religion.value = madre.religion || '';

            form.epsId.value = madre.epsId || '';
            form.sisben.value = madre.sisben || '';
            form.orientadoraId.value = madre.orientadoraId || '';
            form.aliadoId.value = madre.aliadoId || '';
            form.fechaIngreso.value = madre.fechaIngreso || '';

        } else {
            alert('Error al cargar datos para edición: ' + result.error);
            showListView();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
        showListView();
    }
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

    currentFilters = {
        page: 1,
        limit: 25,
        search: '',
        estado: '',
        orientadora: '',
        eps: '',
        edad: ''
    };

    loadMadres();
}

// Cargar opciones para los selectores del formulario y filtros
async function loadFormOptions() {
    // Solo cargar si no tienen opciones (evitar recargas innecesarias)
    const epsSelect = document.getElementById('selectEps');
    if (epsSelect && epsSelect.options.length > 1) return;

    try {
        // Cargar EPS
        fetch('api/eps/listar.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    populateSelect('selectEps', data.data);
                    populateSelect('filterEps', data.data); // Poblar filtro
                }
            })
            .catch(err => console.error('Error fetch EPS:', err));

        // Cargar Orientadoras
        fetch('api/orientadoras/listar.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    populateSelect('selectOrientadora', data.data);
                    populateSelect('filterOrientadora', data.data); // Poblar filtro
                }
            })
            .catch(err => console.error('Error fetch Orientadoras:', err));

        // Cargar Aliados
        fetch('api/aliados/listar.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) populateSelect('selectAliado', data.data);
            })
            .catch(err => console.error('Error fetch Aliados:', err));

    } catch (error) {
        console.error('Error cargando opciones del formulario:', error);
    }
}

function populateSelect(elementId, items) {
    const select = document.getElementById(elementId);
    if (!select) return; // Seguridad si el elemento no existe

    // Mantener la opción por defecto
    const defaultText = select.options[0].text;
    select.innerHTML = `<option value="">${defaultText}</option>`;

    items.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = item.nombre;
        select.appendChild(option);
    });
}

// Manejar envío del formulario
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('madreForm');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Convertir checkbox/radio si es necesario o manejar tipos específicos
            // En este caso FormData maneja bien los inputs, pero aseguramos el formato JSON

            try {
                const response = await fetch('api/madres/guardar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    closeMadresModal(); // O volver al listado
                    openMadresModal(); // Recargar listado
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error al guardar:', error);
                alert('Error de conexión al guardar');
            }
        });
    }
});
