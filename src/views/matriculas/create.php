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
.form-group input, .form-group select { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.form-actions { margin-top: 1rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
.btn-success { background-color: #5cb85c; }
.btn-secondary { background-color: #6c757d; }
#horarios-disponibles-container { margin-top: 1rem; }
.horario-item { border: 1px solid #ccc; padding: 1rem; margin-bottom: 0.5rem; border-radius: 4px; cursor: pointer; }
.horario-item:hover { background-color: #f5f5f5; }
.horario-item.selected { border-color: #337ab7; background-color: #eaf2fa; }
</style>

<div class="form-container">
    <h2>Nueva Matrícula</h2>

    <form id="form-matricula" action="index.php?url=matriculas/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <div class="form-section">
            <h4>1. Seleccionar Alumno y Curso</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="id_alumno">Alumno</label>
                    <select id="id_alumno" name="id_alumno" required>
                        <option value="">Seleccione un alumno</option>
                        <?php foreach ($alumnos as $alumno): ?>
                            <option value="<?php echo htmlspecialchars($alumno['id_alumno']); ?>"><?php echo htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_curso">Curso</label>
                    <select id="id_curso" name="id_curso" required>
                        <option value="">Seleccione un curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?php echo htmlspecialchars($curso['id_curso']); ?>"><?php echo htmlspecialchars($curso['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h4>2. Seleccionar Horario Disponible</h4>
            <div id="horarios-disponibles-container">
                <p>Por favor, seleccione un curso para ver los horarios disponibles.</p>
            </div>
            <input type="hidden" id="id_horario" name="id_horario" required>
            <input type="hidden" id="dias_semana_hidden" name="dias_semana_hidden">
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
                    <label for="precio_final">Precio Final (S/)</label>
                    <input type="number" id="precio_final" name="precio_final" step="0.01" required>
                </div>
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
                <textarea id="observaciones" name="observaciones" rows="3" style="width: 100%;"></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php?url=matriculas" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Registrar Matrícula</button>
        </div>
    </form>
</div>

<script>
document.getElementById('id_curso').addEventListener('change', function() {
    const cursoId = this.value;
    // La lógica de precio ahora viene de los horarios, no del curso.
    document.getElementById('precio_final').value = '';

    const container = document.getElementById('horarios-disponibles-container');
    container.innerHTML = '<p>Cargando horarios...</p>';
    document.getElementById('id_horario').value = ''; // Reset hidden input

    if (!cursoId) {
        container.innerHTML = '<p>Por favor, seleccione un curso para ver los horarios disponibles.</p>';
        return;
    }

    fetch('index.php?url=matriculas/getHorariosByCurso&id_curso=' + cursoId)
        .then(response => response.json())
        .then(horarios => {
            container.innerHTML = '';
            if (horarios.length === 0) {
                container.innerHTML = '<p>No hay horarios con vacantes para este curso.</p>';
            } else {
                horarios.forEach(horario => {
                    const div = document.createElement('div');
                    div.className = 'horario-item';
                    div.dataset.id = horario.id_horario;
                    div.dataset.dias = horario.dias_semana;
                    div.innerHTML = `
                        <strong>Profesor:</strong> ${horario.profesor_nombre}<br>
                        <strong>Lugar:</strong> ${horario.carril_nombre}<br>
                        <strong>Días:</strong> ${horario.tipo_horario_nombre}<br>
                        <strong>Horario:</strong> ${horario.hora_inicio} - ${horario.hora_fin}<br>
                        <strong>Vacantes:</strong> ${horario.vacantes_disponibles}
                    `;
                    div.addEventListener('click', function() {
                        // Remove 'selected' class from all items
                        document.querySelectorAll('.horario-item').forEach(item => item.classList.remove('selected'));
                        // Add 'selected' class to the clicked item
                        this.classList.add('selected');
                        // Set the value of the hidden input
                        document.getElementById('id_horario').value = this.dataset.id;
                        document.getElementById('dias_semana_hidden').value = this.dataset.dias;
                        document.getElementById('precio_final').value = horario.precio_actual || '';
                    });
                    container.appendChild(div);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching horarios:', error);
            container.innerHTML = '<p>Error al cargar los horarios. Por favor, intente de nuevo.</p>';
        });
});

// Validar que se ha seleccionado un horario antes de enviar
document.getElementById('form-matricula').addEventListener('submit', function(event) {
    const horarioId = document.getElementById('id_horario').value;
    if (!horarioId) {
        alert('Por favor, seleccione un horario disponible.');
        event.preventDefault(); // Detener el envío del formulario
    }
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
