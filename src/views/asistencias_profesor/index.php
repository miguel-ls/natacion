<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<style>
/* Estilos para la tabla y acciones */
.container { padding: 2rem; }
.table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background-color: #f2f2f2; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;}
.filter-form { display: flex; align-items: center; gap: 1rem; }
.action-select { padding: 5px; border-radius: 4px; }
.status-presente { color: green; }
.status-falta_justificada { color: orange; }
.status-falta_injustificada { color: red; }
.status-permiso { color: blue; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Control de Asistencia de Profesores</h1>
    </div>

    <div class="filter-form">
        <form action="index.php" method="GET">
            <input type="hidden" name="url" value="asistencias_profesor">
            <label for="fecha">Seleccionar Fecha:</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha_seleccionada); ?>">
            <button type="submit">Filtrar</button>
        </form>
    </div>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <table class="table">
        <thead>
            <tr>
                <th>Horario</th>
                <th>Curso</th>
                <th>Profesor</th>
                <th>Estado Asistencia</th>
                <th>Observaciones</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($horarios_del_dia)): ?>
                <tr>
                    <td colspan="6">No hay clases programadas para la fecha seleccionada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($horarios_del_dia as $horario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('h:i A', strtotime($horario['hora_inicio']))) . ' - ' . htmlspecialchars(date('h:i A', strtotime($horario['hora_fin']))); ?></td>
                        <td><?php echo htmlspecialchars($horario['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($horario['profesor_nombre']); ?></td>
                        <td class="status-<?php echo strtolower($horario['estado_asistencia'] ?? ''); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $horario['estado_asistencia'] ?? 'No marcado'))); ?></td>
                        <td><?php echo htmlspecialchars($horario['observaciones_asistencia'] ?? ''); ?></td>
                        <td>
                            <select class="action-select" data-profesor-id="<?php echo $horario['id_profesor']; ?>" data-horario-id="<?php echo $horario['id_horario']; ?>">
                                <option value="">Marcar como...</option>
                                <option value="presente">Presente</option>
                                <option value="falta_justificada">Falta Justificada</option>
                                <option value="falta_injustificada">Falta Injustificada</option>
                                <option value="permiso">Permiso</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.action-select').forEach(select => {
    select.addEventListener('change', function() {
        const estado = this.value;
        if (!estado) return;

        const id_profesor = this.dataset.profesorId;
        const id_horario = this.dataset.horarioId;
        const fecha = document.getElementById('fecha').value;
        const observaciones = prompt("Añadir una observación (opcional):");

        const formData = new FormData();
        formData.append('id_profesor', id_profesor);
        formData.append('id_horario', id_horario);
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
                location.reload();
            } else {
                alert('Error al guardar la asistencia: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
