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
    views.forEach(view => {
        view.classList.remove('active');
        view.style.display = ''; // Limpiar estilos inline
    });
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

        <!-- Sección de Embarazos -->
        <div class="detail-section full-width">
            <div class="embarazos-header">
                <h3>🤰 Historial de Embarazos</h3>
                <button class="btn-add-embarazo" onclick="agregarEmbarazo(${madre.id})">
                    <span>+</span> Registrar Embarazo
                </button>
            </div>
            <div id="embarazosList">
                <div class="loading-spinner">Cargando embarazos...</div>
            </div>
        </div>

        <!-- Sección de Bebés -->
        <div class="detail-section full-width">
            <div class="embarazos-header"> <!-- Reusamos clase para estilo similar -->
                <h3>👶 Bebés Registrados</h3>
                <button class="btn-add-embarazo" onclick="registrarBebe(${madre.id})">
                    <span>+</span> Registrar Bebé
                </button>
            </div>
            <div id="bebesList">
                <!-- Se cargarán dinámicamente si se requiere, por ahora solo el botón -->
                <p class="no-data">Haga clic en Registrar Bebé para añadir un nuevo registro.</p>
            </div>
        </div>
    `;

    // Actualizar el botón de editar con el ID correcto
    const editBtn = document.querySelector('#madreDetailView .btn-edit-primary');
    if (editBtn) {
        editBtn.onclick = () => editMadre(madre.id);
    }

    // Guardar ID actual para volver
    window.currentMadreId = madre.id;

    // Cargar embarazos y bebés de esta madre
    loadEmbarazosByMadre(madre.id);
    loadBebesByMadre(madre.id);
}

// Mostrar formulario de bebé
function registrarBebe(madreId) {
    // Mostrar modal de bebé
    const modal = document.getElementById('bebeModal');
    modal.classList.add('active');

    // Resetear formulario
    const form = document.getElementById('bebeForm');
    form.reset();
    document.getElementById('bebeId').value = '';
    document.getElementById('bebeMadreId').value = madreId;
    // document.getElementById('bebeFormTitle').textContent = 'Registrar Bebé'; // Ya no es necesario si el título es fijo en el modal

    // Cargar embarazos de la madre para el select
    const selectEmbarazo = document.getElementById('selectEmbarazoBebe');
    selectEmbarazo.innerHTML = '<option value="">Cargando...</option>';

    fetch(`api/embarazos/listar_por_madre.php?madreId=${madreId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                selectEmbarazo.innerHTML = '<option value="">Seleccione Embarazo...</option>';
                data.data.forEach(emb => {
                    const option = document.createElement('option');
                    option.value = emb.id;
                    // Mostrar fecha o algún identificador del embarazo
                    option.textContent = `Embarazo del ${emb.createdAt.substring(0, 10)} (${emb.estado || 'Activo'})`;
                    selectEmbarazo.appendChild(option);
                });
            } else {
                selectEmbarazo.innerHTML = '<option value="">No hay embarazos registrados</option>';
                alert('Esta madre no tiene embarazos registrados. Por favor registre un embarazo primero.');
            }
        })
        .catch(err => {
            console.error('Error cargando embarazos:', err);
            selectEmbarazo.innerHTML = '<option value="">Error al cargar</option>';
        });
}

// Volver al detalle de la madre
function showMadreDetail() {
    hideAllViews();
    document.getElementById('madreDetailView').classList.add('active');
    if (window.currentMadreId) {
        loadMadreDetails(window.currentMadreId); // Recargar para ver cambios
    }
}

function closeBebeModal() {
    document.getElementById('bebeModal').classList.remove('active');
}

// Manejar envío del formulario de bebé
document.addEventListener('DOMContentLoaded', function () {
    const bebeForm = document.getElementById('bebeForm');
    if (bebeForm) {
        bebeForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            // Convertir checkbox a booleano/int
            data.esMellizo = formData.get('esMellizo') ? 1 : 0;

            fetch('api/bebes/guardar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        alert('Bebé guardado correctamente');
                        closeBebeModal();
                        if (window.currentMadreId) {
                            loadBebesByMadre(window.currentMadreId);
                            loadEmbarazosByMadre(window.currentMadreId);
                        }
                    } else {
                        alert('Error al guardar: ' + result.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al guardar');
                });
        });
    }
});

