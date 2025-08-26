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

/* Estilos para búsqueda de alumno */
#alumno-search-container { position: relative; }
#alumno-search-results { position: absolute; background-color: white; border: 1px solid #ccc; width: 100%; max-height: 200px; overflow-y: auto; z-index: 1000; }
.search-result-item { padding: 10px; cursor: pointer; }
.search-result-item:hover { background-color: #f0f0f0; }
#nuevo-alumno-form { display: none; background-color: #f9f9f9; padding: 1rem; border-radius: 5px; margin-top: 1rem;}
.schedule-filters { display: flex; gap: 1rem; align-items: flex-end; background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-top: 1rem;}
</style>

<div class="form-container">
    <h2>Nueva Matrícula</h2>

    <form id="form-matricula" action="index.php?url=matriculas/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">

        <!-- STUDENT SECTION -->
        <div class="form-section">
            <!-- ... (código de búsqueda y creación de alumno sin cambios) ... -->
        </div>

        <!-- SCHEDULE SECTION -->
        <div class="form-section">
            <h4>2. Seleccionar Curso y Horario</h4>
            <div class="form-group">
                <label for="id_curso">Curso</label>
                <select id="id_curso" name="id_curso" required>
                    <option value="">Seleccione un curso para ver horarios</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo htmlspecialchars($curso['id_curso']); ?>"><?php echo htmlspecialchars($curso['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- NEW FILTERS -->
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
                <p>Por favor, seleccione un curso y presione "Filtrar Horarios".</p>
            </div>
            <input type="hidden" id="id_horario" name="id_horario" required>
            <input type="hidden" id="dias_semana_hidden" name="dias_semana_hidden">
        </div>

        <!-- PAYMENT SECTION -->
        <div class="form-section">
            <!-- ... (código de fechas y pago sin cambios) ... -->
        </div>

        <div class="form-actions">
            <a href="index.php?url=matriculas" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Registrar Matrícula</button>
        </div>
    </form>
</div>

<script>
// ... (código de búsqueda de alumno sin cambios) ...

// Lógica para horarios
function buscarHorarios() {
    const cursoId = document.getElementById('id_curso').value;
    const profesorId = document.getElementById('filtro_profesor').value;
    const horaInicio = document.getElementById('filtro_hora_inicio').value;
    const horaFin = document.getElementById('filtro_hora_fin').value;

    document.getElementById('precio_final').value = '';
    const container = document.getElementById('horarios-disponibles-container');
    container.innerHTML = '<p>Cargando horarios...</p>';
    document.getElementById('id_horario').value = '';

    if (!cursoId) {
        container.innerHTML = '<p>Por favor, seleccione un curso para ver los horarios disponibles.</p>';
        return;
    }

    // Construir URL con filtros
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
                    div.dataset.dias = horario.dias_semana;
                    div.innerHTML = `<strong>Profesor:</strong> ${horario.profesor_nombre}<br>
                                     <strong>Lugar:</strong> ${horario.carril_nombre}<br>
                                     <strong>Días:</strong> ${horario.tipo_horario_nombre}<br>
                                     <strong>Horario:</strong> ${horario.hora_inicio} - ${horario.hora_fin}<br>
                                     <strong>Vacantes:</strong> ${horario.vacantes_disponibles}`;
                    div.addEventListener('click', function() {
                        document.querySelectorAll('.horario-item').forEach(item => item.classList.remove('selected'));
                        this.classList.add('selected');
                        document.getElementById('id_horario').value = this.dataset.id;
                        document.getElementById('dias_semana_hidden').value = this.dataset.dias;
                        document.getElementById('precio_final').value = horario.precio_actual || '';
                    });
                    container.appendChild(div);
                });
            }
        });
}

document.getElementById('id_curso').addEventListener('change', buscarHorarios);
document.getElementById('btn-filtrar-horarios').addEventListener('click', buscarHorarios);

// ... (código de validación de formulario sin cambios) ...
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
