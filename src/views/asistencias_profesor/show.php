<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();

// Variables pasadas desde el controlador: $horario, $dias_clase

function get_profesor_status_class($status) {
    switch (strtolower($status)) {
        case 'presente': return 'status-presente';
        case 'falta_justificada': return 'status-falta-justificada';
        case 'falta_injustificada': return 'status-falta-injustificada';
        case 'permiso': return 'status-permiso';
        default: return 'status-no-marcado';
    }
}

function get_spanish_day_name_prof($date_str) {
    $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $day_index = date('w', strtotime($date_str));
    return $days[$day_index];
}
?>

<style>
.details-container { max-width: 1000px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.details-header { border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem; }
.dias-clase-container { margin-top: 2rem; }
.dias-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.dias-table th, .dias-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.dias-table th { background-color: #f2f2f2; }
.status-presente { color: green; }
.status-falta-justificada { color: orange; }
.status-falta-injustificada { color: red; }
.status-permiso { color: blue; }
.status-no-marcado { color: #555; }
.btn-back { display: inline-block; margin-top: 2rem; background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
.action-select { padding: 5px; border-radius: 4px; }
</style>

<div class="details-container">
    <div class="details-header">
        <h2>Marcar Asistencia para Horario #<?php echo htmlspecialchars($horario['id_horario']); ?></h2>
        <p><strong>Profesor:</strong> <?php echo htmlspecialchars($horario['profesor_nombre']); ?></p>
        <p><strong>Curso:</strong> <?php echo htmlspecialchars($horario['curso_nombre']); ?></p>
        <p><strong>Periodo:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($horario['fecha_inicio']))) . ' - ' . htmlspecialchars(date('d/m/Y', strtotime($horario['fecha_fin']))); ?></p>
    </div>

    <div class="dias-clase-container">
        <h3>Días de Clase Programados</h3>
        <table class="dias-table">
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dias_clase)): ?>
                    <tr>
                        <td colspan="5">No hay días de clase programados en el rango de fechas de este horario.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dias_clase as $dia): ?>
                        <tr>
                            <td><?php echo get_spanish_day_name_prof($dia['fecha_clase']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($dia['fecha_clase']))); ?></td>
                            <td class="<?php echo get_profesor_status_class($dia['estado']); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $dia['estado']))); ?></td>
                            <td><?php echo htmlspecialchars($dia['observaciones']); ?></td>
                            <td>
                                <select class="action-select"
                                        data-fecha="<?php echo $dia['fecha_clase']; ?>"
                                        data-horario-id="<?php echo $horario['id_horario']; ?>"
                                        data-profesor-id="<?php echo $horario['id_profesor']; ?>">
                                    <option value="no_marcado" <?php echo $dia['estado'] == 'no_marcado' ? 'selected' : ''; ?>>Sin Marcar</option>
                                    <option value="presente" <?php echo $dia['estado'] == 'presente' ? 'selected' : ''; ?>>Presente</option>
                                    <option value="falta_justificada" <?php echo $dia['estado'] == 'falta_justificada' ? 'selected' : ''; ?>>Falta Justificada</option>
                                    <option value="falta_injustificada" <?php echo $dia['estado'] == 'falta_injustificada' ? 'selected' : ''; ?>>Falta Injustificada</option>
                                    <option value="permiso" <?php echo $dia['estado'] == 'permiso' ? 'selected' : ''; ?>>Permiso</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="index.php?url=asistencias_profesor" class="btn-back">Volver al Listado</a>
</div>

<script>
document.querySelectorAll('.action-select').forEach(select => {
    select.addEventListener('change', function() {
        const estado = this.value;
        const fecha = this.dataset.fecha;
        const id_horario = this.dataset.horarioId;
        const id_profesor = this.dataset.profesorId;
        let observaciones = '';

        // Solo pedir observaciones si se está marcando una asistencia, no al borrarla.
        if (estado !== 'no_marcado') {
            observaciones = prompt("Añadir una observación (opcional):");
            // Si el usuario cancela el prompt, no hacemos nada.
            if (observaciones === null) {
                location.reload(); // Recargar para resetear el dropdown
                return;
            }
        }

        const formData = new FormData();
        formData.append('id_horario', id_horario);
        formData.append('id_profesor', id_profesor);
        formData.append('fecha', fecha);
        formData.append('estado', estado);
        formData.append('observaciones', observaciones || '');
        formData.append('csrf_token', '<?php echo $auth->getCsrfToken(); ?>');

        fetch('index.php?url=asistencias_profesor/save', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Recargar para ver los cambios
            } else {
                alert('Error al guardar la asistencia: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error de conexión.');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
