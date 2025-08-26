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
.action-select { padding: 5px; border-radius: 4px; }
.recuperacion-form { margin-top: 1.5rem; padding: 1rem; border: 1px dashed #ccc; }
</style>

<div class="details-container">
    <div class="details-header">
        <h2>Detalles de la Matrícula #<?php echo htmlspecialchars($matricula['id_matricula']); ?></h2>
        <p><strong>Alumno:</strong> <?php echo htmlspecialchars($matricula['alumno_nombre']); ?></p>
    </div>

    <div class="details-grid">
        <!-- ... (detalles de la matrícula) ... -->
    </div>

    <div class="dias-clase-container">
        <h3>Días de Clase Programados</h3>
        <table class="dias-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Observación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dias_clase as $dia): ?>
                    <tr>
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
