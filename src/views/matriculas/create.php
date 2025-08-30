<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();

// Repoblar datos del formulario si existen en la sesión
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<style>
/* Estilos para el formulario de matrícula */
.form-container { max-width: 900px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.form-section { border-bottom: 1px solid #eee; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
.form-row { display: flex; flex-wrap: wrap; margin: -0.75rem; }
.form-group { flex: 1 1 350px; padding: 0.75rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.form-actions { margin-top: 1rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
#horarios-disponibles-container { margin-top: 1rem; }
.horario-item { border: 1px solid #ccc; padding: 1rem; margin-bottom: 0.5rem; border-radius: 4px; cursor: pointer; }
.horario-item:hover { background-color: #f5f5f5; }
.horario-item.selected { border-color: #337ab7; background-color: #eaf2fa; }

/* Estilos para búsqueda de alumno y curso */
.search-container { position: relative; }
.search-results {
    position: absolute;
    background-color: white;
    border: 1px solid #ccc;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
}
.search-result-item { padding: 10px; cursor: pointer; }
.search-result-item:hover { background-color: #f0f0f0; }
#nuevo-alumno-form { display: none; background-color: #f9f9f9; padding: 1rem; border-radius: 5px; margin-top: 1rem;}
.schedule-filters { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-top: 1rem;}
.error-text { color: red; font-size: 0.875em; margin-top: 0.25rem; }
</style>

<div class="form-container">
    <h2>Nueva Matrícula</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div style="padding: 1rem; margin-bottom: 1rem; border: 1px solid red; color: red; background-color: #fdd;">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form id="form-matricula" action="index.php?url=matriculas/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">

        <div class="form-section">
            <h4>1. Seleccionar o Registrar Cliente</h4>
            <div class="form-group search-container">
                <label for="alumno_search">Buscar Cliente Existente</label>
                <input type="text" id="alumno_search" class="form-control" placeholder="Escriba nombre, apellido o documento..." value="<?php echo htmlspecialchars($form_data['alumno_search'] ?? ''); ?>" autocomplete="off">
                <input type="hidden" id="id_alumno" name="id_alumno" value="<?php echo htmlspecialchars($form_data['id_alumno'] ?? ''); ?>">
                <div id="alumno-search-results" class="search-results"></div>
                <small>Si el cliente no existe, regístrelo a continuación.</small>
            </div>

            <button type="button" id="btn-show-nuevo-alumno" class="btn btn-secondary">Registrar Nuevo Cliente</button>

            <div id="nuevo-alumno-form" style="<?php echo !empty($form_data['nuevo_alumno_nombres']) ? 'display:block;' : 'display:none;'; ?>">
                <h5>Datos del Nuevo Cliente (Esenciales)</h5>
                <div class="form-row">
                    <div class="form-group"><label>Nombres:</label><input type="text" name="nuevo_alumno_nombres" value="<?php echo htmlspecialchars($form_data['nuevo_alumno_nombres'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Apellidos:</label><input type="text" name="nuevo_alumno_apellidos" value="<?php echo htmlspecialchars($form_data['nuevo_alumno_apellidos'] ?? ''); ?>"></div>
                </div>
                 <div class="form-row">
                    <div class="form-group">
                        <label>Tipo de Documento:</label>
                        <select name="nuevo_alumno_id_tipo_documento" id="nuevo_alumno_id_tipo_documento" required>
                            <?php foreach ($tipos_documento as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo['id']); ?>" data-longitud="<?php echo htmlspecialchars($tipo['longitud']); ?>" <?php echo (isset($form_data['nuevo_alumno_id_tipo_documento']) && $form_data['nuevo_alumno_id_tipo_documento'] == $tipo['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo['descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Número de Documento:</label>
                        <input type="text" name="nuevo_alumno_documento" id="nuevo_alumno_documento" value="<?php echo htmlspecialchars($form_data['nuevo_alumno_documento'] ?? ''); ?>" required>
                        <div class="error-text" id="nuevo-dni-error" style="display: none;"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Teléfono:</label><input type="text" name="nuevo_alumno_telefono" value="<?php echo htmlspecialchars($form_data['nuevo_alumno_telefono'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Email:</label><input type="email" name="nuevo_alumno_email" value="<?php echo htmlspecialchars($form_data['nuevo_alumno_email'] ?? ''); ?>"></div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h4>2. Seleccionar Curso y Horario</h4>
            <div class="form-group search-container">
                <label for="curso_search">Buscar Curso</label>
                <input type="text" id="curso_search" class="form-control" placeholder="Escriba el nombre del curso..." value="<?php echo htmlspecialchars($form_data['curso_search'] ?? ''); ?>" autocomplete="off" required>
                <input type="hidden" id="id_curso" name="id_curso" value="<?php echo htmlspecialchars($form_data['id_curso'] ?? ''); ?>" required>
                <div id="curso-search-results" class="search-results"></div>
            </div>

            <div class="schedule-filters">
                <div class="form-group">
                    <label for="filtro_profesor">Profesor</label>
                    <select id="filtro_profesor">
                        <option value="0">Todos</option>
                        <?php foreach ($profesores as $profesor): ?>
                            <option value="<?php echo htmlspecialchars($profesor['id_profesor']); ?>" <?php echo (isset($form_data['filtro_profesor']) && $form_data['filtro_profesor'] == $profesor['id_profesor']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtro_hora_inicio">Desde las:</label>
                    <input type="time" id="filtro_hora_inicio" value="<?php echo htmlspecialchars($form_data['filtro_hora_inicio'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="filtro_hora_fin">Hasta las:</label>
                    <input type="time" id="filtro_hora_fin" value="<?php echo htmlspecialchars($form_data['filtro_hora_fin'] ?? ''); ?>">
                </div>
                <button type="button" id="btn-filtrar-horarios" class="btn btn-primary">Filtrar Horarios</button>
            </div>

            <div id="horarios-disponibles-container">
                <p>Por favor, seleccione un curso.</p>
            </div>
            <input type="hidden" id="id_horario" name="id_horario" value="<?php echo htmlspecialchars($form_data['id_horario'] ?? ''); ?>" required>
            <input type="hidden" id="dias_semana_hidden" name="dias_semana_hidden" value="<?php echo htmlspecialchars($form_data['dias_semana_hidden'] ?? ''); ?>">
            <input type="hidden" id="precio_base" name="precio_base" value="<?php echo htmlspecialchars($form_data['precio_base'] ?? ''); ?>">
        </div>

        <div class="form-section">
            <h4>3. Fechas y Pago</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($form_data['fecha_inicio'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="fecha_fin">Fecha de Fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($form_data['fecha_fin'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="descuento">Descuento (S/)</label>
                    <input type="number" id="descuento" name="descuento" step="0.01" value="<?php echo htmlspecialchars($form_data['descuento'] ?? '0'); ?>">
                </div>
                 <div class="form-group">
                    <label for="precio_final">Precio Final (S/)</label>
                    <input type="number" id="precio_final" name="precio_final" step="0.01" value="<?php echo htmlspecialchars($form_data['precio_final'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="id_forma_pago">Forma de Pago</label>
                    <select id="id_forma_pago" name="id_forma_pago" required>
                        <option value="">Seleccione una forma de pago</option>
                         <?php foreach ($formas_pago as $forma): ?>
                            <option value="<?php echo htmlspecialchars($forma['id_forma_pago']); ?>" <?php echo (isset($form_data['id_forma_pago']) && $form_data['id_forma_pago'] == $forma['id_forma_pago']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($forma['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
             <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($form_data['observaciones'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php?url=matriculas" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Registrar Matrícula</button>
        </div>
    </form>
</div>

<script>
function formatDateDDMMYYYY(dateString) {
    if (!dateString) return 'No definida';
    // El formato de la DB es YYYY-MM-DD, que JS puede interpretar como UTC.
    // Para evitar que la fecha cambie por la zona horaria, la ajustamos.
    const date = new Date(dateString);
    const userTimezoneOffset = date.getTimezoneOffset() * 60000;
    const adjustedDate = new Date(date.getTime() + userTimezoneOffset);

    const day = String(adjustedDate.getDate()).padStart(2, '0');
    const month = String(adjustedDate.getMonth() + 1).padStart(2, '0'); // Los meses son base 0
    const year = adjustedDate.getFullYear();
    return `${day}/${month}/${year}`;
}

// Lógica para pre-selección desde el dashboard
const preselectedSchedule = <?php echo isset($selected_schedule) ? json_encode($selected_schedule) : 'null'; ?>;

document.addEventListener('DOMContentLoaded', function() {
    if (preselectedSchedule) {
        // Pre-llenar el campo de búsqueda de curso
        const cursoSearchInput = document.getElementById('curso_search');
        const cursoHiddenInputId = document.getElementById('id_curso');
        cursoSearchInput.value = preselectedSchedule.curso_nombre;
        cursoHiddenInputId.value = preselectedSchedule.id_curso;

        // Deshabilitar la búsqueda de curso para evitar cambios
        cursoSearchInput.disabled = true;

        // Buscar y seleccionar el horario
        buscarHorarios(function() {
            const horarioItem = document.querySelector(`.horario-item[data-id='${preselectedSchedule.id_horario}']`);
            if (horarioItem) {
                horarioItem.click();
            }
        });
    }
});

// Lógica para búsqueda de alumnos
const searchInput = document.getElementById('alumno_search');
const searchResults = document.getElementById('alumno-search-results');
const hiddenInputId = document.getElementById('id_alumno');
const nuevoAlumnoForm = document.getElementById('nuevo-alumno-form');
const nuevoAlumnoInputs = document.querySelectorAll('#nuevo-alumno-form input, #nuevo-alumno-form select');

// Función para habilitar/deshabilitar el formulario de nuevo alumno
function toggleNuevoAlumnoForm(enabled) {
    if (enabled) {
        nuevoAlumnoForm.style.display = 'block';
        nuevoAlumnoInputs.forEach(input => {
            input.disabled = false;
            // Solo añadir 'required' a los campos que realmente lo son
            if (input.name === 'nuevo_alumno_documento' || input.name === 'nuevo_alumno_id_tipo_documento') {
                input.required = true;
            }
        });
    } else {
        nuevoAlumnoForm.style.display = 'none';
        nuevoAlumnoInputs.forEach(input => {
            input.disabled = true;
            input.required = false;
            input.value = ''; // Limpiar valores
        });
    }
}

// Deshabilitar el formulario de nuevo alumno al cargar la página
toggleNuevoAlumnoForm(false);

searchInput.addEventListener('keyup', function() {
    const term = this.value;
    hiddenInputId.value = '';
    toggleNuevoAlumnoForm(false); // Deshabilitar al empezar a buscar
    if (term.length < 2) {
        searchResults.innerHTML = '';
        return;
    }
    fetch(`index.php?url=alumnos/search&term=${term}`)
        .then(response => response.json())
        .then(data => {
            searchResults.innerHTML = '';
            data.forEach(alumno => {
                const item = document.createElement('div');
                item.className = 'search-result-item';
                item.textContent = `${alumno.nombres} ${alumno.apellidos} (${alumno.documento_identidad || 'N/D'})`;
                item.dataset.id = alumno.id_alumno;
                item.addEventListener('click', function() {
                    searchInput.value = this.textContent;
                    hiddenInputId.value = this.dataset.id;
                    searchResults.innerHTML = '';
                    toggleNuevoAlumnoForm(false); // Deshabilitar al seleccionar uno existente
                });
                searchResults.appendChild(item);
            });
        });
});

document.getElementById('btn-show-nuevo-alumno').addEventListener('click', function() {
    searchInput.value = '';
    hiddenInputId.value = '';
    searchResults.innerHTML = '';
    toggleNuevoAlumnoForm(true); // Habilitar al hacer clic en registrar nuevo
});

// Lógica para búsqueda de cursos
const cursoSearchInput = document.getElementById('curso_search');
const cursoSearchResults = document.getElementById('curso-search-results');
const cursoHiddenInputId = document.getElementById('id_curso');

cursoSearchInput.addEventListener('keyup', function() {
    const term = this.value;
    cursoHiddenInputId.value = '';
    document.getElementById('horarios-disponibles-container').innerHTML = '<p>Por favor, seleccione un curso.</p>'; // Clear schedules
    if (term.length < 2) {
        cursoSearchResults.innerHTML = '';
        return;
    }
    fetch(`index.php?url=cursos/search&term=${term}`)
        .then(response => response.json())
        .then(data => {
            cursoSearchResults.innerHTML = '';
            data.forEach(curso => {
                const item = document.createElement('div');
                item.className = 'search-result-item';
                item.textContent = curso.nombre;
                item.dataset.id = curso.id_curso;
                item.dataset.tipoId = curso.id_tipo_profesor; // Guardar el id del tipo
                item.addEventListener('click', function() {
                    cursoSearchInput.value = this.textContent;
                    cursoHiddenInputId.value = this.dataset.id;
                    cursoSearchResults.innerHTML = '';

                    // Filtrar profesores y luego buscar horarios
                    filtrarProfesoresPorTipo(this.dataset.tipoId, buscarHorarios);
                });
                cursoSearchResults.appendChild(item);
            });
        });
});

function filtrarProfesoresPorTipo(id_tipo, callback) {
    const profesorSelect = document.getElementById('filtro_profesor');

    fetch(`index.php?url=profesores/getByTipo&id_tipo=${id_tipo}`)
        .then(response => response.json())
        .then(profesores => {
            profesorSelect.innerHTML = '<option value="0">Todos</option>'; // Opción por defecto
            profesores.forEach(profesor => {
                const option = document.createElement('option');
                option.value = profesor.id_profesor;
                option.textContent = `${profesor.nombres} ${profesor.apellidos}`;
                profesorSelect.appendChild(option);
            });
            // Ejecutar el callback si existe (para encadenar la búsqueda de horarios)
            if (typeof callback === 'function') {
                callback();
            }
        })
        .catch(error => console.error('Error al filtrar profesores:', error));
}


// Lógica para horarios
let precioBaseCurso = 0;

function calcularPrecioFinal() {
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    const precioFinal = precioBaseCurso - descuento;
    document.getElementById('precio_final').value = precioFinal.toFixed(2);
    document.getElementById('precio_base').value = precioBaseCurso;
}

function buscarHorarios(callback) { // Aceptar un callback
    const cursoId = document.getElementById('id_curso').value;
    const profesorId = document.getElementById('filtro_profesor').value;
    const horaInicio = document.getElementById('filtro_hora_inicio').value;
    const horaFin = document.getElementById('filtro_hora_fin').value;
    const fechaFinInput = document.getElementById('fecha_fin');

    document.getElementById('precio_final').value = '';
    document.getElementById('descuento').value = 0;
    precioBaseCurso = 0;

    const container = document.getElementById('horarios-disponibles-container');
    container.innerHTML = '<p>Cargando horarios...</p>';
    document.getElementById('id_horario').value = '';
    fechaFinInput.readOnly = false; // Desbloquear la fecha final si se busca de nuevo

    if (!cursoId) {
        container.innerHTML = '<p>Por favor, seleccione un curso.</p>';
        return;
    }

    let fetchUrl = `index.php?url=matriculas/getHorariosByCurso&id_curso=${cursoId}&id_profesor=${profesorId}`;
    if (horaInicio) fetchUrl += `&hora_inicio=${horaInicio}`;
    if (horaFin) fetchUrl += `&hora_fin=${horaFin}`;

    fetch(fetchUrl)
        .then(response => response.json())
        .then(horarios => {
            container.innerHTML = '';
            if (horarios.length === 0) {
                container.innerHTML = '<p>No se encontraron horarios con los filtros seleccionados.</p>';
            } else {
                horarios.forEach(horario => {
                    const div = document.createElement('div');
                    div.className = 'horario-item';
                    div.dataset.id = horario.id_horario;
                    // Ya no se obtiene el precio aquí, se buscará dinámicamente
                    div.dataset.dias = horario.dias_semana;
                    div.dataset.fechaInicioCurso = horario.fecha_inicio; // Guardar la fecha de inicio del curso
                    div.dataset.fechaFinCurso = horario.fecha_fin;
                    const fechaInicioFormatted = formatDateDDMMYYYY(horario.fecha_inicio);
                    const fechaFinFormatted = formatDateDDMMYYYY(horario.fecha_fin);

                    div.innerHTML = `<strong>Profesor:</strong> ${horario.profesor_nombre}<br>
                                     <strong>Periodo:</strong> ${fechaInicioFormatted} - ${fechaFinFormatted}<br>
                                     <strong>Lugar:</strong> ${horario.carril_nombre}<br>
                                     <strong>Días:</strong> ${horario.tipo_horario_nombre}<br>
                                     <strong>Horario:</strong> ${horario.hora_inicio} - ${horario.hora_fin}<br>
                                     <strong>Vacantes:</strong> ${horario.vacantes_disponibles}`;
                    div.addEventListener('click', function() {
                        document.querySelectorAll('.horario-item').forEach(item => item.classList.remove('selected'));
                        this.classList.add('selected');
                        document.getElementById('id_horario').value = this.dataset.id;
                        document.getElementById('dias_semana_hidden').value = this.dataset.dias;

                        // Lógica para auto-seleccionar fechas
                        const fechaInicioInput = document.getElementById('fecha_inicio');
                        const fechaFinInput = document.getElementById('fecha_fin');
                        const cursoInicio = this.dataset.fechaInicioCurso;
                        const cursoFin = this.dataset.fechaFinCurso;

                        if (cursoInicio && cursoFin) {
                            // Establecer fecha de fin
                            fechaFinInput.value = cursoFin;

                            // Establecer fecha de inicio
                            const hoy = new Date();
                            hoy.setHours(0, 0, 0, 0); // Normalizar a medianoche
                            const fechaCursoInicio = new Date(cursoInicio);

                            // La fecha de la DB viene en YYYY-MM-DD, que new Date() interpreta como UTC.
                            // Para evitar problemas de zona horaria, se ajusta.
                            fechaCursoInicio.setMinutes(fechaCursoInicio.getMinutes() + fechaCursoInicio.getTimezoneOffset());

                            if (hoy > fechaCursoInicio) {
                                // Si el curso ya empezó, la fecha de inicio es hoy
                                const hoyString = hoy.toISOString().split('T')[0];
                                fechaInicioInput.value = hoyString;
                            } else {
                                // Si el curso no ha empezado, la fecha de inicio es la del curso
                                fechaInicioInput.value = cursoInicio;
                            }
                            // Disparar el evento change para que se actualice el precio
                            fechaInicioInput.dispatchEvent(new Event('change'));
                        }
                    });
                    container.appendChild(div);
                });
            }
            // Ejecutar el callback si existe
            if (typeof callback === 'function') {
                callback();
            }
        });
}

document.getElementById('descuento').addEventListener('input', calcularPrecioFinal);

document.getElementById('btn-filtrar-horarios').addEventListener('click', buscarHorarios);

// --- Nueva Lógica de Precio Dinámico ---

function actualizarPrecio() {
    const cursoId = document.getElementById('id_curso').value;
    const fechaInicio = document.getElementById('fecha_inicio').value;

    if (!cursoId || !fechaInicio) {
        precioBaseCurso = 0;
        calcularPrecioFinal();
        return;
    }

    fetch(`index.php?url=matriculas/getPrecioByFecha&id_curso=${cursoId}&fecha=${fechaInicio}`)
        .then(response => response.json())
        .then(data => {
            precioBaseCurso = parseFloat(data.precio) || 0;
            calcularPrecioFinal();
        })
        .catch(error => {
            console.error('Error al obtener el precio:', error);
            precioBaseCurso = 0;
            calcularPrecioFinal();
        });
}

document.getElementById('fecha_inicio').addEventListener('change', actualizarPrecio);

// Form validation
document.getElementById('form-matricula').addEventListener('submit', function(event) {
    console.log("Form submission attempted!"); // DEBUGGING
    if (!document.getElementById('id_alumno').value && !document.querySelector('[name="nuevo_alumno_nombres"]').value) {
        alert('Por favor, seleccione un alumno existente o registre uno nuevo.');
        event.preventDefault();
    }
    if (!document.getElementById('id_horario').value) {
        alert('Por favor, seleccione un horario disponible.');
        event.preventDefault();
    }
});

// DNI validation for new student
const nuevoTipoDocumentoSelect = document.getElementById('nuevo_alumno_id_tipo_documento');
const nuevoDocumentoInput = document.getElementById('nuevo_alumno_documento');
const nuevoDocumentoError = document.getElementById('nuevo-dni-error');
const mainForm = document.getElementById('form-matricula');
const submitButton = document.querySelector('#form-matricula button[type="submit"]');

let isNuevoDocumentoDuplicate = false;
let isNuevoDocumentoInvalid = false;

function updateNuevoDocumentoValidation() {
    const selectedOption = nuevoTipoDocumentoSelect.options[nuevoTipoDocumentoSelect.selectedIndex];
    const longitud = selectedOption.getAttribute('data-longitud');
    nuevoDocumentoInput.maxLength = longitud;
    validateNuevoDocumentoFormat();
}

function validateNuevoDocumentoFormat() {
    const documento = nuevoDocumentoInput.value.trim();
    const longitud = nuevoDocumentoInput.maxLength;

    if (documento === '') {
        nuevoDocumentoError.style.display = 'none';
        isNuevoDocumentoInvalid = false;
        updateSubmitButtonState();
        return;
    }

    if (documento.length !== parseInt(longitud)) {
        nuevoDocumentoError.textContent = `El número de documento debe tener ${longitud} caracteres.`;
        nuevoDocumentoError.style.display = 'block';
        isNuevoDocumentoInvalid = true;
    } else {
        nuevoDocumentoError.style.display = 'none';
        isNuevoDocumentoInvalid = false;
    }
    updateSubmitButtonState();
}

function checkNuevoDocumentoDuplication() {
    const documento = nuevoDocumentoInput.value.trim();
    if (isNuevoDocumentoInvalid || documento === '') {
        isNuevoDocumentoDuplicate = false;
        updateSubmitButtonState();
        return;
    }

    fetch(`index.php?url=alumnos/checkDni&dni=${documento}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                nuevoDocumentoError.textContent = 'Este número de documento ya está registrado.';
                nuevoDocumentoError.style.display = 'block';
                isNuevoDocumentoDuplicate = true;
            } else {
                if (!isNuevoDocumentoInvalid) {
                    nuevoDocumentoError.style.display = 'none';
                }
                isNuevoDocumentoDuplicate = false;
            }
            updateSubmitButtonState();
        })
        .catch(error => {
            console.error('Error al verificar el documento:', error);
            isNuevoDocumentoDuplicate = false;
            updateSubmitButtonState();
        });
}

function updateSubmitButtonState() {
    const isAlumnoSelected = !!document.getElementById('id_alumno').value;
    const isNuevoAlumnoFormVisible = document.getElementById('nuevo-alumno-form').style.display === 'block';

    if (isAlumnoSelected || !isNuevoAlumnoFormVisible) {
        submitButton.disabled = false;
        return;
    }

    submitButton.disabled = isNuevoDocumentoDuplicate || isNuevoDocumentoInvalid;
}

// Event listeners
nuevoTipoDocumentoSelect.addEventListener('change', updateNuevoDocumentoValidation);
nuevoDocumentoInput.addEventListener('input', validateNuevoDocumentoFormat);
nuevoDocumentoInput.addEventListener('blur', checkNuevoDocumentoDuplication);

// Initial validation
updateNuevoDocumentoValidation();

mainForm.addEventListener('submit', function(event) {
    const isAlumnoSelected = !!document.getElementById('id_alumno').value;
    const isNuevoAlumnoFormVisible = document.getElementById('nuevo-alumno-form').style.display === 'block';

    if (!isAlumnoSelected && isNuevoAlumnoFormVisible) {
        validateNuevoDocumentoFormat();
        if (isNuevoDocumentoInvalid) {
            event.preventDefault();
            alert(`Por favor, corrija los errores. El número de documento del nuevo alumno debe tener ${nuevoDocumentoInput.maxLength} caracteres.`);
            return;
        }
        if (isNuevoDocumentoDuplicate) {
            event.preventDefault();
            alert('No se puede registrar la matrícula porque el número de documento del nuevo alumno ya existe.');
            return;
        }
    }

    if (!isAlumnoSelected && !document.querySelector('[name="nuevo_alumno_nombres"]').value) {
        alert('Por favor, seleccione un cliente existente o registre uno nuevo.');
        event.preventDefault();
    }
    if (!document.getElementById('id_horario').value) {
        alert('Por favor, seleccione un horario disponible.');
        event.preventDefault();
    }
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
