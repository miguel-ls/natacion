<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
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
</style>

<div class="form-container">
    <h2>Nueva Matrícula</h2>

    <form id="form-matricula" action="index.php?url=matriculas/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">

        <div class="form-section">
            <h4>1. Seleccionar o Registrar Alumno</h4>
            <div class="form-group search-container">
                <label for="alumno_search">Buscar Alumno Existente</label>
                <input type="text" id="alumno_search" class="form-control" placeholder="Escriba nombre, apellido o documento..." autocomplete="off">
                <input type="hidden" id="id_alumno" name="id_alumno">
                <div id="alumno-search-results" class="search-results"></div>
                <small>Si el alumno no existe, regístrelo a continuación.</small>
            </div>

            <button type="button" id="btn-show-nuevo-alumno" class="btn btn-secondary">Registrar Nuevo Alumno</button>

            <div id="nuevo-alumno-form">
                <h5>Datos del Nuevo Alumno (Esenciales)</h5>
                <div class="form-row">
                    <div class="form-group"><label>Nombres:</label><input type="text" name="nuevo_alumno_nombres"></div>
                    <div class="form-group"><label>Apellidos:</label><input type="text" name="nuevo_alumno_apellidos"></div>
                </div>
                 <div class="form-row">
                    <div class="form-group"><label>Documento:</label><input type="text" name="nuevo_alumno_documento"></div>
                    <div class="form-group"><label>Teléfono:</label><input type="text" name="nuevo_alumno_telefono"></div>
                </div>
                 <div class="form-group"><label>Email:</label><input type="email" name="nuevo_alumno_email"></div>
            </div>
        </div>

        <div class="form-section">
            <h4>2. Seleccionar Curso y Horario</h4>
            <div class="form-group search-container">
                <label for="curso_search">Buscar Curso</label>
                <input type="text" id="curso_search" class="form-control" placeholder="Escriba el nombre del curso..." autocomplete="off" required>
                <input type="hidden" id="id_curso" name="id_curso" required>
                <div id="curso-search-results" class="search-results"></div>
            </div>

            <div class="schedule-filters">
                <div class="form-group">
                    <label for="filtro_profesor">Profesor</label>
                    <select id="filtro_profesor">
                        <option value="0">Todos</option>
                        <?php foreach ($profesores as $profesor): ?>
                            <option value="<?php echo htmlspecialchars($profesor['id_profesor']); ?>"><?php echo htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtro_hora_inicio">Desde las:</label>
                    <input type="time" id="filtro_hora_inicio">
                </div>
                <div class="form-group">
                    <label for="filtro_hora_fin">Hasta las:</label>
                    <input type="time" id="filtro_hora_fin">
                </div>
                <button type="button" id="btn-filtrar-horarios" class="btn btn-primary">Filtrar Horarios</button>
            </div>

            <div id="horarios-disponibles-container">
                <p>Por favor, seleccione un curso.</p>
            </div>
            <input type="hidden" id="id_horario" name="id_horario" required>
            <input type="hidden" id="dias_semana_hidden" name="dias_semana_hidden">
            <input type="hidden" id="precio_base" name="precio_base">
        </div>

        <div class="form-section">
            <h4>3. Fechas y Pago</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label for="fecha_fin">Fecha de Fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="descuento">Descuento (S/)</label>
                    <input type="number" id="descuento" name="descuento" step="0.01" value="0">
                </div>
                 <div class="form-group">
                    <label for="precio_final">Precio Final (S/)</label>
                    <input type="number" id="precio_final" name="precio_final" step="0.01" required readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="id_forma_pago">Forma de Pago</label>
                    <select id="id_forma_pago" name="id_forma_pago" required>
                        <option value="">Seleccione una forma de pago</option>
                         <?php foreach ($formas_pago as $forma): ?>
                            <option value="<?php echo htmlspecialchars($forma['id_forma_pago']); ?>"><?php echo htmlspecialchars($forma['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
             <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="3"></textarea>
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

searchInput.addEventListener('keyup', function() {
    const term = this.value;
    hiddenInputId.value = '';
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
                    nuevoAlumnoForm.style.display = 'none';
                    document.querySelectorAll('#nuevo-alumno-form input').forEach(input => input.value = '');
                });
                searchResults.appendChild(item);
            });
        });
});

document.getElementById('btn-show-nuevo-alumno').addEventListener('click', function() {
    nuevoAlumnoForm.style.display = 'block';
    searchInput.value = '';
    hiddenInputId.value = '';
    searchResults.innerHTML = '';
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
                item.addEventListener('click', function() {
                    cursoSearchInput.value = this.textContent;
                    cursoHiddenInputId.value = this.dataset.id;
                    cursoSearchResults.innerHTML = '';
                    buscarHorarios();
                });
                cursoSearchResults.appendChild(item);
            });
        });
});


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
                    div.dataset.precio = horario.precio_actual;
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

                        precioBaseCurso = parseFloat(this.dataset.precio) || 0;
                        calcularPrecioFinal();

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

// Form validation
document.getElementById('form-matricula').addEventListener('submit', function(event) {
    if (!document.getElementById('id_alumno').value && !document.querySelector('[name="nuevo_alumno_nombres"]').value) {
        alert('Por favor, seleccione un alumno existente o registre uno nuevo.');
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
