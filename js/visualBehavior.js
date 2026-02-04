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
    const selectMadreContainer = document.getElementById('selectMadreBebeContainer');
    
    modal.classList.add('active');

    // Ocultar selector de madre (ya viene desde detalle de madre)
    selectMadreContainer.style.display = 'none';

    // Resetear formulario
    const form = document.getElementById('bebeForm');
    form.reset();
    document.getElementById('bebeId').value = '';
    document.getElementById('bebeMadreId').value = madreId;

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

// Registrar bebé desde vista global (requiere selección de madre)
function registrarBebeGlobal() {
    console.log('Registrar bebé desde vista global');

    const modal = document.getElementById('bebeModal');
    const form = document.getElementById('bebeForm');
    const selectMadreContainer = document.getElementById('selectMadreBebeContainer');
    const selectEmbarazo = document.getElementById('selectEmbarazoBebe');

    // Resetear formulario
    form.reset();
    document.getElementById('bebeId').value = '';
    document.getElementById('bebeMadreId').value = '';
    document.getElementById('searchMadreBebe').value = '';
    document.getElementById('selectedMadreBebeId').value = '';

    // Ocultar campo ID al crear nuevo bebé
    const idField = document.getElementById('bebeIdField');
    if (idField) {
        idField.style.display = 'none';
    }

    // Mostrar contenedor de búsqueda de madre
    selectMadreContainer.style.display = 'block';
    
    // Limpiar selector de embarazos
    selectEmbarazo.innerHTML = '<option value="">Primero busque y seleccione una madre...</option>';
    
    // Configurar autocompletado con callback para cargar embarazos
    setupMadreAutocomplete(
        'searchMadreBebe',
        'madreBebeResults',
        'selectedMadreBebeId',
        function(madreId) {
            // Callback cuando se selecciona una madre
            document.getElementById('bebeMadreId').value = madreId;
            
            // Cargar embarazos de la madre seleccionada
            selectEmbarazo.innerHTML = '<option value="">Cargando...</option>';
            
            fetch(`api/embarazos/listar_por_madre.php?madreId=${madreId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        selectEmbarazo.innerHTML = '<option value="">Seleccione Embarazo...</option>';
                        data.data.forEach(emb => {
                            const option = document.createElement('option');
                            option.value = emb.id;
                            option.textContent = `Embarazo del ${emb.createdAt.substring(0, 10)}`;
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
    );
    
    // Mostrar modal
    modal.classList.add('active');
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
                            // Desde detalle de madre
                            loadBebesByMadre(window.currentMadreId);
                            loadEmbarazosByMadre(window.currentMadreId);
                        } else {
                            // Desde vista global
                            loadGlobalBebes();
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
    const selectMadreContainer = document.getElementById('selectMadreEmbarazoContainer');

    // Resetear formulario
    form.reset();

    // Ocultar selector de madre (ya viene desde detalle de madre)
    selectMadreContainer.style.display = 'none';

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

// Agregar embarazo desde vista global (requiere selección de madre)
function agregarEmbarazoGlobal() {
    console.log('Agregar embarazo desde vista global');
    
    const modal = document.getElementById('embarazoModal');
    const form = document.getElementById('embarazoForm');
    const selectMadreContainer = document.getElementById('selectMadreEmbarazoContainer');
    
    // Resetear formulario
    form.reset();
    document.getElementById('embarazoMadreId').value = '';
    document.getElementById('searchMadreEmbarazo').value = '';
    document.getElementById('selectedMadreEmbarazoId').value = '';
    
    // Mostrar contenedor de búsqueda de madre
    selectMadreContainer.style.display = 'block';
    
    // Configurar autocompletado
    setupMadreAutocomplete(
        'searchMadreEmbarazo',
        'madreEmbarazoResults',
        'selectedMadreEmbarazoId',
        null
    );
    
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
                        <div style="font-size: 0.85em; color: #666; margin-top: 4px;">
                            ID: ${bebe.id}
                        </div>
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

                // Mostrar y poblar campo ID
                const idField = document.getElementById('bebeIdField');
                const idDisplay = document.getElementById('bebeIdDisplay');
                if (idField && idDisplay) {
                    idField.style.display = 'block';
                    idDisplay.textContent = bebe.id;
                }

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

// Buscar madres en tiempo real para autocompletado
let searchMadresTimeout = null;
let allMadresCache = [];

async function searchMadresAutocomplete(searchTerm, inputId, resultsId, hiddenId, onSelectCallback) {
    const resultsDiv = document.getElementById(resultsId);
    
    if (!searchTerm || searchTerm.length < 2) {
        resultsDiv.style.display = 'none';
        return;
    }
    
    // Si no hay cache, cargar todas las madres
    if (allMadresCache.length === 0) {
        try {
            const response = await fetch('api/madres/listar.php?estado=activa&limit=500');
            const result = await response.json();
            if (result.success) {
                allMadresCache = result.data;
            }
        } catch (error) {
            console.error('Error cargando madres:', error);
            return;
        }
    }
    
    // Filtrar madres por término de búsqueda
    const searchLower = searchTerm.toLowerCase();
    const filtered = allMadresCache.filter(madre => 
        madre.nombreCompleto.toLowerCase().includes(searchLower) ||
        (madre.numeroDocumento && madre.numeroDocumento.includes(searchTerm))
    );
    
    // Mostrar resultados
    if (filtered.length > 0) {
        resultsDiv.innerHTML = '';
        filtered.slice(0, 10).forEach(madre => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <strong>${madre.nombreCompleto}</strong>
                <small>${madre.numeroDocumento || 'Sin documento'}</small>
            `;
            item.onclick = () => {
                document.getElementById(inputId).value = madre.nombreCompleto;
                document.getElementById(hiddenId).value = madre.id;
                document.getElementById('embarazoMadreId').value = madre.id;
                resultsDiv.style.display = 'none';
                if (onSelectCallback) onSelectCallback(madre.id);
            };
            resultsDiv.appendChild(item);
        });
        resultsDiv.style.display = 'block';
    } else {
        resultsDiv.innerHTML = '<div class="autocomplete-item no-results">No se encontraron madres</div>';
        resultsDiv.style.display = 'block';
    }
}