// ========================================
// GESTIÓN DE EMBARAZOS
// ========================================

// Cargar embarazos de una madre
async function loadEmbarazosByMadre(madreId) {
    const embarazosList = document.getElementById('embarazosList');

    try {
        const response = await fetch(`api/embarazos/listar_por_madre.php?madreId=${madreId}`);
        const result = await response.json();

        if (result.success) {
            renderEmbarazosList(result.data, madreId);
        } else {
            embarazosList.innerHTML = `<div class="error-message">Error: ${result.error}</div>`;
        }
    } catch (error) {
        console.error('Error cargando embarazos:', error);
        embarazosList.innerHTML = '<div class="error-message">Error de conexión al cargar embarazos</div>';
    }
}

// Renderizar lista de embarazos
function renderEmbarazosList(embarazos, madreId) {
    const embarazosList = document.getElementById('embarazosList');

    if (embarazos.length === 0) {
        embarazosList.innerHTML = '<div class="no-data">No hay embarazos registrados para esta madre.</div>';
        return;
    }

    let html = '<div class="embarazos-grid">';

    embarazos.forEach((embarazo, index) => {
        const enCurso = embarazo.totalBebesPorNacer > 0;
        const estadoClass = enCurso ? 'embarazo-activo' : 'embarazo-completo';
        const estadoIcon = enCurso ? '🤰' : '✅';
        const multipleTag = embarazo.esMultiple ? '<span class="tag-multiple">👥 Múltiple</span>' : '';

        html += `
            <div class="embarazo-card ${estadoClass}">
                <div class="embarazo-card-header">
                    <div class="embarazo-icon">${estadoIcon}</div>
                    <div class="embarazo-info">
                        <h4>Embarazo #${embarazos.length - index}</h4>
                        <span class="embarazo-estado-badge ${enCurso ? 'estado-en-curso' : 'estado-completo'}">
                            ${enCurso ? 'En Curso' : 'Completado'}
                        </span>
                        ${multipleTag}
                    </div>
                </div>
                <div class="embarazo-card-body">
                    <div class="embarazo-stats">
                        <div class="stat-item ${embarazo.totalBebesNacidos > 0 ? 'stat-highlight' : ''}">
                            <span class="stat-number">${embarazo.totalBebesNacidos}</span>
                            <span class="stat-label">Nacidos</span>
                        </div>
                        <div class="stat-item ${embarazo.totalBebesPorNacer > 0 ? 'stat-highlight-blue' : ''}">
                            <span class="stat-number">${embarazo.totalBebesPorNacer}</span>
                            <span class="stat-label">Por Nacer</span>
                        </div>
                        ${embarazo.bebesNoNacidos > 0 ? `
                        <div class="stat-item stat-warning">
                            <span class="stat-number">${embarazo.bebesNoNacidos}</span>
                            <span class="stat-label">No Nacidos</span>
                        </div>` : ''}
                        ${embarazo.bebesFallecidos > 0 ? `
                        <div class="stat-item stat-danger">
                            <span class="stat-number">${embarazo.bebesFallecidos}</span>
                            <span class="stat-label">Fallecidos</span>
                        </div>` : ''}
                    </div>
                    <div class="embarazo-total">
                        <strong>Total:</strong> ${embarazo.totalBebes} bebé(s)
                    </div>
                    <div class="embarazo-fecha">
                        <small>Registrado: ${formatDate(embarazo.createdAt)}</small>
                    </div>
                </div>
                <div class="embarazo-card-actions">
                    <button class="btn-view-embarazo" onclick="verDetalleEmbarazo(${embarazo.id})" title="Ver detalles">
                        👁️ Ver Bebés
                    </button>
                    <button class="btn-delete-embarazo" onclick="eliminarEmbarazo(${embarazo.id})" title="Eliminar">
                        🗑️
                    </button>
                </div>
            </div>
        `;
    });

    html += '</div>';

    // Resumen de embarazos
    const totalEmbarazos = embarazos.length;
    const embarazosActivos = embarazos.filter(e => e.totalBebesPorNacer > 0).length;
    const totalNacidos = embarazos.reduce((sum, e) => sum + e.totalBebesNacidos, 0);

    html += `
        <div class="embarazos-resumen">
            <div class="resumen-item">
                <span class="resumen-label">Total Embarazos:</span>
                <span class="resumen-value">${totalEmbarazos}</span>
            </div>
            <div class="resumen-item">
                <span class="resumen-label">En Curso:</span>
                <span class="resumen-value resumen-activo">${embarazosActivos}</span>
            </div>
            <div class="resumen-item">
                <span class="resumen-label">Total Bebés Nacidos:</span>
                <span class="resumen-value resumen-nacidos">${totalNacidos}</span>
            </div>
        </div>
    `;

    embarazosList.innerHTML = html;
}

