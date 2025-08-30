<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();

function get_status_class($status) {
    switch (strtolower($status)) {
        case 'asistio': return 'status-asistio';
        case 'falto': return 'status-falto';
        case 'postergada': return 'status-postergada';
        case 'recuperada': return 'status-recuperada';
        default: return 'status-programada';
    }
}

function get_spanish_day_name($date_str) {
    $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $day_index = date('w', strtotime($date_str));
    return $days[$day_index];
}
?>

<style>
.details-container { max-width: 1000px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.details-header { border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem; }
.details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
.detail-item { background-color: #f9f9f9; padding: 1rem; border-radius: 4px; }
.detail-item strong { display: block; margin-bottom: 0.5rem; color: #555; }
.dias-clase-container { margin-top: 2rem; }
.dias-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.dias-table th, .dias-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.dias-table th { background-color: #f2f2f2; }
.status-asistio { color: green; }
.status-falto { color: red; }
.status-postergada { color: orange; }
.status-recuperada { color: purple; }
.status-programada { color: blue; }
.btn-back { display: inline-block; margin-top: 2rem; background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
.btn-info { background-color: #5bc0de; }
.btn-warning { background-color: #f0ad4e; }
.action-select { padding: 5px; border-radius: 4px; }
.recuperacion-form { margin-top: 1.5rem; padding: 1rem; border: 1px dashed #ccc; }
</style>

<div class="details-container">
    <div class="details-header">
        <h2>Detalles de la Matrícula #<?php echo htmlspecialchars($matricula['id_matricula']); ?></h2>
        <p><strong>Alumno:</strong> <?php echo htmlspecialchars($matricula['alumno_nombre']); ?></p>
    </div>

    <div class="details-grid">
        <div class="detail-item">
            <strong>Curso:</strong> <?php echo htmlspecialchars($matricula['curso_nombre']); ?>
        </div>
        <div class="detail-item">
            <strong>Profesor:</strong> <?php echo htmlspecialchars($matricula['profesor_nombre']); ?>
        </div>
        <div class="detail-item">
            <strong>Horario:</strong> <?php echo htmlspecialchars($matricula['tipo_horario_nombre']); ?> de <?php echo htmlspecialchars(date('h:i A', strtotime($matricula['hora_inicio']))) . ' a ' . htmlspecialchars(date('h:i A', strtotime($matricula['hora_fin']))); ?>
        </div>
        <div class="detail-item">
            <strong>Area y Sub Area:</strong> <?php echo htmlspecialchars($matricula['carril_nombre']); ?>
        </div>
        <div class="detail-item">
            <strong>Periodo:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_inicio']))); ?> - <?php echo htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_fin']))); ?>
        </div>
        <div class="detail-item">
            <strong>Estado de Matrícula:</strong> <?php echo htmlspecialchars(ucfirst($matricula['estado'])); ?>
        </div>
        <div class="detail-item">
            <strong>Forma de Pago:</strong> <?php echo htmlspecialchars($matricula['forma_pago_nombre'] ?? 'No especificado'); ?>
        </div>
        <div class="detail-item">
            <strong>Descuento:</strong> S/ <?php echo htmlspecialchars(number_format($matricula['descuento'] ?? 0, 2)); ?>
        </div>
        <div class="detail-item">
            <strong>Importe Final:</strong> S/ <?php echo htmlspecialchars(number_format($matricula['precio_final'] ?? 0, 2)); ?>
        </div>
    </div>

    <div class="dias-clase-container">
        <h3>Días de Clase Programados</h3>
        <table class="dias-table">
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Observación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dias_clase)): ?>
                    <tr>
                        <td colspan="5">No hay días de clase generados para esta matrícula.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dias_clase as $dia): ?>
                        <tr>
                            <td><?php echo get_spanish_day_name($dia['fecha_clase']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($dia['fecha_clase']))); ?></td>
                            <td class="status-cell <?php echo get_status_class($dia['estado']); ?>"><?php echo htmlspecialchars(ucfirst($dia['estado'])); ?></td>
                            <td><?php echo htmlspecialchars($dia['observacion']); ?></td>
                            <td>
                                <select class="action-select" data-id="<?php echo $dia['id_matricula_dia']; ?>">
                                    <option value="programada" <?php echo $dia['estado'] == 'programada' ? 'selected' : ''; ?>>Programada</option>
                                    <option value="asistio" <?php echo $dia['estado'] == 'asistio' ? 'selected' : ''; ?>>Asistió</option>
                                    <option value="falto" <?php echo $dia['estado'] == 'falto' ? 'selected' : ''; ?>>Faltó</option>
                                    <option value="postergada" <?php echo $dia['estado'] == 'postergada' ? 'selected' : ''; ?>>Postergada</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="recuperacion-form">
        <h4>Agendar Clase de Recuperación</h4>
        <form id="form-recuperacion">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
            <input type="hidden" name="id_matricula" value="<?php echo htmlspecialchars($matricula['id_matricula']); ?>">
            <label for="fecha_recuperacion">Fecha:</label>
            <input type="date" id="fecha_recuperacion" name="fecha" required>
            <label for="obs_recuperacion">Observación:</label>
            <input type="text" id="obs_recuperacion" name="observacion" placeholder="Ej. Recupera clase del día X">
            <button type="submit">Agregar Recuperación</button>
        </form>
    </div>

    <a href="index.php?url=matriculas/edit&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-warning">Editar Matrícula</a>
    <a href="index.php?url=alumnos/show&id=<?php echo $matricula['id_alumno']; ?>" class="btn btn-info">Volver a Alumno</a>
    <a href="index.php?url=matriculas" class="btn-back">Volver al Listado</a>
</div>

<script>
document.querySelectorAll('.action-select').forEach(select => {
    select.addEventListener('change', function() {
        const id_dia = this.dataset.id;
        const estado = this.value;
        const observacion = prompt("Añadir una observación (opcional):");

        const formData = new FormData();
        formData.append('id_dia', id_dia);
        formData.append('estado', estado);
        formData.append('observacion', observacion || '');
        formData.append('csrf_token', '<?php echo $auth->getCsrfToken(); ?>');

        fetch('index.php?url=matriculas/updateDiaClase', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página para ver los cambios
                location.reload();
            } else {
                alert('Error al actualizar: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

document.getElementById('form-recuperacion').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);

    fetch('index.php?url=matriculas/addRecuperacion', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al agregar clase: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