// Configurar autocompletado para input de búsqueda de madre
function setupMadreAutocomplete(inputId, resultsId, hiddenId, onSelectCallback) {
    const input = document.getElementById(inputId);
    const resultsDiv = document.getElementById(resultsId);
    
    if (!input) return;
    
    input.addEventListener('input', function() {
        clearTimeout(searchMadresTimeout);
        searchMadresTimeout = setTimeout(() => {
            searchMadresAutocomplete(this.value, inputId, resultsId, hiddenId, onSelectCallback);
        }, 300);
    });
    
    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
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

                    // Recargar según el contexto
                    if (window.currentMadreId) {
                        // Desde detalle de madre
                        loadEmbarazosByMadre(data.madreId);
                    } else {
                        // Desde vista global
                        loadGlobalEmbarazos();
                    }
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

// Funciones para módulos en desarrollo
function openOrientadorasModal() {
    alert('Módulo de Orientadoras en desarrollo');
}

// Abrir modal global de Embarazos y Bebés
// Editar embarazo
async function editarEmbarazo(id) {
    try {
        const response = await fetch(`api/embarazos/obtener.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            const emb = result.data;

            // Abrir modal
            const modal = document.getElementById('embarazoModal');
            modal.classList.add('active');

            // Cambiar título del modal
            modal.querySelector('h2').textContent = '🤰 Editar Embarazo';

            // Llenar formulario
            const form = document.getElementById('embarazoForm');

            // Agregar campo ID oculto si no existe
            let idInput = document.getElementById('embarazoId');
            if (!idInput) {
                idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.id = 'embarazoId';
                idInput.name = 'id';
                form.appendChild(idInput);
            }
            idInput.value = emb.id;

            document.getElementById('embarazoMadreId').value = emb.madreId;
            document.getElementById('totalBebesPorNacer').value = emb.totalBebesPorNacer;
            document.getElementById('esMultiple').checked = emb.esMultiple;

            // Mostrar campos opcionales si tienen datos
            const hasOptionalData = emb.totalBebesNacidos > 0 || emb.bebesNoNacidos > 0 || emb.bebesFallecidos > 0;
            const optionalFields = document.getElementById('optionalFields');

            if (hasOptionalData) {
                optionalFields.style.display = 'block';
                document.getElementById('toggleIcon').textContent = '▼';
            } else {
                optionalFields.style.display = 'none';
                document.getElementById('toggleIcon').textContent = '▶';
            }

            document.getElementById('totalBebesNacidos').value = emb.totalBebesNacidos;
            document.getElementById('bebesNoNacidos').value = emb.bebesNoNacidos;
            document.getElementById('bebesFallecidos').value = emb.bebesFallecidos;

        } else {
            alert('Error al cargar datos: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

// Abrir pantalla global de Embarazos y Bebés
function openEmbarazosBebesScreen() {
    // Ocultar tarjetas
    document.querySelector('.info-section').classList.add('module-active');

    // Mostrar pantalla
    const screen = document.getElementById('embarazos-bebes-screen');
    screen.classList.add('active');

    // Asegurar que se muestra la lista y no el detalle
    showEmbarazosBebesLists();

    loadGlobalEmbarazos();
    loadGlobalBebes();
    
    // Configurar búsqueda si no está configurado
    if (!window.searchEmbarazosBebesInitialized) {
        setupEmbarazosBebesSearch();
        window.searchEmbarazosBebesInitialized = true;
    }
}

function closeEmbarazosBebesScreen() {
    document.querySelector('.info-section').classList.remove('module-active');
    document.getElementById('embarazos-bebes-screen').classList.remove('active');
}

function showEmbarazosBebesLists() {
    document.getElementById('embarazos-bebes-list-view').classList.add('active');
    document.getElementById('embarazos-bebes-detail-view').classList.remove('active');
}

function showEmbarazosBebesDetail(title, content) {
    document.getElementById('embarazos-bebes-list-view').classList.remove('active');
    const detailView = document.getElementById('embarazos-bebes-detail-view');
    detailView.classList.add('active');

    document.getElementById('detail-view-title').textContent = title;
    document.getElementById('detail-view-content').innerHTML = content;
}

// Cargar lista global de embarazos
async function loadGlobalEmbarazos(search = '') {
    const container = document.getElementById('globalEmbarazosList');
    const countBadge = document.getElementById('globalEmbarazosCount');

    container.innerHTML = '<div class="loading-spinner">Cargando...</div>';

    try {
        const params = new URLSearchParams({
            limit: 50,
            search: search
        });
        
        const response = await fetch(`api/embarazos/listar.php?${params.toString()}`);
        const result = await response.json();

        if (result.success) {
            countBadge.textContent = result.pagination.total;
            renderGlobalEmbarazos(result.data);
        } else {
            container.innerHTML = `<div class="error-message">Error: ${result.error}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="error-message">Error de conexión</div>';
    }
}

function renderGlobalEmbarazos(embarazos) {
    const container = document.getElementById('globalEmbarazosList');

    if (embarazos.length === 0) {
        container.innerHTML = '<div class="no-data">No hay embarazos registrados.</div>';
        return;
    }

    let html = '';
    embarazos.forEach(emb => {
        const estadoClass = emb.totalBebesPorNacer > 0 ? 'status-active' : 'status-nacido';
        const estadoText = emb.totalBebesPorNacer > 0 ? 'En Curso' : 'Completado';

        html += `
            <div class="global-list-item">
                <div class="item-info">
                    <h4>${emb.madreNombre}</h4>
                    <div class="item-meta">
                        <div class="meta-row">
                            <span>👩‍⚕️ Orientadora:</span>
                            <strong>${emb.orientadoraNombre}</strong>
                        </div>
                        <div class="meta-row">
                            <span>📅 Registrado:</span>
                            <span>${formatDate(emb.createdAt)}</span>
                        </div>
                        <div class="meta-row">
                            <span class="status-badge ${estadoClass}" style="padding: 2px 8px; font-size: 11px;">
                                ${estadoText}
                            </span>
                            ${emb.esMultiple ? '<span class="tag-multiple" style="font-size: 11px;">Múltiple</span>' : ''}
                        </div>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="btn-view-detail" onclick="verDetalleEmbarazoGlobal(${emb.id})">
                        👁️ Ver
                    </button>
                    <button class="btn-edit-bebe" onclick="editarEmbarazo(${emb.id})" title="Editar">
                        ✏️
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Ver detalle de embarazo en la vista global
async function verDetalleEmbarazoGlobal(id) {
    try {
        const response = await fetch(`api/embarazos/obtener.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            const emb = result.data;
            const html = `
                <div class="detail-content">
                    <div class="detail-section">
                        <h3>Información General</h3>
                        <p><strong>Madre:</strong> ${emb.madreNombre || 'Desconocida'}</p>
                        <p><strong>Orientadora:</strong> ${emb.orientadoraNombre || 'Sin asignar'}</p>
                        <p><strong>Fecha Registro:</strong> ${formatDate(emb.createdAt)}</p>
                        <p><strong>Estado:</strong> ${emb.totalBebesPorNacer > 0 ? 'En Curso' : 'Finalizado'}</p>
                    </div>
                    <div class="detail-section">
                        <h3>Estadísticas</h3>
                        <p><strong>Bebés Nacidos:</strong> ${emb.totalBebesNacidos}</p>
                        <p><strong>Por Nacer:</strong> ${emb.totalBebesPorNacer}</p>
                        <p><strong>No Nacidos:</strong> ${emb.bebesNoNacidos}</p>
                        <p><strong>Fallecidos:</strong> ${emb.bebesFallecidos}</p>
                    </div>
                    <div class="detail-actions" style="margin-top: 20px;">
                        <button class="btn-edit-bebe" onclick="editarEmbarazo(${emb.id})">Editar Embarazo</button>
                    </div>
                </div>
            `;
            showEmbarazosBebesDetail(`Detalle de Embarazo #${emb.id}`, html);
        } else {
            alert('Error al cargar detalles: ' + result.error);
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión');
    }
}

// Cargar lista global de bebés
async function loadGlobalBebes(search = '') {
    const container = document.getElementById('globalBebesList');
    const countBadge = document.getElementById('globalBebesCount');

    container.innerHTML = '<div class="loading-spinner">Cargando...</div>';

    try {
        const params = new URLSearchParams({
            limit: 50,
            search: search
        });
        
        const response = await fetch(`api/bebes/listar.php?${params.toString()}`);
        const result = await response.json();

        if (result.success) {
            countBadge.textContent = result.pagination.total;
            renderGlobalBebes(result.data);
        } else {
            container.innerHTML = `<div class="error-message">Error: ${result.error}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="error-message">Error de conexión</div>';
    }
}

function renderGlobalBebes(bebes) {
    const container = document.getElementById('globalBebesList');

    if (bebes.length === 0) {
        container.innerHTML = '<div class="no-data">No hay bebés registrados.</div>';
        return;
    }

    let html = '';
    bebes.forEach(bebe => {
        const sexoIcon = bebe.sexo === 'M' ? '👦' : bebe.sexo === 'F' ? '👧' : '👶';
        const estadoClass = getEstadoClass(bebe.estado);

        html += `
            <div class="global-list-item">
                <div class="item-info">
                    <h4>${sexoIcon} ${bebe.nombre || 'Sin nombre'}</h4>
                    <div class="item-meta">
                        <div class="meta-row">
                            <span><strong>ID:</strong></span>
                            <strong>${bebe.id}</strong>
                        </div>
                        <div class="meta-row">
                            <span>🤰 Madre:</span>
                            <strong>${bebe.madreNombre || 'Desconocida'}</strong>
                        </div>
                        <div class="meta-row">
                            <span>🎂 Nacimiento:</span>
                            <span>${bebe.fechaNacimiento ? formatDate(bebe.fechaNacimiento) : 'Pendiente'}</span>
                        </div>
                        <div class="meta-row">
                            <span class="bebe-estado ${estadoClass}" style="font-size: 11px;">
                                ${bebe.estado}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="btn-view-detail" onclick="verDetalleBebeGlobal(${bebe.id})">
                        👁️ Ver
                    </button>
                    <button class="btn-edit-bebe" onclick="editarBebe(${bebe.id})" title="Editar">
                        ✏️
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Ver detalle de bebé en la vista global
async function verDetalleBebeGlobal(id) {
    try {
        const response = await fetch(`api/bebes/obtener.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            const bebe = result.data;
            const html = `
                <div class="detail-content">
                    <div class="detail-section">
                        <h3>Datos del Bebé</h3>
                        <p><strong>ID:</strong> ${bebe.id}</p>
                        <p><strong>Nombre:</strong> ${bebe.nombre || 'Sin nombre'}</p>
                        <p><strong>Sexo:</strong> ${bebe.sexo === 'M' ? 'Masculino' : bebe.sexo === 'F' ? 'Femenino' : 'No especificado'}</p>
                        <p><strong>Fecha Nacimiento:</strong> ${bebe.fechaNacimiento || 'Pendiente'}</p>
                        <p><strong>Estado:</strong> ${bebe.estado}</p>
                        <p><strong>Mellizo:</strong> ${bebe.esMellizo ? 'Sí' : 'No'}</p>
                    </div>
                    <div class="detail-section">
                        <h3>Observaciones</h3>
                        <p>${bebe.observaciones || 'Ninguna'}</p>
                    </div>
                    <div class="detail-actions" style="margin-top: 20px;">
                        <button class="btn-edit-bebe" onclick="editarBebe(${bebe.id})">Editar Bebé</button>
                    </div>
                </div>
            `;
            showEmbarazosBebesDetail(`Detalle de Bebé`, html);
        } else {
            alert('Error al cargar detalles: ' + result.error);
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión');
    }
}

function openAliadosModal() {
    alert('Módulo de Aliados en desarrollo');
}

function openAyudasModal() {
    alert('Módulo de Ayudas en desarrollo');
}

function openReportesModal() {
    alert('Módulo de Reportes en desarrollo');
}

// Cargar estadísticas del dashboard
async function loadDashboardStats() {
    // Solo si estamos en el dashboard (existen los elementos)
    const totalMadres = document.getElementById('totalMadres');
    if (!totalMadres) return;

    // Cargar estadísticas de Madres
    try {
        const responseMadres = await fetch('api/madres/estadisticas.php');
        if (responseMadres.ok) {
            const resultMadres = await responseMadres.json();
            if (resultMadres.success) {
                document.getElementById('totalMadres').textContent = resultMadres.data.total;
                document.getElementById('totalMadresActivas').textContent = resultMadres.data.activas;
                document.getElementById('totalMadresInactivas').textContent = resultMadres.data.inactivas;
            }
        } else {
            console.error('Error HTTP al cargar estadísticas de madres:', responseMadres.status);
        }
    } catch (error) {
        console.error('Error cargando estadísticas de madres:', error);
    }

    // Estadísticas de Embarazos y Bebés
    try {
        const responseEmbarazos = await fetch('api/embarazos/estadisticas.php');
        if (responseEmbarazos.ok) {
            const resultEmbarazos = await responseEmbarazos.json();
            if (resultEmbarazos.success) {
                const totalEmbarazosEl = document.getElementById('totalEmbarazos');
                const totalBebesEl = document.getElementById('totalBebesNacidos');

                if (totalEmbarazosEl) totalEmbarazosEl.textContent = resultEmbarazos.data.totalEmbarazos;
                if (totalBebesEl) totalBebesEl.textContent = resultEmbarazos.data.totalBebesNacidos;
            }
        } else {
            console.error('Error HTTP al cargar estadísticas de embarazos:', responseEmbarazos.status);
        }
    } catch (error) {
        console.error('Error cargando estadísticas de embarazos:', error);
    }

    // Estadísticas de Orientadoras
    loadOrientadorasStats();
}

// Configurar búsqueda de embarazos y bebés
function setupEmbarazosBebesSearch() {
    const searchInput = document.getElementById('searchEmbarazosBebes');
    if (!searchInput) return;
    
    let searchTimeout = null;
    
    searchInput.addEventListener('input', function(e) {
        const searchValue = e.target.value.trim();
        
        // Limpiar timeout anterior
        clearTimeout(searchTimeout);
        
        // Esperar 500ms antes de buscar (debounce)
        searchTimeout = setTimeout(() => {
            loadGlobalEmbarazos(searchValue);
            loadGlobalBebes(searchValue);
        }, 500);
    });
}

// Inicializar dashboard
document.addEventListener('DOMContentLoaded', function () {
    loadDashboardStats();
    // Configurar listeners del modal de ayuda
    setupAyudaModalListeners();
    // Cargar estadísticas de aliados y programas
    loadAliadosStats();
    loadProgramasStats();
});

// ========================================
// MÓDULO DE AYUDAS
// ========================================

// Estado global para ayudas
let currentAyudasPage = 1;
let ayudasPerPage = 25;
let ayudasFilters = {
    search: '',
    tipoAyuda: '',
    origenAyuda: '',
    estado: ''
};

// Abrir pantalla de ayudas
function openAyudasScreen() {
    const ayudasScreen = document.getElementById('ayudas-screen');
    const infoSection = document.querySelector('.info-section');

    ayudasScreen.classList.add('active');
    infoSection.classList.add('module-active');
    showAyudasListView();
    
    loadAyudas();
    loadAyudasStats();
    setupAyudasFilters();
}

// Cerrar pantalla de ayudas
function closeAyudasScreen() {
    const ayudasScreen = document.getElementById('ayudas-screen');
    const infoSection = document.querySelector('.info-section');

    ayudasScreen.classList.remove('active');
    infoSection.classList.remove('module-active');
}

// Mostrar vista de lista
function showAyudasListView() {
    document.getElementById('ayudas-list-view').classList.add('active');
    document.getElementById('ayudas-detail-view').classList.remove('active');
}

// Mostrar vista de detalle
function showAyudasDetailView() {
    document.getElementById('ayudas-list-view').classList.remove('active');
    document.getElementById('ayudas-detail-view').classList.add('active');
}

// Cargar estadísticas de ayudas
async function loadAyudasStats() {
    try {
        const response = await fetch('api/ayudas/estadisticas.php');
        const result = await response.json();

        if (result.success) {
            const data = result.data;
            document.getElementById('totalAyudas').textContent = data.totalAyudas || 0;
            document.getElementById('totalValorAyudas').textContent = formatCurrency(data.totalValorEntregado || 0);
        }
    } catch (error) {
        console.error('Error al cargar estadísticas de ayudas:', error);
    }
}

// Cargar listado de ayudas
async function loadAyudas(page = 1) {
    currentAyudasPage = page;
    const tableBody = document.getElementById('ayudasTableBody');
    tableBody.innerHTML = '<tr><td colspan="9" class="td-center">Cargando...</td></tr>';

    try {
        // Construir URL con parámetros
        const params = new URLSearchParams({
            page: page,
            limit: ayudasPerPage,
            tipoAyuda: ayudasFilters.tipoAyuda,
            origenAyuda: ayudasFilters.origenAyuda,
            estado: ayudasFilters.estado
        });

        const response = await fetch(`api/ayudas/listar.php?${params}`);
        const result = await response.json();

        if (result.success) {
            renderAyudasTable(result.data);
            renderAyudasPagination(result.pagination);
        } else {
            tableBody.innerHTML = `<tr><td colspan="9" class="td-center">Error: ${result.error}</td></tr>`;
        }
    } catch (error) {
        console.error('Error al cargar ayudas:', error);
        tableBody.innerHTML = '<tr><td colspan="9" class="td-center">Error de conexión</td></tr>';
    }
}

// Renderizar tabla de ayudas
function renderAyudasTable(ayudas) {
    const tableBody = document.getElementById('ayudasTableBody');
    
    if (ayudas.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="9" class="td-center">No se encontraron ayudas</td></tr>';
        return;
    }

    tableBody.innerHTML = ayudas.map(ayuda => `
        <tr>
            <td>${ayuda.id}</td>
            <td class="td-nombre">${ayuda.madreNombre}</td>
            <td>${ayuda.bebeNombre || '-'}</td>
            <td>${formatTipoAyuda(ayuda.tipoAyuda)}</td>
            <td>${formatOrigenAyuda(ayuda.origenAyuda)}</td>
            <td>${formatDate(ayuda.fechaRecepcion)}</td>
            <td class="td-valor">${formatCurrency(ayuda.valor || 0)}</td>
            <td><span class="badge-estado-ayuda ${ayuda.estado}">${formatEstadoAyuda(ayuda.estado)}</span></td>
            <td class="td-actions">
                <button class="btn-icon-action" title="Ver detalle" onclick="viewAyudaDetail(${ayuda.id})">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 4.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7zM2 8s2.5-4 6-4 6 4 6 4-2.5 4-6 4-6-4-6-4z"/>
                    </svg>
                </button>
                <button class="btn-icon-action" title="Editar" onclick="editarAyuda(${ayuda.id})">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                    </svg>
                </button>
                <button class="btn-icon-action btn-delete" title="Eliminar" onclick="eliminarAyuda(${ayuda.id})">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                    </svg>
                </button>
            </td>
        </tr>
    `).join('');
}

// Renderizar paginación
function renderAyudasPagination(pagination) {
    const pageCount = document.getElementById('ayudasPageCount');
    const pageButtons = document.getElementById('ayudasPageButtons');

    pageCount.textContent = `${pagination.total} registros`;

    const currentPage = pagination.page;
    const totalPages = pagination.pages;

    let buttonsHTML = '';

    // Botón anterior
    buttonsHTML += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} 
                     onclick="loadAyudas(${currentPage - 1})">←</button>`;

    // Números de página
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            buttonsHTML += `<button class="page-btn ${i === currentPage ? 'active' : ''}" 
                             onclick="loadAyudas(${i})">${i}</button>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            buttonsHTML += '<span class="page-ellipsis">...</span>';
        }
    }

    // Botón siguiente
    buttonsHTML += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} 
                     onclick="loadAyudas(${currentPage + 1})">→</button>`;

    pageButtons.innerHTML = buttonsHTML;
}

// Configurar filtros y eventos
function setupAyudasFilters() {
    // Filtro de búsqueda con debounce
    const searchInput = document.getElementById('searchAyudas');
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            ayudasFilters.search = e.target.value;
            loadAyudas(1);
        }, 500);
    });

    // Filtros de select
    document.getElementById('filterTipoAyuda').addEventListener('change', (e) => {
        ayudasFilters.tipoAyuda = e.target.value;
        loadAyudas(1);
    });

    document.getElementById('filterOrigenAyuda').addEventListener('change', (e) => {
        ayudasFilters.origenAyuda = e.target.value;
        loadAyudas(1);
    });

    document.getElementById('filterEstadoAyuda').addEventListener('change', (e) => {
        ayudasFilters.estado = e.target.value;
        loadAyudas(1);
    });

    // Items por página
    document.getElementById('ayudasPerPage').addEventListener('change', (e) => {
        ayudasPerPage = parseInt(e.target.value);
        loadAyudas(1);
    });
}

// Limpiar filtros
function clearAyudasFilters() {
    document.getElementById('searchAyudas').value = '';
    document.getElementById('filterTipoAyuda').value = '';
    document.getElementById('filterOrigenAyuda').value = '';
    document.getElementById('filterEstadoAyuda').value = '';
    
    ayudasFilters = {
        search: '',
        tipoAyuda: '',
        origenAyuda: '',
        estado: ''
    };
    
    loadAyudas(1);
}

// ========================================
// GESTIÓN DE AYUDA (CRUD)
// ========================================

// Agregar ayuda desde vista global
function agregarAyudaGlobal() {
    openAyudaModal();
    
    // Mostrar selector de madre
    document.getElementById('selectMadreAyudaContainer').style.display = 'block';
    
    // Resetear formulario
    document.getElementById('ayudaForm').reset();
    document.getElementById('ayudaId').value = '';
    document.getElementById('ayudaMadreId').value = '';
    document.getElementById('selectedMadreAyudaId').value = '';
    document.getElementById('searchMadreAyuda').value = '';
    document.getElementById('selectBebeAyudaContainer').style.display = 'none';
    
    // Configurar autocomplete de madre
    setupMadreAutocompleteAyuda();
    
    document.querySelector('#ayudaModal h2').textContent = '🎁 Registrar Nueva Ayuda';
}

// Agregar ayuda desde una madre específica
function agregarAyudaParaMadre(madreId) {
    openAyudaModal();
    
    // Ocultar selector de madre
    document.getElementById('selectMadreAyudaContainer').style.display = 'none';
    document.getElementById('ayudaMadreId').value = madreId;
    
    // Resetear formulario
    document.getElementById('ayudaForm').reset();
    document.getElementById('ayudaId').value = '';
    document.getElementById('selectBebeAyudaContainer').style.display = 'none';
    
    document.querySelector('#ayudaModal h2').textContent = '🎁 Registrar Nueva Ayuda';
}

// Editar ayuda
async function editarAyuda(ayudaId) {
    try {
        const response = await fetch(`api/ayudas/obtener.php?id=${ayudaId}`);
        const result = await response.json();

        if (result.success) {
            const ayuda = result.data;
            openAyudaModal();

            // Llenar formulario
            document.getElementById('ayudaId').value = ayuda.id;
            document.getElementById('ayudaMadreId').value = ayuda.madreId;
            document.getElementById('tipoAyuda').value = ayuda.tipoAyuda;
            document.getElementById('origenAyuda').value = ayuda.origenAyuda;
            document.getElementById('fechaRecepcion').value = ayuda.fechaRecepcion;
            document.getElementById('valorAyuda').value = ayuda.valor || '';
            document.getElementById('estadoAyuda').value = ayuda.estado;
            document.getElementById('observacionesAyuda').value = ayuda.observaciones || '';

            // Ocultar selector de madre (no se puede cambiar al editar)
            document.getElementById('selectMadreAyudaContainer').style.display = 'none';

            // Si es ayuda de bebé, cargar bebés y seleccionar
            if (ayuda.bebeId) {
                await loadBebesForMadre(ayuda.madreId);
                document.getElementById('selectBebeAyuda').value = ayuda.bebeId;
                document.getElementById('selectBebeAyudaContainer').style.display = 'block';
            }

            document.querySelector('#ayudaModal h2').textContent = '✏️ Editar Ayuda';
        } else {
            alert('Error al cargar la ayuda: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión al cargar la ayuda');
    }
}

// Eliminar ayuda
async function eliminarAyuda(ayudaId) {
    if (!confirm('¿Está seguro de eliminar esta ayuda?')) {
        return;
    }

    try {
        const response = await fetch('api/ayudas/eliminar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: ayudaId })
        });

        const result = await response.json();

        if (result.success) {
            alert('Ayuda eliminada correctamente');
            loadAyudas(currentAyudasPage);
            loadAyudasStats();
        } else {
            alert('Error al eliminar: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

// Ver detalle de ayuda
async function viewAyudaDetail(ayudaId) {
    try {
        const response = await fetch(`api/ayudas/obtener.php?id=${ayudaId}`);
        const result = await response.json();

        if (result.success) {
            const ayuda = result.data;
            renderAyudaDetail(ayuda);
            showAyudasDetailView();
        } else {
            alert('Error al cargar detalle: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

// Renderizar detalle de ayuda
function renderAyudaDetail(ayuda) {
    const detailContent = document.getElementById('ayudaDetailContent');
    
    detailContent.innerHTML = `
        <div class="detail-card">
            <div class="detail-section">
                <h3>🎁 Información General</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">ID:</span>
                        <span class="detail-value">#${ayuda.id}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tipo de Ayuda:</span>
                        <span class="detail-value">${formatTipoAyuda(ayuda.tipoAyuda)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Origen:</span>
                        <span class="detail-value">${formatOrigenAyuda(ayuda.origenAyuda)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Estado:</span>
                        <span class="badge-estado-ayuda ${ayuda.estado}">${formatEstadoAyuda(ayuda.estado)}</span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3>👩 Beneficiaria</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Madre:</span>
                        <span class="detail-value">${ayuda.madreNombre}</span>
                    </div>
                    ${ayuda.bebeNombre ? `
                    <div class="detail-item">
                        <span class="detail-label">Bebé:</span>
                        <span class="detail-value">${ayuda.bebeNombre}</span>
                    </div>
                    ` : ''}
                </div>
            </div>

            <div class="detail-section">
                <h3>📅 Información de Entrega</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Fecha de Recepción:</span>
                        <span class="detail-value">${formatDate(ayuda.fechaRecepcion)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Valor:</span>
                        <span class="detail-value">${formatCurrency(ayuda.valor || 0)}</span>
                    </div>
                </div>
            </div>

            ${ayuda.observaciones ? `
            <div class="detail-section">
                <h3>📝 Observaciones</h3>
                <p class="detail-observaciones">${ayuda.observaciones}</p>
            </div>
            ` : ''}

            <div class="detail-section">
                <h3>ℹ️ Información del Registro</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Creado:</span>
                        <span class="detail-value">${formatDateTime(ayuda.createdAt)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Última actualización:</span>
                        <span class="detail-value">${formatDateTime(ayuda.updatedAt)}</span>
                    </div>
                </div>
            </div>

            <div class="detail-actions">
                <button class="btn-edit-detail" onclick="editarAyuda(${ayuda.id})">
                    ✏️ Editar Ayuda
                </button>
                <button class="btn-delete-detail" onclick="eliminarAyuda(${ayuda.id})">
                    🗑️ Eliminar Ayuda
                </button>
            </div>
        </div>
    `;
}

// ========================================
// MODAL DE AYUDA
// ========================================

function openAyudaModal() {
    const modal = document.getElementById('ayudaModal');
    if (modal) {
        modal.classList.add('active');
        console.log('Modal de ayuda abierto');
    } else {
        console.error('No se encontró el modal de ayuda');
    }
}

function closeAyudaModal() {
    const modal = document.getElementById('ayudaModal');
    if (modal) {
        modal.classList.remove('active');
        document.getElementById('ayudaForm').reset();
    }
}

// Cerrar modal al hacer clic fuera - Se configura después del DOM
function setupAyudaModalListeners() {
    const ayudaModal = document.getElementById('ayudaModal');
    if (!ayudaModal) return;
    
    ayudaModal.addEventListener('click', function (e) {
        if (e.target === this) {
            closeAyudaModal();
        }
    });

    // Manejar cambio de tipo de ayuda (mostrar/ocultar selector de bebé)
    document.getElementById('tipoAyuda').addEventListener('change', function() {
        const tiposParaBebe = ['kit_recien_nacido', 'salud_recien_nacido', 'elementos_recien_nacido'];
        const selectBebeContainer = document.getElementById('selectBebeAyudaContainer');
        const selectBebe = document.getElementById('selectBebeAyuda');
        
        if (tiposParaBebe.includes(this.value)) {
            selectBebeContainer.style.display = 'block';
            selectBebe.required = true;
            
            // Si hay madre seleccionada, cargar sus bebés
            const madreId = document.getElementById('ayudaMadreId').value;
            if (madreId) {
                loadBebesForMadre(madreId);
            }
        } else {
            selectBebeContainer.style.display = 'none';
            selectBebe.required = false;
            selectBebe.value = '';
        }
    });

    // Enviar formulario de ayuda
    document.getElementById('ayudaForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = {
            id: formData.get('id') || undefined,
            madreId: document.getElementById('ayudaMadreId').value || document.getElementById('selectedMadreAyudaId').value,
            bebeId: formData.get('bebeId') || undefined,
            tipoAyuda: formData.get('tipoAyuda'),
            origenAyuda: formData.get('origenAyuda'),
            fechaRecepcion: formData.get('fechaRecepcion'),
            valor: formData.get('valor') ? parseFloat(formData.get('valor')) : 0,
            estado: formData.get('estado'),
            observaciones: formData.get('observaciones') || undefined
        };

        // Validar madre
        if (!data.madreId) {
            alert('Debe seleccionar una madre');
            return;
        }

        // Validar bebé si es requerido
        const tiposParaBebe = ['kit_recien_nacido', 'salud_recien_nacido', 'elementos_recien_nacido'];
        if (tiposParaBebe.includes(data.tipoAyuda) && !data.bebeId) {
            alert('Debe seleccionar un bebé para este tipo de ayuda');
            return;
        }

        try {
            const response = await fetch('api/ayudas/guardar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                closeAyudaModal();
                loadAyudas(currentAyudasPage);
                loadAyudasStats();
            } else {
                alert('Error: ' + result.error);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión');
        }
    });
}

// Configurar autocomplete de madre para ayudas
let madreAyudaAutocompleteInitialized = false;

function setupMadreAutocompleteAyuda() {
    const searchInput = document.getElementById('searchMadreAyuda');
    const resultsDiv = document.getElementById('madreAyudaResults');
    const hiddenInput = document.getElementById('selectedMadreAyudaId');
    
    // Solo configurar una vez
    if (madreAyudaAutocompleteInitialized) {
        return;
    }
    
    let searchTimeout;

    searchInput.addEventListener('input', async (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            resultsDiv.style.display = 'none';
            hiddenInput.value = '';
            return;
        }

        searchTimeout = setTimeout(async () => {
            // Cargar cache si está vacío
            if (allMadresCache.length === 0) {
                try {
                    const response = await fetch('api/madres/listar.php?estado=activa&limit=500');
                    const result = await response.json();
                    if (result.success) {
                        allMadresCache = result.data;
                        console.log('Cache de madres cargado:', allMadresCache.length, 'madres');
                    } else {
                        console.error('Error al cargar madres:', result.error);
                        return;
                    }
                } catch (error) {
                    console.error('Error de conexión al cargar madres:', error);
                    return;
                }
            }

            const filtered = allMadresCache.filter(madre =>
                madre.nombreCompleto.toLowerCase().includes(query.toLowerCase()) ||
                (madre.numeroDocumento && madre.numeroDocumento.includes(query))
            ).slice(0, 10);

            if (filtered.length > 0) {
                resultsDiv.innerHTML = filtered.map(madre => `
                    <div class="autocomplete-item" data-id="${madre.id}">
                        <strong>${madre.nombreCompleto}</strong>
                        <small>(${madre.numeroDocumento || 'N/A'})</small>
                    </div>
                `).join('');
                resultsDiv.style.display = 'block';

                // Agregar eventos de clic
                resultsDiv.querySelectorAll('.autocomplete-item').forEach(item => {
                    item.addEventListener('click', async () => {
                        const madreId = item.dataset.id;
                        const madreName = item.querySelector('strong').textContent;
                        searchInput.value = madreName;
                        hiddenInput.value = madreId;
                        document.getElementById('ayudaMadreId').value = madreId;
                        resultsDiv.style.display = 'none';
                        
                        // Cargar bebés de esta madre
                        await loadBebesForMadre(madreId);
                    });
                });
            } else {
                resultsDiv.innerHTML = '<div class="no-results">No se encontraron madres</div>';
                resultsDiv.style.display = 'block';
                hiddenInput.value = '';
            }
        }, 300);
    });

    // Cerrar al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });
    
    madreAyudaAutocompleteInitialized = true;
    console.log('Autocomplete de madre para ayuda configurado');
}

// Cargar bebés de una madre para el selector
async function loadBebesForMadre(madreId) {
    try {
        const response = await fetch(`api/bebes/listar_por_madre.php?madreId=${madreId}`);
        const result = await response.json();

        const selectBebe = document.getElementById('selectBebeAyuda');
        
        if (result.success && result.data.length > 0) {
            selectBebe.innerHTML = '<option value="">Seleccione un bebé...</option>' +
                result.data.map(bebe => 
                    `<option value="${bebe.id}">${bebe.nombre} (${formatDate(bebe.fechaNacimiento)})</option>`
                ).join('');
        } else {
            selectBebe.innerHTML = '<option value="">No hay bebés registrados</option>';
        }
    } catch (error) {
        console.error('Error al cargar bebés:', error);
    }
}


// ========================================
// FUNCIONES HELPER PARA AYUDAS
// ========================================

function formatTipoAyuda(tipo) {
    const tipos = {
        'economica': '💰 Económica',
        'transporte': '🚌 Transporte',
        'habitabilidad': '🏠 Habitabilidad',
        'alimentos': '🍎 Alimentos',
        'medicamentos': '💊 Medicamentos',
        'humanitaria': '🆘 Humanitaria',
        'kit_recien_nacido': '👶 Kit Recién Nacido',
        'salud_recien_nacido': '🏥 Salud Recién Nacido',
        'elementos_recien_nacido': '🧸 Elementos Recién Nacido'
    };
    return tipos[tipo] || tipo;
}

function formatOrigenAyuda(origen) {
    const origenes = {
        'corporacion': '🏢 Corporación Adonai',
        'programa_externo': '🤝 Programa Externo'
    };
    return origenes[origen] || origen;
}

function formatEstadoAyuda(estado) {
    const estados = {
        'pendiente': '⏳ Pendiente',
        'entregada': '✅ Entregada',
        'rechazada': '❌ Rechazada',
        'cancelada': '🚫 Cancelada'
    };
    return estados[estado] || estado;
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(value);
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    const date = new Date(dateTimeString);
    return date.toLocaleString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// ========================================
// GESTIÓN DE ALIADOS
// ========================================

// Variables globales para Aliados
let currentAliadosPage = 1;
let aliadosPerPage = 25;
let aliadosFilters = { search: '', estado: '' };

// Abrir pantalla de aliados
function openAliadosScreen() {
    const aliadosScreen = document.getElementById('aliados-screen');
    const infoSection = document.querySelector('.info-section');

    aliadosScreen.classList.add('active');
    infoSection.classList.add('madres-active');
    showAliadosListView();
    loadAliados(1);
    setupAliadosFilters();
}

// Cerrar pantalla de aliados
function closeAliadosScreen() {
    const aliadosScreen = document.getElementById('aliados-screen');
    const infoSection = document.querySelector('.info-section');

    aliadosScreen.classList.remove('active');
    infoSection.classList.remove('madres-active');
}

// Mostrar vista de lista
function showAliadosListView() {
    document.getElementById('aliados-list-view').classList.add('active');
    document.getElementById('aliados-detail-view').classList.remove('active');
}

// Mostrar vista de detalle
function showAliadosDetailView() {
    document.getElementById('aliados-list-view').classList.remove('active');
    document.getElementById('aliados-detail-view').classList.add('active');
}

// Cargar aliados con paginación y filtros
async function loadAliados(page = 1) {
    currentAliadosPage = page;
    aliadosPerPage = document.getElementById('aliadosPerPage')?.value || 25;

    const params = new URLSearchParams({
        page: currentAliadosPage,
        per_page: aliadosPerPage,
        search: aliadosFilters.search || '',
        estado: aliadosFilters.estado || ''
    });

    try {
        const response = await fetch(`api/aliados/listar.php?${params}`);
        const result = await response.json();

        if (result.success) {
            renderAliadosTable(result.data);
            renderAliadosPagination(result.pagination);
        } else {
            console.error('Error al cargar aliados:', result.error);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Renderizar tabla de aliados
function renderAliadosTable(aliados) {
    const tbody = document.getElementById('aliadosTableBody');

    if (!aliados || aliados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="td-center">No se encontraron aliados</td></tr>';
        return;
    }

    tbody.innerHTML = aliados.map(aliado => `
        <tr>
            <td>${aliado.id}</td>
            <td><strong>${aliado.nombre || 'N/A'}</strong></td>
            <td>${aliado.contactoNombre || 'N/A'}</td>
            <td>${aliado.contactoTelefono || 'N/A'}</td>
            <td>${aliado.contactoCorreo || 'N/A'}</td>
            <td>${formatEstadoAliado(aliado.estado)}</td>
            <td><span class="badge">${aliado.totalProgramas || 0}</span></td>
            <td>
                <button class="btn-action btn-view" onclick="viewAliadoDetail(${aliado.id})" title="Ver detalle">
                    👁️
                </button>
                <button class="btn-action btn-edit" onclick="editarAliado(${aliado.id})" title="Editar">
                    ✏️
                </button>
                <button class="btn-action btn-delete" onclick="eliminarAliado(${aliado.id})" title="Eliminar">
                    🗑️
                </button>
            </td>
        </tr>
    `).join('');
}

// Renderizar paginación
function renderAliadosPagination(pagination) {
    const pageCount = document.getElementById('aliadosPageCount');
    const pageButtons = document.getElementById('aliadosPageButtons');

    if (pageCount) {
        pageCount.textContent = `${pagination.total} registros`;
    }

    if (pageButtons) {
        let buttonsHTML = '';
        
        // Botón anterior
        buttonsHTML += `<button class="page-btn" ${currentAliadosPage === 1 ? 'disabled' : ''} 
                        onclick="loadAliados(${currentAliadosPage - 1})">←</button>`;
        
        // Botones de páginas
        for (let i = 1; i <= pagination.totalPages; i++) {
            if (i === 1 || i === pagination.totalPages || (i >= currentAliadosPage - 1 && i <= currentAliadosPage + 1)) {
                buttonsHTML += `<button class="page-btn ${i === currentAliadosPage ? 'active' : ''}" 
                                onclick="loadAliados(${i})">${i}</button>`;
            } else if (i === currentAliadosPage - 2 || i === currentAliadosPage + 2) {
                buttonsHTML += '<span class="page-ellipsis">...</span>';
            }
        }
        
        // Botón siguiente
        buttonsHTML += `<button class="page-btn" ${currentAliadosPage === pagination.totalPages ? 'disabled' : ''} 
                        onclick="loadAliados(${currentAliadosPage + 1})">→</button>`;
        
        pageButtons.innerHTML = buttonsHTML;
    }
}

// Configurar filtros
function setupAliadosFilters() {
    const searchInput = document.getElementById('searchAliados');
    const estadoFilter = document.getElementById('filterEstadoAliado');
    const perPageSelect = document.getElementById('aliadosPerPage');

    if (searchInput && !searchInput.dataset.listenerAttached) {
        searchInput.addEventListener('input', debounce(function() {
            aliadosFilters.search = this.value;
            loadAliados(1);
        }, 500));
        searchInput.dataset.listenerAttached = 'true';
    }

    if (estadoFilter && !estadoFilter.dataset.listenerAttached) {
        estadoFilter.addEventListener('change', function() {
            aliadosFilters.estado = this.value;
            loadAliados(1);
        });
        estadoFilter.dataset.listenerAttached = 'true';
    }

    if (perPageSelect && !perPageSelect.dataset.listenerAttached) {
        perPageSelect.addEventListener('change', function() {
            loadAliados(1);
        });
        perPageSelect.dataset.listenerAttached = 'true';
    }
}

// Limpiar filtros
function clearAliadosFilters() {
    aliadosFilters = { search: '', estado: '' };
    document.getElementById('searchAliados').value = '';
    document.getElementById('filterEstadoAliado').value = '';
    loadAliados(1);
}

// Abrir modal para agregar aliado
function agregarAliado() {
    document.getElementById('aliadoId').value = '';
    document.getElementById('aliadoForm').reset();
    document.querySelector('#aliadoModal h2').textContent = '🤝 Registrar Aliado';
    openAliadoModal();
    loadOrientadorasForAliado();
}

// Editar aliado
async function editarAliado(id) {
    try {
        const response = await fetch(`api/aliados/obtener.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            const aliado = result.data;
            document.getElementById('aliadoId').value = aliado.id;
            document.getElementById('aliadoNombre').value = aliado.nombre || '';
            document.getElementById('aliadoDescripcion').value = aliado.descripcion || '';
            document.getElementById('aliadoContactoNombre').value = aliado.contactoNombre || '';
            document.getElementById('aliadoContactoCargo').value = aliado.contactoCargo || '';
            document.getElementById('aliadoContactoTelefono').value = aliado.contactoTelefono || '';
            document.getElementById('aliadoContactoCorreo').value = aliado.contactoCorreo || '';
            document.getElementById('aliadoVinculadorNombre').value = aliado.vinculadorNombre || '';
            document.getElementById('aliadoVinculadorTelefono').value = aliado.vinculadorTelefono || '';
            document.getElementById('aliadoVinculadorCorreo').value = aliado.vinculadorCorreo || '';
            document.getElementById('aliadoDireccion').value = aliado.direccion || '';
            document.getElementById('aliadoEstado').value = aliado.estado || 'activo';
            
            if (aliado.orientadora && aliado.orientadora.id) {
                document.getElementById('aliadoOrientadora').value = aliado.orientadora.id;
            }

            document.querySelector('#aliadoModal h2').textContent = '🤝 Editar Aliado';
            openAliadoModal();
            loadOrientadorasForAliado();
        } else {
            alert('Error al cargar el aliado: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar el aliado');
    }
}

// Eliminar aliado
async function eliminarAliado(id) {
    if (!confirm('¿Está seguro de eliminar este aliado? Esta acción no se puede deshacer.')) {
        return;
    }

    try {
        const response = await fetch('api/aliados/eliminar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();

        if (result.success) {
            alert('Aliado eliminado exitosamente');
            loadAliados(currentAliadosPage);
            loadAliadosStats();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar el aliado');
    }
}

// Ver detalle de aliado
async function viewAliadoDetail(id) {
    try {
        const response = await fetch(`api/aliados/obtener.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            renderAliadoDetail(result.data);
            showAliadosDetailView();
        } else {
            alert('Error al cargar el detalle: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar el detalle');
    }
}

// Renderizar detalle del aliado
function renderAliadoDetail(aliado) {
    const detailContent = document.getElementById('aliadoDetailContent');
    
    detailContent.innerHTML = `
        <div class="detail-section">
            <h3>Información General</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>ID:</label>
                    <span>${aliado.id}</span>
                </div>
                <div class="detail-item">
                    <label>Nombre:</label>
                    <span><strong>${aliado.nombre || 'N/A'}</strong></span>
                </div>
                <div class="detail-item">
                    <label>Estado:</label>
                    <span>${formatEstadoAliado(aliado.estado)}</span>
                </div>
                <div class="detail-item full-width">
                    <label>Descripción:</label>
                    <span>${aliado.descripcion || 'N/A'}</span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Información de Contacto</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Persona de Contacto:</label>
                    <span>${aliado.contactoNombre || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Cargo:</label>
                    <span>${aliado.contactoCargo || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Teléfono:</label>
                    <span>${aliado.contactoTelefono || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Correo:</label>
                    <span>${aliado.contactoCorreo || 'N/A'}</span>
                </div>
                <div class="detail-item full-width">
                    <label>Dirección:</label>
                    <span>${aliado.direccion || 'N/A'}</span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Información del Vinculador</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Nombre:</label>
                    <span>${aliado.vinculadorNombre || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Teléfono:</label>
                    <span>${aliado.vinculadorTelefono || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Correo:</label>
                    <span>${aliado.vinculadorCorreo || 'N/A'}</span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Información de Registro</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Orientadora:</label>
                    <span>${aliado.orientadora ? aliado.orientadora.nombre : 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Fecha de Registro:</label>
                    <span>${formatDateTime(aliado.createdAt)}</span>
                </div>
                <div class="detail-item">
                    <label>Última Actualización:</label>
                    <span>${formatDateTime(aliado.updatedAt)}</span>
                </div>
            </div>
        </div>

        <div class="detail-actions">
            <button class="btn-action btn-edit" onclick="editarAliado(${aliado.id})">
                ✏️ Editar
            </button>
            <button class="btn-action btn-delete" onclick="eliminarAliado(${aliado.id})">
                🗑️ Eliminar
            </button>
        </div>
    `;
}

// Abrir modal
function openAliadoModal() {
    document.getElementById('aliadoModal').classList.add('active');
}

// Cerrar modal
function closeAliadoModal() {
    document.getElementById('aliadoModal').classList.remove('active');
}

// Cargar orientadoras para selector
async function loadOrientadorasForAliado() {
    try {
        const response = await fetch('api/orientadoras/listar.php?all=true');
        const result = await response.json();

        if (result.success) {
            const select = document.getElementById('aliadoOrientadora');
            select.innerHTML = '<option value="">Seleccione...</option>';
            result.data.forEach(orientadora => {
                select.innerHTML += `<option value="${orientadora.id}">${orientadora.nombre}</option>`;
            });
        }
    } catch (error) {
        console.error('Error al cargar orientadoras:', error);
    }
}

// Manejar envío del formulario de aliado
document.addEventListener('DOMContentLoaded', function () {
    const aliadoForm = document.getElementById('aliadoForm');
    if (aliadoForm) {
        aliadoForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = {
                id: document.getElementById('aliadoId').value || null,
                nombre: document.getElementById('aliadoNombre').value,
                descripcion: document.getElementById('aliadoDescripcion').value,
                contactoNombre: document.getElementById('aliadoContactoNombre').value,
                contactoCargo: document.getElementById('aliadoContactoCargo').value,
                contactoTelefono: document.getElementById('aliadoContactoTelefono').value,
                contactoCorreo: document.getElementById('aliadoContactoCorreo').value,
                vinculadorNombre: document.getElementById('aliadoVinculadorNombre').value,
                vinculadorTelefono: document.getElementById('aliadoVinculadorTelefono').value,
                vinculadorCorreo: document.getElementById('aliadoVinculadorCorreo').value,
                direccion: document.getElementById('aliadoDireccion').value,
                estado: document.getElementById('aliadoEstado').value,
                orientadoraId: document.getElementById('aliadoOrientadora').value || null
            };

            try {
                const response = await fetch('api/aliados/guardar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    closeAliadoModal();
                    loadAliados(currentAliadosPage);
                    loadAliadosStats();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al guardar el aliado');
            }
        });
    }
});

// Cargar estadísticas de aliados para el dashboard
async function loadAliadosStats() {
    try {
        // Total de aliados activos
        const responseActivos = await fetch('api/aliados/activos.php');
        const resultActivos = await responseActivos.json();
        
        if (resultActivos.success) {
            const totalActivos = resultActivos.data.length;
            document.getElementById('totalAliadosActivos').textContent = totalActivos;
        }

        // Total de programas de aliados
        const responseProgramas = await fetch('api/programas/listar.php?esPropio=false&all=true');
        const resultProgramas = await responseProgramas.json();
        
        if (resultProgramas.success) {
            const totalProgramas = resultProgramas.pagination.total;
            document.getElementById('totalProgramasAliados').textContent = totalProgramas;
        }
    } catch (error) {
        console.error('Error al cargar estadísticas de aliados:', error);
    }
}

// Helper: Formatear estado de aliado
function formatEstadoAliado(estado) {
    if (estado === 'activo') {
        return '<span class="badge badge-aliado-activo">✅ Activo</span>';
    } else {
        return '<span class="badge badge-aliado-inactivo">❌ Inactivo</span>';
    }
}

// ========================================
// GESTIÓN DE PROGRAMAS
// ========================================

// Variables globales para Programas
let currentProgramasPage = 1;
let programasPerPage = 25;
let programasFilters = { search: '', esPropio: '', aliadoId: '', estado: '' };

// Abrir pantalla de programas
function openProgramasScreen() {
    const programasScreen = document.getElementById('programas-screen');
    const infoSection = document.querySelector('.info-section');

    programasScreen.classList.add('active');
    infoSection.classList.add('madres-active');
    showProgramasListView();
    loadProgramas(1);
    setupProgramasFilters();
    loadAliadosForFilter();
}

// Cerrar pantalla de programas
function closeProgramasScreen() {
    const programasScreen = document.getElementById('programas-screen');
    const infoSection = document.querySelector('.info-section');

    programasScreen.classList.remove('active');
    infoSection.classList.remove('madres-active');
}

// Mostrar vista de lista
function showProgramasListView() {
    document.getElementById('programas-list-view').classList.add('active');
    document.getElementById('programas-detail-view').classList.remove('active');
}

// Mostrar vista de detalle
function showProgramasDetailView() {
    document.getElementById('programas-list-view').classList.remove('active');
    document.getElementById('programas-detail-view').classList.add('active');
}

// Cargar programas con paginación y filtros
async function loadProgramas(page = 1) {
    currentProgramasPage = page;
    programasPerPage = document.getElementById('programasPerPage')?.value || 25;

    const params = new URLSearchParams({
        page: currentProgramasPage,
        per_page: programasPerPage,
        search: programasFilters.search || '',
        estado: programasFilters.estado || ''
    });

    if (programasFilters.esPropio !== '') {
        params.append('esPropio', programasFilters.esPropio);
    }
    if (programasFilters.aliadoId) {
        params.append('aliadoId', programasFilters.aliadoId);
    }

    try {
        const response = await fetch(`api/programas/listar.php?${params}`);
        const result = await response.json();

        if (result.success) {
            renderProgramasTable(result.data);
            renderProgramasPagination(result.pagination);
        } else {
            console.error('Error al cargar programas:', result.error);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Renderizar tabla de programas
function renderProgramasTable(programas) {
    const tbody = document.getElementById('programasTableBody');

    if (!programas || programas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="td-center">No se encontraron programas</td></tr>';
        return;
    }

    tbody.innerHTML = programas.map(programa => `
        <tr>
            <td>${programa.id}</td>
            <td><strong>${programa.nombre || 'N/A'}</strong></td>
            <td>${formatTipoPrograma(programa.esPropio)}</td>
            <td>${programa.aliado ? programa.aliado.nombre : 'N/A'}</td>
            <td>${programa.responsableNombre || 'N/A'}</td>
            <td>${formatEstadoPrograma(programa.estado)}</td>
            <td><span class="badge">${programa.totalMadres || 0}</span></td>
            <td>
                <button class="btn-action btn-view" onclick="viewProgramaDetail(${programa.id})" title="Ver detalle">
                    👁️
                </button>
                <button class="btn-action btn-primary" onclick="openSesionesFormacionModal(${programa.id}, '${(programa.nombre || '').replace(/'/g, "\\'")}')" title="Sesiones de Formación">
                    📚
                </button>
                <button class="btn-action btn-edit" onclick="editarPrograma(${programa.id})" title="Editar">
                    ✏️
                </button>
                <button class="btn-action btn-delete" onclick="eliminarPrograma(${programa.id})" title="Eliminar">
                    🗑️
                </button>
            </td>
        </tr>
    `).join('');
}

// Renderizar paginación
function renderProgramasPagination(pagination) {
    const pageCount = document.getElementById('programasPageCount');
    const pageButtons = document.getElementById('programasPageButtons');

    if (pageCount) {
        pageCount.textContent = `${pagination.total} registros`;
    }

    if (pageButtons) {
        let buttonsHTML = '';
        
        // Botón anterior
        buttonsHTML += `<button class="page-btn" ${currentProgramasPage === 1 ? 'disabled' : ''} 
                        onclick="loadProgramas(${currentProgramasPage - 1})">←</button>`;
        
        // Botones de páginas
        for (let i = 1; i <= pagination.totalPages; i++) {
            if (i === 1 || i === pagination.totalPages || (i >= currentProgramasPage - 1 && i <= currentProgramasPage + 1)) {
                buttonsHTML += `<button class="page-btn ${i === currentProgramasPage ? 'active' : ''}" 
                                onclick="loadProgramas(${i})">${i}</button>`;
            } else if (i === currentProgramasPage - 2 || i === currentProgramasPage + 2) {
                buttonsHTML += '<span class="page-ellipsis">...</span>';
            }
        }
        
        // Botón siguiente
        buttonsHTML += `<button class="page-btn" ${currentProgramasPage === pagination.totalPages ? 'disabled' : ''} 
                        onclick="loadProgramas(${currentProgramasPage + 1})">→</button>`;
        
        pageButtons.innerHTML = buttonsHTML;
    }
}

// Configurar filtros
function setupProgramasFilters() {
    const searchInput = document.getElementById('searchProgramas');
    const tipoFilter = document.getElementById('filterTipoPrograma');
    const aliadoFilter = document.getElementById('filterAliadoPrograma');
    const estadoFilter = document.getElementById('filterEstadoPrograma');
    const perPageSelect = document.getElementById('programasPerPage');

    if (searchInput && !searchInput.dataset.listenerAttached) {
        searchInput.addEventListener('input', debounce(function() {
            programasFilters.search = this.value;
            loadProgramas(1);
        }, 500));
        searchInput.dataset.listenerAttached = 'true';
    }

    if (tipoFilter && !tipoFilter.dataset.listenerAttached) {
        tipoFilter.addEventListener('change', function() {
            programasFilters.esPropio = this.value;
            loadProgramas(1);
        });
        tipoFilter.dataset.listenerAttached = 'true';
    }

    if (aliadoFilter && !aliadoFilter.dataset.listenerAttached) {
        aliadoFilter.addEventListener('change', function() {
            programasFilters.aliadoId = this.value;
            loadProgramas(1);
        });
        aliadoFilter.dataset.listenerAttached = 'true';
    }

    if (estadoFilter && !estadoFilter.dataset.listenerAttached) {
        estadoFilter.addEventListener('change', function() {
            programasFilters.estado = this.value;
            loadProgramas(1);
        });
        estadoFilter.dataset.listenerAttached = 'true';
    }

    if (perPageSelect && !perPageSelect.dataset.listenerAttached) {
        perPageSelect.addEventListener('change', function() {
            loadProgramas(1);
        });
        perPageSelect.dataset.listenerAttached = 'true';
    }
}

// Limpiar filtros
function clearProgramasFilters() {
    programasFilters = { search: '', esPropio: '', aliadoId: '', estado: '' };
    document.getElementById('searchProgramas').value = '';
    document.getElementById('filterTipoPrograma').value = '';
    document.getElementById('filterAliadoPrograma').value = '';
    document.getElementById('filterEstadoPrograma').value = '';
    loadProgramas(1);
}

// Cargar aliados activos para el filtro
async function loadAliadosForFilter() {
    try {
        const response = await fetch('api/aliados/activos.php');
        const result = await response.json();

        if (result.success) {
            const select = document.getElementById('filterAliadoPrograma');
            const currentValue = select.value;
            select.innerHTML = '<option value="">Todos los aliados</option>';
            result.data.forEach(aliado => {
                select.innerHTML += `<option value="${aliado.id}">${aliado.nombre}</option>`;
            });
            select.value = currentValue;
        }
    } catch (error) {
        console.error('Error al cargar aliados:', error);
    }
}

// Abrir modal para agregar programa
function agregarPrograma() {
    document.getElementById('programaId').value = '';
    document.getElementById('programaForm').reset();
    document.getElementById('programaEsPropio').value = 'true';
    document.getElementById('programaAliadoContainer').style.display = 'none';
    document.querySelector('#programaModal h2').textContent = '📚 Registrar Programa';
    openProgramaModal();
    loadAliadosActivos();
}

// Editar programa
async function editarPrograma(id) {
    try {
        const response = await fetch(`api/programas/obtener.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            const programa = result.data;
            document.getElementById('programaId').value = programa.id;
            document.getElementById('programaNombre').value = programa.nombre || '';
            document.getElementById('programaDescripcion').value = programa.descripcion || '';
            document.getElementById('programaEsPropio').value = programa.esPropio ? 'true' : 'false';
            document.getElementById('programaResponsableNombre').value = programa.responsableNombre || '';
            document.getElementById('programaResponsableCargo').value = programa.responsableCargo || '';
            document.getElementById('programaResponsableTelefono').value = programa.responsableTelefono || '';
            document.getElementById('programaResponsableCorreo').value = programa.responsableCorreo || '';
            document.getElementById('programaFechaInicio').value = programa.fechaInicio || '';
            document.getElementById('programaFechaFin').value = programa.fechaFin || '';
            document.getElementById('programaEstado').value = programa.estado || 'activo';

            // Mostrar/ocultar selector de aliado
            if (programa.esPropio) {
                document.getElementById('programaAliadoContainer').style.display = 'none';
            } else {
                document.getElementById('programaAliadoContainer').style.display = 'block';
                if (programa.aliado && programa.aliado.id) {
                    document.getElementById('programaAliado').value = programa.aliado.id;
                }
            }

            document.querySelector('#programaModal h2').textContent = '📚 Editar Programa';
            openProgramaModal();
            loadAliadosActivos();
        } else {
            alert('Error al cargar el programa: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar el programa');
    }
}

// Eliminar programa
async function eliminarPrograma(id) {
    if (!confirm('¿Está seguro de eliminar este programa? Esta acción no se puede deshacer.')) {
        return;
    }

    try {
        const response = await fetch('api/programas/eliminar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();

        if (result.success) {
            alert('Programa eliminado exitosamente');
            loadProgramas(currentProgramasPage);
            loadProgramasStats();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar el programa');
    }
}

// Ver detalle de programa
async function viewProgramaDetail(id) {
    try {
        const response = await fetch(`api/programas/obtener.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            renderProgramaDetail(result.data);
            showProgramasDetailView();
        } else {
            alert('Error al cargar el detalle: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar el detalle');
    }
}

// Renderizar detalle del programa
function renderProgramaDetail(programa) {
    const detailContent = document.getElementById('programaDetailContent');
    
    detailContent.innerHTML = `
        <div class="detail-section">
            <h3>Información General</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>ID:</label>
                    <span>${programa.id}</span>
                </div>
                <div class="detail-item">
                    <label>Nombre:</label>
                    <span><strong>${programa.nombre || 'N/A'}</strong></span>
                </div>
                <div class="detail-item">
                    <label>Tipo:</label>
                    <span>${formatTipoPrograma(programa.esPropio)}</span>
                </div>
                <div class="detail-item">
                    <label>Estado:</label>
                    <span>${formatEstadoPrograma(programa.estado)}</span>
                </div>
                <div class="detail-item full-width">
                    <label>Descripción:</label>
                    <span>${programa.descripcion || 'N/A'}</span>
                </div>
            </div>
        </div>

        ${programa.aliado ? `
        <div class="detail-section">
            <h3>Aliado</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Nombre:</label>
                    <span><strong>${programa.aliado.nombre}</strong></span>
                </div>
                <div class="detail-item">
                    <label>Contacto:</label>
                    <span>${programa.aliado.contactoNombre || 'N/A'}</span>
                </div>
            </div>
        </div>
        ` : ''}

        <div class="detail-section">
            <h3>Responsable del Programa</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Nombre:</label>
                    <span>${programa.responsableNombre || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Cargo:</label>
                    <span>${programa.responsableCargo || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Teléfono:</label>
                    <span>${programa.responsableTelefono || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Correo:</label>
                    <span>${programa.responsableCorreo || 'N/A'}</span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Fechas</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Fecha Inicio:</label>
                    <span>${programa.fechaInicio || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Fecha Fin:</label>
                    <span>${programa.fechaFin || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Fecha de Registro:</label>
                    <span>${formatDateTime(programa.createdAt)}</span>
                </div>
                <div class="detail-item">
                    <label>Última Actualización:</label>
                    <span>${formatDateTime(programa.updatedAt)}</span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Madres Inscritas</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Total:</label>
                    <span class="badge">${programa.totalMadres || 0}</span>
                </div>
            </div>
        </div>

        <div class="detail-actions">
            <button class="btn-action btn-primary" onclick="openSesionesFormacionModal(${programa.id}, '${programa.nombre.replace(/'/g, "\\'")}')">
                📚 Sesiones de Formación
            </button>
            <button class="btn-action btn-edit" onclick="editarPrograma(${programa.id})">
                ✏️ Editar
            </button>
            <button class="btn-action btn-delete" onclick="eliminarPrograma(${programa.id})">
                🗑️ Eliminar
            </button>
        </div>
    `;
}

// Abrir modal
function openProgramaModal() {
    document.getElementById('programaModal').classList.add('active');
}

// Cerrar modal
function closeProgramaModal() {
    document.getElementById('programaModal').classList.remove('active');
}

// Toggle selector de aliado según tipo
function toggleAliadoSelector() {
    const esPropio = document.getElementById('programaEsPropio').value === 'true';
    const aliadoContainer = document.getElementById('programaAliadoContainer');
    
    if (esPropio) {
        aliadoContainer.style.display = 'none';
        document.getElementById('programaAliado').value = '';
    } else {
        aliadoContainer.style.display = 'block';
    }
}

// Cargar aliados activos para selector
async function loadAliadosActivos() {
    try {
        const response = await fetch('api/aliados/activos.php');
        const result = await response.json();

        if (result.success) {
            const select = document.getElementById('programaAliado');
            const currentValue = select.value;
            select.innerHTML = '<option value="">Seleccione...</option>';
            result.data.forEach(aliado => {
                select.innerHTML += `<option value="${aliado.id}">${aliado.nombre}</option>`;
            });
            if (currentValue) {
                select.value = currentValue;
            }
        }
    } catch (error) {
        console.error('Error al cargar aliados:', error);
    }
}

// Manejar envío del formulario de programa
document.addEventListener('DOMContentLoaded', function () {
    const programaForm = document.getElementById('programaForm');
    if (programaForm) {
        programaForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const esPropio = document.getElementById('programaEsPropio').value === 'true';
            const aliadoId = document.getElementById('programaAliado').value;

            // Validación
            if (!esPropio && !aliadoId) {
                alert('Debe seleccionar un aliado para programas externos');
                return;
            }

            const formData = {
                id: document.getElementById('programaId').value || null,
                nombre: document.getElementById('programaNombre').value,
                descripcion: document.getElementById('programaDescripcion').value,
                esPropio: esPropio,
                aliadoId: esPropio ? null : (aliadoId || null),
                responsableNombre: document.getElementById('programaResponsableNombre').value,
                responsableCargo: document.getElementById('programaResponsableCargo').value,
                responsableTelefono: document.getElementById('programaResponsableTelefono').value,
                responsableCorreo: document.getElementById('programaResponsableCorreo').value,
                fechaInicio: document.getElementById('programaFechaInicio').value || null,
                fechaFin: document.getElementById('programaFechaFin').value || null,
                estado: document.getElementById('programaEstado').value
            };

            try {
                const response = await fetch('api/programas/guardar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    closeProgramaModal();
                    loadProgramas(currentProgramasPage);
                    loadProgramasStats();
                    loadAliadosStats();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al guardar el programa');
            }
        });
    }
});

// Cargar estadísticas de programas para el dashboard
async function loadProgramasStats() {
    try {
        // Total de programas activos
        const responseProgramas = await fetch('api/programas/activos.php');
        const resultProgramas = await responseProgramas.json();
        
        if (resultProgramas.success) {
            const totalProgramas = resultProgramas.data.length;
            document.getElementById('totalProgramasActivos').textContent = totalProgramas;
        }

        // Total de madres en programas
        const responseMadres = await fetch('api/madres_programas/listar.php?all=true');
        const resultMadres = await responseMadres.json();
        
        if (resultMadres.success) {
            const totalMadres = resultMadres.pagination.total;
            document.getElementById('totalMadresEnProgramas').textContent = totalMadres;
        }
    } catch (error) {
        console.error('Error al cargar estadísticas de programas:', error);
    }
}

// Helper: Formatear tipo de programa
function formatTipoPrograma(esPropio) {
    if (esPropio) {
        return '<span class="badge badge-programa-propio">🏢 Propio</span>';
    } else {
        return '<span class="badge badge-programa-aliado">🤝 Aliado</span>';
    }
}

// Helper: Formatear estado de programa
function formatEstadoPrograma(estado) {
    const badges = {
        'activo': '<span class="badge badge-programa-activo">✅ Activo</span>',
        'inactivo': '<span class="badge badge-programa-inactivo">❌ Inactivo</span>',
        'finalizado': '<span class="badge badge-programa-finalizado">🏁 Finalizado</span>'
    };
    return badges[estado] || estado;
}

// ========================================
// GESTIÓN DE ORIENTADORAS
// ========================================

// Variables globales para orientadoras
let orientadorasCurrentPage = 1;
let orientadorasPerPage = 25;
let orientadorasFilters = {
    search: '',
    activa: ''
};

// Funciones de navegación
function openOrientadorasScreen() {
    const orientadorasScreen = document.getElementById('orientadoras-screen');
    const infoSection = document.querySelector('.info-section');

    if (orientadorasScreen && infoSection) {
        orientadorasScreen.classList.add('active');
        infoSection.classList.add('module-active');
        loadOrientadoras();
        setupOrientadorasFilters();
    }
}

function closeOrientadorasScreen() {
    const orientadorasScreen = document.getElementById('orientadoras-screen');
    const infoSection = document.querySelector('.info-section');

    if (orientadorasScreen && infoSection) {
        orientadorasScreen.classList.remove('active');
        infoSection.classList.remove('module-active');
    }
}

// Funciones de carga de datos
async function loadOrientadoras() {
    try {
        const params = new URLSearchParams({
            page: orientadorasCurrentPage,
            limit: orientadorasPerPage,
            search: orientadorasFilters.search,
            activa: orientadorasFilters.activa
        });

        const response = await fetch(`api/orientadoras/listar.php?${params}`);
        const result = await response.json();

        if (result.success) {
            renderOrientadorasTable(result.data);
            renderOrientadorasPagination(result.pagination);
        } else {
            console.error('Error al cargar orientadoras');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function loadOrientadorasStats() {
    try {
        const response = await fetch('api/orientadoras/estadisticas.php');
        const result = await response.json();

        if (result.success) {
            if (document.getElementById('totalOrientadoras')) {
                document.getElementById('totalOrientadoras').textContent = result.data.total || 0;
            }
            if (document.getElementById('madresAtendidasPorOrientadoras')) {
                document.getElementById('madresAtendidasPorOrientadoras').textContent = result.data.madresAtendidas || 0;
            }
        }
    } catch (error) {
        console.error('Error loading orientadoras stats:', error);
    }
}

// Funciones de renderizado
function renderOrientadorasTable(orientadoras) {
    const tbody = document.getElementById('orientadorasTableBody');

    if (!tbody) return;

    if (orientadoras.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 30px; color: #9ca3af;">No hay orientadoras registradas</td></tr>';
        return;
    }

    let html = '';
    orientadoras.forEach(o => {
        const estadoBadge = o.activa
            ? '<span class="badge-active" style="background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Activa</span>'
            : '<span class="badge-inactive" style="background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Inactiva</span>';

        html += `
            <tr style="border-bottom: 1px solid #f3f4f6;">
                <td style="padding: 16px;">${o.id}</td>
                <td style="padding: 16px; font-weight: 500;">${o.nombre}</td>
                <td style="padding: 16px; text-align: center;">${estadoBadge}</td>
                <td style="padding: 16px; text-align: center;">${formatDate(o.createdAt || o.created_at)}</td>
                <td style="padding: 16px; text-align: center;">
                    <button onclick="verHistorialOrientadora(${o.id}, '${o.nombre}')" title="Ver Historial de Madres"
                            style="background: #f0fdf4; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px;">
                        📋
                    </button>
                    <button onclick="editOrientadora(${o.id})" title="Editar"
                            style="background: #eff6ff; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px;">
                        ✏️
                    </button>
                    <button onclick="deleteOrientadora(${o.id})" title="Eliminar"
                            style="background: #fef2f2; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;">
                        🗑️
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function renderOrientadorasPagination(pagination) {
    const container = document.getElementById('orientadorasPagination');
    if (!container || !pagination) return;

    let html = '';

    // Botón anterior
    if (pagination.page > 1) {
        html += `<button onclick="cambiarPaginaOrientadoras(${pagination.page - 1})"
                         style="padding: 8px 16px; background: white; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;">
                    ← Anterior
                </button>`;
    }

    // Números de página
    html += `<span style="padding: 8px 16px;">
        Página ${pagination.page} de ${pagination.pages} (Total: ${pagination.total})
    </span>`;

    // Botón siguiente
    if (pagination.page < pagination.pages) {
        html += `<button onclick="cambiarPaginaOrientadoras(${pagination.page + 1})"
                         style="padding: 8px 16px; background: white; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;">
                    Siguiente →
                </button>`;
    }

    container.innerHTML = html;
}

function cambiarPaginaOrientadoras(page) {
    orientadorasCurrentPage = page;
    loadOrientadoras();
}

// Funciones de modal y CRUD
function openOrientadoraModal() {
    const form = document.getElementById('orientadoraForm');
    if (form) {
        form.reset();
        document.getElementById('orientadoraId').value = '';
        document.getElementById('orientadoraModalTitle').textContent = '👩‍💼 Nueva Orientadora';
        document.getElementById('orientadoraActiva').checked = true;
        document.getElementById('orientadoraModal').classList.add('active');
    }
}

function closeOrientadoraModal() {
    const modal = document.getElementById('orientadoraModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

async function editOrientadora(id) {
    try {
        const response = await fetch(`api/orientadoras/obtener.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            const o = result.data;
            document.getElementById('orientadoraId').value = o.id;
            document.getElementById('orientadoraNombre').value = o.nombre;
            document.getElementById('orientadoraActiva').checked = o.activa;
            document.getElementById('orientadoraModalTitle').textContent = '👩‍💼 Editar Orientadora';
            document.getElementById('orientadoraModal').classList.add('active');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar orientadora');
    }
}

async function guardarOrientadora(event) {
    event.preventDefault();

    const formData = {
        id: document.getElementById('orientadoraId').value || null,
        nombre: document.getElementById('orientadoraNombre').value.trim(),
        activa: document.getElementById('orientadoraActiva').checked
    };

    if (!formData.nombre) {
        alert('El nombre es requerido');
        return;
    }

    try {
        const response = await fetch('api/orientadoras/guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            closeOrientadoraModal();
            loadOrientadoras();
            loadOrientadorasStats();
            alert(result.message);
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

async function deleteOrientadora(id) {
    if (!confirm('¿Está seguro de eliminar esta orientadora?')) return;

    try {
        const response = await fetch('api/orientadoras/eliminar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const result = await response.json();

        if (result.success) {
            loadOrientadoras();
            loadOrientadorasStats();
            alert('Orientadora eliminada correctamente');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

// Funciones de filtros
function setupOrientadorasFilters() {
    if (window.orientadorasFiltersSetup) return;
    window.orientadorasFiltersSetup = true;

    const searchInput = document.getElementById('searchOrientadora');
    const activaSelect = document.getElementById('filterOrientadoraActiva');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            orientadorasFilters.search = e.target.value;
            orientadorasCurrentPage = 1;
            loadOrientadoras();
        });
    }

    if (activaSelect) {
        activaSelect.addEventListener('change', (e) => {
            orientadorasFilters.activa = e.target.value;
            orientadorasCurrentPage = 1;
            loadOrientadoras();
        });
    }
}

function clearOrientadorasFilters() {
    const searchInput = document.getElementById('searchOrientadora');
    const activaSelect = document.getElementById('filterOrientadoraActiva');

    if (searchInput) searchInput.value = '';
    if (activaSelect) activaSelect.value = '';

    orientadorasFilters = { search: '', activa: '' };
    orientadorasCurrentPage = 1;
    loadOrientadoras();
}

// ========================================
// HISTORIAL DE ORIENTADORA-MADRE
// ========================================

let historialOrientadoraData = [];
let historialOrientadoraFiltro = 'todas';

// Abrir modal de historial
async function verHistorialOrientadora(orientadoraId, orientadoraNombre) {
    const modal = document.getElementById('historialOrientadoraModal');
    document.getElementById('orientadoraNombreHistorial').textContent = orientadoraNombre;

    modal.classList.add('active');

    // Reset tabs
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('tabTodasMadres').classList.add('active');
    historialOrientadoraFiltro = 'todas';

    // Cargar datos
    await loadHistorialOrientadora(orientadoraId);
}

// Cerrar modal
function closeHistorialOrientadoraModal() {
    const modal = document.getElementById('historialOrientadoraModal');
    modal.classList.remove('active');
    historialOrientadoraData = [];
}

// Cargar historial desde API
async function loadHistorialOrientadora(orientadoraId) {
    const tbody = document.getElementById('historialOrientadoraTableBody');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px;">Cargando historial...</td></tr>';

    try {
        const response = await fetch(`api/orientadoras/historial.php?orientadoraId=${orientadoraId}`);
        const result = await response.json();

        if (result.success) {
            historialOrientadoraData = result.data;

            // Actualizar contadores en tabs
            document.getElementById('countTodasMadres').textContent = result.estadisticas.madresHistoricas;
            document.getElementById('countActivasMadres').textContent = result.estadisticas.madresActivas;
            document.getElementById('countInactivasMadres').textContent = result.estadisticas.madresInactivas;

            // Renderizar tabla
            renderHistorialOrientadoraTable(historialOrientadoraData);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 30px; color: #dc2626;">Error: ${result.error}</td></tr>`;
        }
    } catch (error) {
        console.error('Error cargando historial:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #dc2626;">Error de conexión</td></tr>';
    }
}

// Renderizar tabla de historial
function renderHistorialOrientadoraTable(data) {
    const tbody = document.getElementById('historialOrientadoraTableBody');

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #9ca3af;">No hay registros para mostrar</td></tr>';
        return;
    }

    let html = '';
    data.forEach(asignacion => {
        const estadoBadge = asignacion.activa
            ? '<span style="background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">✅ Activa</span>'
            : '<span style="background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">⏸️ Finalizada</span>';

        const fechaFin = asignacion.fechaFin ? formatFechaHistorial(asignacion.fechaFin) : '-';
        const duracion = asignacion.duracionDias + ' días';

        html += `
            <tr style="border-bottom: 1px solid #f3f4f6;">
                <td style="padding: 12px;">${asignacion.madreNombre}</td>
                <td style="padding: 12px;">${asignacion.madreDocumento || 'N/A'}</td>
                <td style="padding: 12px;">${asignacion.madreTelefono || 'N/A'}</td>
                <td style="padding: 12px;">${formatFechaHistorial(asignacion.fechaAsignacion)}</td>
                <td style="padding: 12px;">${fechaFin}</td>
                <td style="padding: 12px;">${duracion}</td>
                <td style="padding: 12px; text-align: center;">${estadoBadge}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

// Filtrar historial por estado
function filtrarHistorialOrientadora(filtro) {
    // Actualizar tabs
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    if (filtro === 'todas') {
        document.getElementById('tabTodasMadres').classList.add('active');
    } else if (filtro === 'activas') {
        document.getElementById('tabActivasMadres').classList.add('active');
    } else if (filtro === 'inactivas') {
        document.getElementById('tabInactivasMadres').classList.add('active');
    }

    historialOrientadoraFiltro = filtro;

    // Filtrar datos
    let dataFiltrada = historialOrientadoraData;

    if (filtro === 'activas') {
        dataFiltrada = historialOrientadoraData.filter(a => a.activa);
    } else if (filtro === 'inactivas') {
        dataFiltrada = historialOrientadoraData.filter(a => !a.activa);
    }

    renderHistorialOrientadoraTable(dataFiltrada);
}

// Helper: Formatear fecha para historial
function formatFechaHistorial(dateString) {
    if (!dateString) return '-';
    const parts = dateString.split('-');
    if (parts.length !== 3) return dateString;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

// Event listener para el formulario
if (document.getElementById('orientadoraForm')) {
    document.getElementById('orientadoraForm').addEventListener('submit', guardarOrientadora);
}

// Cerrar modal al hacer clic fuera
if (document.getElementById('orientadoraModal')) {
    document.getElementById('orientadoraModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeOrientadoraModal();
        }
    });
}

// ========================================
// MÓDULO DE REPORTES
// ========================================

// Abrir modal de reportes
function openReportesModal(event) {
    if (event) {
        event.preventDefault();
    }
    const modal = document.getElementById('reportesModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Cerrar modal de reportes
function closeReportesModal() {
    const modal = document.getElementById('reportesModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Descargar reporte
function downloadReport(reportType) {
    const endpoints = {
        'madres_vinculadas': 'api/reportes/madres_vinculadas_excel.php',
        'madres_desvinculadas': 'api/reportes/madres_desvinculadas_excel.php',
        'ayudas_total': 'api/reportes/ayudas_total_excel.php',
        'ayudas_por_madre': 'api/reportes/ayudas_por_madre_excel.php',
        'bebes_nacidos': 'api/reportes/bebes_nacidos_excel.php',
        'bebes_por_nacer': 'api/reportes/bebes_por_nacer_excel.php',
        'hijos_registrados': 'api/reportes/hijos_registrados_excel.php',
        'hijos_por_mama': 'api/reportes/hijos_por_mama_excel.php',
        'mamas_por_eps': 'api/reportes/mamas_por_eps_excel.php',
        'mamas_por_consejera': 'api/reportes/mamas_por_consejera_excel.php',
        'mamas_por_programas': 'api/reportes/mamas_por_programas_excel.php',
        'programas_por_mamas': 'api/reportes/programas_por_mamas_excel.php'
    };

    const endpoint = endpoints[reportType];
    if (!endpoint) {
        alert('Tipo de reporte no válido');
        return;
    }

    // Mostrar indicador de carga
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner"></span> Generando...';
    btn.disabled = true;

    // Crear enlace de descarga
    const link = document.createElement('a');
    link.href = endpoint;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Restaurar botón después de un momento
    setTimeout(() => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }, 2000);
}

// Cerrar modal de reportes al hacer clic fuera
if (document.getElementById('reportesModal')) {
    document.getElementById('reportesModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeReportesModal();
        }
    });
}

// Variables globales para búsqueda de madre en reportes
let selectedMadreForReport = null;
let searchMadreTimeout = null;

// Buscar madre para reporte con autocompletado
async function searchMadreForReport(query) {
    const suggestionsContainer = document.getElementById('madreReporteSuggestions');
    const downloadBtn = document.getElementById('btnDownloadAyudasMadre');
    
    // Limpiar timeout anterior
    if (searchMadreTimeout) {
        clearTimeout(searchMadreTimeout);
    }
    
    // Si el query está vacío, ocultar sugerencias
    if (!query || query.trim().length < 2) {
        suggestionsContainer.classList.remove('active');
        suggestionsContainer.innerHTML = '';
        selectedMadreForReport = null;
        downloadBtn.disabled = true;
        return;
    }
    
    // Esperar 300ms antes de buscar (debounce)
    searchMadreTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`api/madres/buscar.php?q=${encodeURIComponent(query.trim())}`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                renderMadreSuggestions(result.data);
            } else {
                suggestionsContainer.innerHTML = '<div class="autocomplete-suggestion-item" style="color: #9ca3af; cursor: default;">No se encontraron resultados</div>';
                suggestionsContainer.classList.add('active');
            }
        } catch (error) {
            console.error('Error al buscar madres:', error);
            suggestionsContainer.classList.remove('active');
        }
    }, 300);
}

// Renderizar sugerencias de madres
function renderMadreSuggestions(madres) {
    const suggestionsContainer = document.getElementById('madreReporteSuggestions');
    
    suggestionsContainer.innerHTML = madres.map(madre => `
        <div class="autocomplete-suggestion-item" onclick="selectMadreForReport(${madre.id}, '${madre.nombreCompleto.replace(/'/g, "\\'")}')">
            <div class="suggestion-name">${madre.nombreCompleto}</div>
            <div class="suggestion-doc">${madre.tipoDocumento} ${madre.numeroDocumento}</div>
        </div>
    `).join('');
    
    suggestionsContainer.classList.add('active');
}

// Seleccionar madre para reporte
function selectMadreForReport(madreId, nombreCompleto) {
    selectedMadreForReport = madreId;
    
    const input = document.getElementById('searchMadreReporte');
    const suggestionsContainer = document.getElementById('madreReporteSuggestions');
    const downloadBtn = document.getElementById('btnDownloadAyudasMadre');
    
    input.value = nombreCompleto;
    suggestionsContainer.classList.remove('active');
    suggestionsContainer.innerHTML = '';
    downloadBtn.disabled = false;
}

// Descargar reporte de ayudas por madre específica
function downloadReportAyudasPorMadre() {
    if (!selectedMadreForReport) {
        alert('Por favor seleccione una madre de la lista');
        return;
    }
    
    const btn = document.getElementById('btnDownloadAyudasMadre');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner"></span> Generando...';
    btn.disabled = true;
    
    // Crear enlace de descarga con parámetro de madre
    const endpoint = `api/reportes/ayudas_por_madre_excel.php?madre_id=${selectedMadreForReport}`;
    const link = document.createElement('a');
    link.href = endpoint;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Restaurar botón después de un momento
    setTimeout(() => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }, 2000);
}

// Cerrar sugerencias al hacer clic fuera
document.addEventListener('click', function(e) {
    const suggestionsContainer = document.getElementById('madreReporteSuggestions');
    const searchInput = document.getElementById('searchMadreReporte');
    
    if (suggestionsContainer && searchInput) {
        if (!suggestionsContainer.contains(e.target) && e.target !== searchInput) {
            suggestionsContainer.classList.remove('active');
        }
    }
    
    const madreAsistenteSuggestions = document.getElementById('madreAsistenteSuggestions');
    const buscarMadreAsistente = document.getElementById('buscarMadreAsistente');
    if (madreAsistenteSuggestions && buscarMadreAsistente) {
        if (!madreAsistenteSuggestions.contains(e.target) && e.target !== buscarMadreAsistente) {
            madreAsistenteSuggestions.classList.remove('active');
        }
    }
});

// ========================================
// SESIONES DE FORMACIÓN
// ========================================

let currentProgramaIdSesiones = null;
let currentProgramaNombreSesiones = null;
let madresAsistentesSeleccionadas = [];
let searchMadreAsistenteTimeout = null;

function openSesionesFormacionModal(programaId, programaNombre) {
    currentProgramaIdSesiones = programaId;
    currentProgramaNombreSesiones = programaNombre;
    
    document.getElementById('sesionesFormacionProgramaNombre').textContent = programaNombre;
    document.getElementById('sesionFormProgramaId').value = programaId;
    
    const modal = document.getElementById('sesionesFormacionModal');
    if (modal) {
        modal.style.display = 'flex';
        loadSesionesFormacion(programaId);
    }
}

function closeSesionesFormacionModal() {
    const modal = document.getElementById('sesionesFormacionModal');
    if (modal) {
        modal.style.display = 'none';
    }
    cancelarSesionForm();
    currentProgramaIdSesiones = null;
    currentProgramaNombreSesiones = null;
}

async function loadSesionesFormacion(programaId) {
    try {
        const response = await fetch(`api/sesiones_formacion/por_programa.php?programaId=${programaId}`);
        const result = await response.json();

        if (result.success) {
            if (result.estadisticas) {
                document.getElementById('statDiscipulado').textContent = result.estadisticas.sesiones_discipulado || 0;
                document.getElementById('statConsejeria').textContent = result.estadisticas.sesiones_consejeria || 0;
                document.getElementById('statCapacitacion').textContent = result.estadisticas.sesiones_capacitacion || 0;
                document.getElementById('statReunion').textContent = result.estadisticas.sesiones_reunion || 0;
            }
            renderSesionesFormacionLista(result.data);
        } else {
            document.getElementById('sesionesFormacionListaContent').innerHTML = 
                '<p style="text-align: center; color: #ef4444;">Error al cargar las sesiones</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('sesionesFormacionListaContent').innerHTML = 
            '<p style="text-align: center; color: #ef4444;">Error de conexión</p>';
    }
}

function renderSesionesFormacionLista(sesiones) {
    const container = document.getElementById('sesionesFormacionListaContent');
    
    if (!sesiones || sesiones.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #9ca3af; padding: 40px;">No hay sesiones registradas para este programa</p>';
        return;
    }

    const tipoLabels = {
        'discipulado': { label: 'Discipulado', color: '#92400e', bg: '#fef3c7' },
        'consejeria': { label: 'Consejería', color: '#1e40af', bg: '#dbeafe' },
        'capacitacion': { label: 'Capacitación', color: '#166534', bg: '#dcfce7' },
        'reunion_tematica': { label: 'Reunión Temática', color: '#6b21a8', bg: '#f3e8ff' }
    };

    let html = '<div style="display: flex; flex-direction: column; gap: 12px;">';
    
    sesiones.forEach(sesion => {
        const tipo = tipoLabels[sesion.tipoSesion] || { label: sesion.tipoSesion, color: '#6b7280', bg: '#f3f4f6' };
        const fechaFormateada = formatDate(sesion.fechaSesion);
        
        html += `
            <div class="sesion-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; transition: all 0.2s ease;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: ${tipo.bg}; color: ${tipo.color};">
                            ${tipo.label}
                        </span>
                        <span style="margin-left: 12px; color: #6b7280; font-size: 14px;">
                            ${fechaFormateada}
                        </span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="editarSesionFormacion(${sesion.id})" style="background: none; border: none; cursor: pointer; color: #4A90A4; padding: 4px;" title="Editar">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button onclick="eliminarSesionFormacion(${sesion.id})" style="background: none; border: none; cursor: pointer; color: #ef4444; padding: 4px;" title="Eliminar">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div style="margin-bottom: 8px;">
                    <strong style="color: #374151;">Responsables:</strong>
                    <span style="color: #6b7280;">${sesion.responsables}</span>
                </div>
                ${sesion.temasTratados ? `
                <div style="margin-bottom: 8px;">
                    <strong style="color: #374151;">Temas:</strong>
                    <span style="color: #6b7280;">${sesion.temasTratados}</span>
                </div>
                ` : ''}
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                    </svg>
                    <span style="color: #6b7280; font-size: 14px;">
                        <strong>${sesion.totalAsistentes || 0}</strong> asistentes
                    </span>
                    ${sesion.madresAsistentes && sesion.madresAsistentes.length > 0 ? `
                    <span style="color: #9ca3af; font-size: 12px; margin-left: 8px;">
                        (${sesion.madresAsistentes.slice(0, 3).map(m => m.nombre).join(', ')}${sesion.madresAsistentes.length > 3 ? '...' : ''})
                    </span>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function openNuevaSesionForm() {
    document.getElementById('sesionFormId').value = '';
    document.getElementById('sesionFormTitle').textContent = 'Nueva Sesión de Formación';
    document.getElementById('sesionTipoSesion').value = '';
    document.getElementById('sesionFechaSesion').value = new Date().toISOString().split('T')[0];
    document.getElementById('sesionResponsables').value = '';
    document.getElementById('sesionTemasTratados').value = '';
    document.getElementById('sesionObservaciones').value = '';
    
    madresAsistentesSeleccionadas = [];
    renderMadresAsistentesSeleccionadas();
    
    document.getElementById('nuevaSesionForm').style.display = 'block';
}

function cancelarSesionForm() {
    document.getElementById('nuevaSesionForm').style.display = 'none';
    madresAsistentesSeleccionadas = [];
}

async function guardarSesionFormacion() {
    const id = document.getElementById('sesionFormId').value;
    const programaId = document.getElementById('sesionFormProgramaId').value || currentProgramaIdSesiones;
    const tipoSesion = document.getElementById('sesionTipoSesion').value;
    const fechaSesion = document.getElementById('sesionFechaSesion').value;
    const responsables = document.getElementById('sesionResponsables').value.trim();
    const temasTratados = document.getElementById('sesionTemasTratados').value.trim();
    const observaciones = document.getElementById('sesionObservaciones').value.trim();

    if (!tipoSesion) {
        alert('Seleccione el tipo de sesión');
        return;
    }
    if (!fechaSesion) {
        alert('Ingrese la fecha de la sesión');
        return;
    }
    if (!responsables) {
        alert('Ingrese los responsables de la sesión');
        return;
    }

    const data = {
        programaId: parseInt(programaId),
        tipoSesion,
        fechaSesion,
        responsables,
        temasTratados: temasTratados || null,
        observaciones: observaciones || null,
        madresAsistentes: madresAsistentesSeleccionadas.map(m => m.id)
    };

    if (id) {
        data.id = parseInt(id);
    }

    try {
        const response = await fetch('api/sesiones_formacion/guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            cancelarSesionForm();
            loadSesionesFormacion(currentProgramaIdSesiones);
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión al guardar la sesión');
    }
}

async function editarSesionFormacion(id) {
    try {
        const response = await fetch(`api/sesiones_formacion/obtener.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            const sesion = result.data;
            
            document.getElementById('sesionFormId').value = sesion.id;
            document.getElementById('sesionFormTitle').textContent = 'Editar Sesión de Formación';
            document.getElementById('sesionTipoSesion').value = sesion.tipoSesion;
            document.getElementById('sesionFechaSesion').value = sesion.fechaSesion;
            document.getElementById('sesionResponsables').value = sesion.responsables;
            document.getElementById('sesionTemasTratados').value = sesion.temasTratados || '';
            document.getElementById('sesionObservaciones').value = sesion.observaciones || '';
            
            madresAsistentesSeleccionadas = sesion.madresAsistentes || [];
            renderMadresAsistentesSeleccionadas();
            
            document.getElementById('nuevaSesionForm').style.display = 'block';
        } else {
            alert('Error al cargar la sesión: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

async function eliminarSesionFormacion(id) {
    if (!confirm('¿Está seguro de eliminar esta sesión de formación?')) {
        return;
    }

    try {
        const response = await fetch('api/sesiones_formacion/eliminar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            loadSesionesFormacion(currentProgramaIdSesiones);
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

async function buscarMadreAsistente(query) {
    const suggestionsContainer = document.getElementById('madreAsistenteSuggestions');
    
    if (searchMadreAsistenteTimeout) {
        clearTimeout(searchMadreAsistenteTimeout);
    }
    
    if (!query || query.trim().length < 2) {
        suggestionsContainer.classList.remove('active');
        return;
    }
    
    searchMadreAsistenteTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`api/madres/buscar.php?q=${encodeURIComponent(query.trim())}`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                let html = '';
                result.data.forEach(madre => {
                    const yaSeleccionada = madresAsistentesSeleccionadas.some(m => m.id === madre.id);
                    if (!yaSeleccionada) {
                        const nombreCompleto = madre.nombreCompleto || '';
                        const documento = madre.numeroDocumento || '';
                        html += `
                            <div class="autocomplete-suggestion-item" onclick="agregarMadreAsistente(${madre.id}, '${nombreCompleto.replace(/'/g, "\\'")}', '')">
                                <strong>${nombreCompleto}</strong>
                                <span style="color: #9ca3af; font-size: 12px; margin-left: 8px;">${documento}</span>
                            </div>
                        `;
                    }
                });
                
                if (html) {
                    suggestionsContainer.innerHTML = html;
                    suggestionsContainer.classList.add('active');
                } else {
                    suggestionsContainer.innerHTML = '<div class="autocomplete-suggestion-item" style="color: #9ca3af;">Todas las coincidencias ya están seleccionadas</div>';
                    suggestionsContainer.classList.add('active');
                }
            } else {
                suggestionsContainer.innerHTML = '<div class="autocomplete-suggestion-item" style="color: #9ca3af;">No se encontraron resultados</div>';
                suggestionsContainer.classList.add('active');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }, 300);
}

function agregarMadreAsistente(id, nombre, apellido) {
    if (!madresAsistentesSeleccionadas.some(m => m.id === id)) {
        madresAsistentesSeleccionadas.push({ id, nombre, apellido });
        renderMadresAsistentesSeleccionadas();
    }
    
    document.getElementById('buscarMadreAsistente').value = '';
    document.getElementById('madreAsistenteSuggestions').classList.remove('active');
}

function quitarMadreAsistente(id) {
    madresAsistentesSeleccionadas = madresAsistentesSeleccionadas.filter(m => m.id !== id);
    renderMadresAsistentesSeleccionadas();
}

function renderMadresAsistentesSeleccionadas() {
    const container = document.getElementById('madresAsistentesSeleccionadas');
    
    if (madresAsistentesSeleccionadas.length === 0) {
        container.innerHTML = '<span style="color: #9ca3af; font-size: 14px;">No hay madres seleccionadas</span>';
        return;
    }
    
    let html = '';
    madresAsistentesSeleccionadas.forEach(madre => {
        const nombreMostrar = madre.nombre + (madre.apellido ? ' ' + madre.apellido : '');
        html += `
            <span style="display: inline-flex; align-items: center; gap: 6px; background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 20px; font-size: 13px;">
                ${nombreMostrar}
                <button onclick="quitarMadreAsistente(${madre.id})" style="background: none; border: none; cursor: pointer; color: #0369a1; padding: 0; display: flex;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </span>
        `;
    });
    
    container.innerHTML = html;
}

// Cerrar modal de sesiones al hacer clic fuera
if (document.getElementById('sesionesFormacionModal')) {
    document.getElementById('sesionesFormacionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSesionesFormacionModal();
        }
    });
}