// Ver detalle de un embarazo con sus bebés
async function verDetalleEmbarazo(embarazoId) {
    try {
        const response = await fetch(`api/embarazos/obtener.php?id=${embarazoId}&con_bebes=true`);
        const result = await response.json();

        if (result.success) {
            mostrarModalEmbarazo(result.data);
        } else {
            alert('Error al cargar detalles del embarazo: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión al cargar detalles del embarazo');
    }
}

// Mostrar modal con detalles del embarazo
function mostrarModalEmbarazo(data) {
    const embarazo = data.embarazo;
    const bebes = data.bebes;

    let bebesHTML = '';
    if (bebes.length > 0) {
        bebesHTML = '<div class="modal-bebes-list">';
        bebes.forEach(bebe => {
            const sexoIcon = bebe.sexo === 'M' ? '👦' : bebe.sexo === 'F' ? '👧' : '👶';
            bebesHTML += `
                <div class="modal-bebe-item">
                    <span class="modal-bebe-icon">${sexoIcon}</span>
                    <div class="modal-bebe-info">
                        <strong>${bebe.nombre || 'Sin nombre'}</strong>
                        <span class="modal-bebe-estado ${getEstadoClass(bebe.estado)}">${bebe.estado}</span>
                    </div>
                    ${bebe.fechaNacimiento ? `<span class="modal-bebe-fecha">${formatDate(bebe.fechaNacimiento)}</span>` : ''}
                </div>
            `;
        });
        bebesHTML += '</div>';
    } else {
        bebesHTML = '<p class="no-data">No hay bebés registrados para este embarazo.</p>';
    }

    // Mostrar modal
    const modal = document.getElementById('viewBebesModal');
    const content = document.getElementById('viewBebesContent');
    content.innerHTML = bebesHTML;
    modal.classList.add('active');
}

function closeViewBebesModal() {
    document.getElementById('viewBebesModal').classList.remove('active');
}

// Eliminar Embarazo
function eliminarEmbarazo(id) {
    if (!confirm('¿Está seguro de eliminar este embarazo? Esta acción no se puede deshacer y eliminará también los bebés asociados.')) return;

    fetch('api/embarazos/eliminar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Embarazo eliminado');
                if (window.currentMadreId) {
                    loadEmbarazosByMadre(window.currentMadreId);
                    loadBebesByMadre(window.currentMadreId);
                }
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => console.error(err));
}

// Función para abrir modal de registro de embarazo
function agregarEmbarazo(madreId) {
    console.log('Agregar embarazo para madre:', madreId);

    // Obtener el modal y el formulario
    const modal = document.getElementById('embarazoModal');
    const form = document.getElementById('embarazoForm');

    // Resetear formulario
    form.reset();

    // Establecer el ID de la madre
    document.getElementById('embarazoMadreId').value = madreId;

    // Valores por defecto
    document.getElementById('totalBebesPorNacer').value = 1;
    document.getElementById('totalBebesNacidos').value = 0;
    document.getElementById('bebesNoNacidos').value = 0;
    document.getElementById('bebesFallecidos').value = 0;
    document.getElementById('esMultiple').checked = false;

    // Ocultar campos opcionales
    document.getElementById('optionalFields').style.display = 'none';
    document.getElementById('toggleIcon').textContent = '▶';

    // Mostrar modal
    modal.classList.add('active');
}

// Función para cerrar modal de embarazo
function closeEmbarazoModal() {
    const modal = document.getElementById('embarazoModal');
    modal.classList.remove('active');
}

