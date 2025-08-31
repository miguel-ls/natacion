<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<style>
/* Estilos para el formulario de matrícula */
.form-container { max-width: 1200px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.form-section { border-bottom: 1px solid #eee; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
.form-row { display: flex; flex-wrap: wrap; margin: -0.75rem; }
.form-group { flex: 1 1 300px; padding: 0.75rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.form-actions { margin-top: 1rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; background-color: #007bff; }
.btn-secondary { background-color: #6c757d; }
.btn-success { background-color: #28a745; }
.btn-primary { background-color: #007bff; }

/* Estilos para búsqueda */
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
.error-text { color: red; font-size: 0.875em; margin-top: 0.25rem; }

/* Estilos para los filtros y resultados */
.filters-container { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-top: 1rem;}
.results-grid { margin-top: 1.5rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.area-card, .profesor-card { border: 1px solid #ccc; padding: 1rem; border-radius: 4px; }
.area-card { cursor: pointer; }
.area-card:hover { background-color: #f5f5f5; }
.area-card.selected { border-color: #28a745; background-color: #eafaf1; }
</style>

<div class="form-container">
    <h2>Matrícula Múltiple</h2>

    <form id="form-matricula-multiple" action="index.php?url=matricula_multiple/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">

        <!-- SECCIÓN 1: CLIENTE -->
        <div class="form-section">
            <h4>1. Seleccionar o Registrar Cliente</h4>
            <div class="form-group search-container">
                <label for="alumno_search">Buscar Cliente Existente</label>
                <input type="text" id="alumno_search" class="form-control" placeholder="Escriba nombre, apellido o documento..." autocomplete="off">
                <input type="hidden" id="id_alumno" name="id_alumno" required>
                <div id="alumno-search-results" class="search-results"></div>
                <small>Si el cliente no existe, regístrelo a continuación.</small>
            </div>

            <button type="button" id="btn-show-nuevo-alumno" class="btn btn-secondary">Registrar Nuevo Cliente</button>

            <div id="nuevo-alumno-form">
                <h5>Datos del Nuevo Cliente</h5>
                <div class="form-row">
                    <div class="form-group"><label>Nombres:</label><input type="text" name="nuevo_alumno_nombres"></div>
                    <div class="form-group"><label>Apellidos:</label><input type="text" name="nuevo_alumno_apellidos"></div>
                </div>
                 <div class="form-row">
                    <div class="form-group">
                        <label>Tipo de Documento:</label>
                        <select name="nuevo_alumno_id_tipo_documento" id="nuevo_alumno_id_tipo_documento">
                            <?php foreach ($tipos_documento as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo['id']); ?>" data-longitud="<?php echo htmlspecialchars($tipo['longitud']); ?>">
                                    <?php echo htmlspecialchars($tipo['descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Número de Documento:</label>
                        <input type="text" name="nuevo_alumno_documento" id="nuevo_alumno_documento">
                        <div class="error-text" id="nuevo-dni-error" style="display: none;"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Teléfono:</label><input type="text" name="nuevo_alumno_telefono"></div>
                    <div class="form-group"><label>Email:</label><input type="email" name="nuevo_alumno_email"></div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: FILTROS Y RESULTADOS -->
        <div class="form-section">
            <h4>2. Seleccionar Curso y Horario</h4>
            <div class="filters-container">
                <div class="form-group">
                    <label for="filtro_tipo_area">Tipo de Area</label>
                    <select id="filtro_tipo_area" name="filtro_tipo_area">
                         <option value="0">Todos</option>
                        <?php foreach ($tipos_area as $tipo): ?>
                            <option value="<?php echo htmlspecialchars($tipo['id_tipo_piscina']); ?>">
                                <?php echo htmlspecialchars($tipo['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtro_id_curso">Curso</label>
                    <select id="filtro_id_curso" name="id_curso" required>
                        <option value="">Seleccione un curso...</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?php echo htmlspecialchars($curso['id_curso']); ?>">
                                <?php echo htmlspecialchars($curso['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtro_id_profesor">Profesor</label>
                    <select id="filtro_id_profesor" name="id_profesor" required>
                        <option value="">Seleccione un profesor...</option>
                        <?php foreach ($profesores as $profesor): ?>
                            <option value="<?php echo htmlspecialchars($profesor['id_profesor']); ?>">
                                <?php echo htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtro_fecha_inicio">Fecha de Reserva</label>
                    <input type="date" id="filtro_fecha_inicio" name="filtro_fecha_inicio">
                </div>
                <div class="form-group">
                    <label for="filtro_fecha_fin">Fecha Final</label>
                    <input type="date" id="filtro_fecha_fin" name="filtro_fecha_fin">
                </div>
                <div class="form-group">
                    <label for="filtro_hora_inicio">Hora Inicial</label>
                    <input type="time" id="filtro_hora_inicio" name="filtro_hora_inicio">
                </div>
                <div class="form-group">
                    <label for="filtro_hora_fin">Hora Final</label>
                    <input type="time" id="filtro_hora_fin" name="filtro_hora_fin">
                </div>
                <button type="button" id="btn-filtrar-areas" class="btn btn-primary">Filtrar</button>
            </div>

            <!-- SECCIÓN 3: AREAS DISPONIBLES -->
            <h3>3. Areas Disponibles</h3>
            <div id="available-areas-container" class="results-grid">
                <p>Use los filtros para buscar áreas y horarios disponibles.</p>
            </div>
            <input type="hidden" id="selected_schedules" name="selected_schedules" required>

            <!-- SECCIÓN 4: PROFESORES DISPONIBLES -->
            <h3>4. Profesores Disponibles</h3>
            <div id="available-profesores-container" class="results-grid">
                <?php if (isset($profesores) && !empty($profesores)): ?>
                    <?php foreach ($profesores as $profesor): ?>
                        <div class="profesor-card">
                            <strong><?php echo htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?></strong><br>
                            <small>Tipo: <?php echo htmlspecialchars($profesor['tipo_profesor_nombre'] ?? 'No asignado'); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay profesores disponibles.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php?url=matricula_multiple" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Grabar Matrícula Múltiple</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para búsqueda de alumnos (copiada y adaptada)
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
                        // Aquí no deshabilitamos el form de nuevo alumno, solo lo ocultamos si es necesario
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

    // Lógica para el botón de filtrar
    document.getElementById('btn-filtrar-areas').addEventListener('click', function() {
        const id_tipo_area = document.getElementById('filtro_tipo_area').value;
        const filtro_fecha_inicio = document.getElementById('filtro_fecha_inicio').value;
        const filtro_fecha_fin = document.getElementById('filtro_fecha_fin').value;
        const filtro_hora_inicio = document.getElementById('filtro_hora_inicio').value;
        const filtro_hora_fin = document.getElementById('filtro_hora_fin').value;
        const container = document.getElementById('available-areas-container');

        if (!filtro_fecha_inicio || !filtro_fecha_fin || !filtro_hora_inicio || !filtro_hora_fin) {
            alert('Por favor, complete todos los filtros de fecha y hora.');
            return;
        }

        container.innerHTML = '<p>Buscando carriles libres...</p>';

        const queryParams = new URLSearchParams({
            id_tipo_area,
            filtro_fecha_inicio,
            filtro_fecha_fin,
            filtro_hora_inicio,
            filtro_hora_fin
        });

        fetch(`index.php?url=matricula_multiple/getAvailableAreas&${queryParams}`)
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';
                if (data.error) {
                    container.innerHTML = `<p style="color: red;">${data.error}</p>`;
                    return;
                }
                if (data.length === 0) {
                    container.innerHTML = '<p>No se encontraron áreas/carriles libres con los criterios seleccionados.</p>';
                    return;
                }

                data.forEach(lane => {
                    const card = document.createElement('div');
                    card.className = 'area-card';
                    card.dataset.laneId = lane.id_carril;
                    card.innerHTML = `
                        <strong>Área:</strong> ${lane.area_nombre}<br>
                        <strong>Sub Área:</strong> ${lane.sub_area_nombre}<br>
                        <span style="color: green; font-weight: bold;">¡Disponible!</span>
                    `;
                    container.appendChild(card);
                });
            })
            .catch(error => {
                container.innerHTML = `<p style="color: red;">Ocurrió un error al realizar la búsqueda.</p>`;
                console.error('Error fetching available areas:', error);
            });
    });

    // Lógica para seleccionar/deseleccionar tarjetas de área
    const container = document.getElementById('available-areas-container');
    const selectedSchedulesInput = document.getElementById('selected_schedules');
    let selectedIds = [];

    container.addEventListener('click', function(e) {
        const card = e.target.closest('.area-card');
        if (!card) return;

        const laneId = card.dataset.laneId;
        if (!laneId) return;

        if (card.classList.contains('selected')) {
            card.classList.remove('selected');
            selectedIds = selectedIds.filter(id => id !== laneId);
        } else {
            card.classList.add('selected');
            selectedIds.push(laneId);
        }

        selectedSchedulesInput.value = JSON.stringify(selectedIds);
    });

    // Validación del formulario
    document.getElementById('form-matricula-multiple').addEventListener('submit', function(event) {
        if (!document.getElementById('id_alumno').value && !document.querySelector('[name="nuevo_alumno_nombres"]').value) {
            alert('Por favor, seleccione un cliente existente o registre uno nuevo.');
            event.preventDefault();
        }
        if (selectedIds.length === 0) {
            alert('Por favor, seleccione al menos un área/horario disponible.');
            event.preventDefault();
        }
    });

});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