// Función para toggle de campos opcionales
function toggleOptionalFields() {
    const optionalFields = document.getElementById('optionalFields');
    const toggleIcon = document.getElementById('toggleIcon');

    if (optionalFields.style.display === 'none') {
        optionalFields.style.display = 'block';
        toggleIcon.textContent = '▼';
    } else {
        optionalFields.style.display = 'none';
        toggleIcon.textContent = '▶';
    }
}

// Función placeholder para editar embarazo
function editarEmbarazo(embarazoId) {
    console.log('Editar embarazo:', embarazoId);
    alert('Funcionalidad de edición de embarazo en desarrollo');
}

// ========================================
// GESTIÓN DE BEBÉS
// ========================================

// Cargar bebés de una madre
async function loadBebesByMadre(madreId) {
    const bebesList = document.getElementById('bebesList');

    try {
        const response = await fetch(`api/bebes/listar_por_madre.php?madreId=${madreId}`);
        const result = await response.json();

        if (result.success) {
            renderBebesList(result.data);
        } else {
            bebesList.innerHTML = `<div class="error-message">Error: ${result.error}</div>`;
        }
    } catch (error) {
        console.error('Error cargando bebés:', error);
        bebesList.innerHTML = '<div class="error-message">Error de conexión al cargar bebés</div>';
    }
}

// Renderizar lista de bebés
function renderBebesList(bebes) {
    const bebesList = document.getElementById('bebesList');

    if (bebes.length === 0) {
        bebesList.innerHTML = '<div class="no-data">No hay bebés registrados para esta madre.</div>';
        return;
    }

    let html = '<div class="bebes-grid">';

    bebes.forEach(bebe => {
        const estadoClass = getEstadoClass(bebe.estado);
        const sexoIcon = bebe.sexo === 'M' ? '👦' : bebe.sexo === 'F' ? '👧' : '👶';
        const mellizoTag = bebe.esMellizo ? '<span class="tag-mellizo">👯 Mellizo</span>' : '';

        html += `
            <div class="bebe-card">
                <div class="bebe-card-header">
                    <div class="bebe-icon">${sexoIcon}</div>
                    <div class="bebe-info">
                        <h4>${bebe.nombre || 'Sin nombre'}</h4>
                        <span class="bebe-estado ${estadoClass}">${bebe.estado}</span>
                        ${mellizoTag}
                    </div>
                </div>
                <div class="bebe-card-body">
                    <div class="bebe-detail">
                        <label>Sexo:</label>
                        <span>${getSexoText(bebe.sexo)}</span>
                    </div>
                    <div class="bebe-detail">
                        <label>Fecha Nacimiento:</label>
                        <span>${bebe.fechaNacimiento ? formatDate(bebe.fechaNacimiento) : 'No registrada'}</span>
                    </div>
                    ${bebe.fechaIncidente ? `
                    <div class="bebe-detail">
                        <label>Fecha Incidente:</label>
                        <span>${formatDate(bebe.fechaIncidente)}</span>
                    </div>` : ''}
                    ${bebe.observaciones ? `
                    <div class="bebe-detail">
                        <label>Observaciones:</label>
                        <span>${bebe.observaciones}</span>
                    </div>` : ''}
                </div>
                <div class="bebe-card-actions">
                    <button class="btn-edit-bebe" onclick="editarBebe(${bebe.id})" title="Editar">
                        ✏️
                    </button>
                    <button class="btn-delete-bebe" onclick="eliminarBebe(${bebe.id})" title="Eliminar">
                        🗑️
                    </button>
                </div>
            </div>
        `;
    });

    html += '</div>';
    bebesList.innerHTML = html;
}

// Funciones auxiliares para bebés
function getEstadoClass(estado) {
    const estados = {
        'Nacido': 'estado-nacido',
        'Por nacer': 'estado-por-nacer',
        'Muerte gestacional': 'estado-muerte',
        'Aborto': 'estado-aborto',
        'Fallecido': 'estado-fallecido'
    };
    return estados[estado] || 'estado-default';
}

function getSexoText(sexo) {
    const sexos = {
        'M': 'Masculino',
        'F': 'Femenino'
    };
    return sexos[sexo] || 'No especificado';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Función placeholder para agregar bebé (implementar después)
function agregarBebe(madreId) {
    console.log('Agregar bebé para madre:', madreId);
    alert('Funcionalidad de registro de bebé en desarrollo');
}

// Editar Bebé
function editarBebe(id) {
    fetch(`api/bebes/obtener.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const bebe = data.data;
                const modal = document.getElementById('bebeModal');
                const form = document.getElementById('bebeForm');

                // Reset y popular
                form.reset();
                document.getElementById('bebeId').value = bebe.id;
                document.getElementById('bebeMadreId').value = bebe.madreId;

                // Cargar embarazos y seleccionar el correcto
                const selectEmbarazo = document.getElementById('selectEmbarazoBebe');
                fetch(`api/embarazos/listar_por_madre.php?madreId=${bebe.madreId}`)
                    .then(r => r.json())
                    .then(resp => {
                        if (resp.success) {
                            selectEmbarazo.innerHTML = '<option value="">Seleccione...</option>';
                            resp.data.forEach(emb => {
                                const opt = document.createElement('option');
                                opt.value = emb.id;
                                opt.textContent = `Embarazo del ${emb.createdAt.substring(0, 10)}`;
                                if (emb.id == bebe.embarazoId) opt.selected = true;
                                selectEmbarazo.appendChild(opt);
                            });
                        }
                    });

                // Llenar campos
                form.elements['nombre'].value = bebe.nombre || '';
                form.elements['sexo'].value = bebe.sexo || '';
                form.elements['fechaNacimiento'].value = bebe.fechaNacimiento || '';
                form.elements['estado'].value = bebe.estado || 'Por nacer';
                form.elements['fechaIncidente'].value = bebe.fechaIncidente || '';
                form.elements['esMellizo'].checked = bebe.esMellizo == 1;
                form.elements['observaciones'].value = bebe.observaciones || '';

                modal.classList.add('active');
            } else {
                alert('Error al cargar datos del bebé');
            }
        });
}

// Eliminar Bebé
function eliminarBebe(id) {
    if (!confirm('¿Está seguro de eliminar este bebé?')) return;

    fetch('api/bebes/eliminar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Bebé eliminado');
                if (window.currentMadreId) {
                    loadBebesByMadre(window.currentMadreId);
                    loadEmbarazosByMadre(window.currentMadreId);
                }
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => console.error(err));
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

// Manejar envío de formularios
document.addEventListener('DOMContentLoaded', function () {
    // Formulario de Madres
    const madreForm = document.getElementById('madreForm');
    if (madreForm) {
        madreForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(madreForm);
            const data = Object.fromEntries(formData.entries());

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
                    closeMadresModal();
                    openMadresModal();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error al guardar:', error);
                alert('Error de conexión al guardar');
            }
        });
    }

    // Formulario de Embarazos
    const embarazoForm = document.getElementById('embarazoForm');
    if (embarazoForm) {
        embarazoForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(embarazoForm);
            const data = {
                madreId: parseInt(formData.get('madreId')),
                totalBebesNacidos: parseInt(formData.get('totalBebesNacidos') || 0),
                totalBebesPorNacer: parseInt(formData.get('totalBebesPorNacer') || 1),
                bebesNoNacidos: parseInt(formData.get('bebesNoNacidos') || 0),
                bebesFallecidos: parseInt(formData.get('bebesFallecidos') || 0),
                esMultiple: formData.get('esMultiple') === 'on'
            };

            // Validación: si espera más de 1 bebé, debe marcarse como múltiple
            if (data.totalBebesPorNacer > 1 && !data.esMultiple) {
                if (confirm('Está esperando más de un bebé. ¿Desea marcarlo como embarazo múltiple?')) {
                    data.esMultiple = true;
                }
            }

            try {
                const response = await fetch('api/embarazos/guardar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // Cerrar modal
                    closeEmbarazoModal();

                    // Mostrar mensaje de éxito
                    alert('✅ ' + result.message);

                    // Recargar la lista de embarazos de esta madre
                    loadEmbarazosByMadre(data.madreId);
                } else {
                    alert('❌ Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error al guardar embarazo:', error);
                alert('❌ Error de conexión al guardar embarazo');
            }
        });
    }

    // Cerrar modal al hacer click fuera
    const embarazoModal = document.getElementById('embarazoModal');
    if (embarazoModal) {
        embarazoModal.addEventListener('click', function (e) {
            if (e.target === embarazoModal) {
                closeEmbarazoModal();
            }
        });
    }
});
