<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<style>
.details-container { max-width: 900px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.current-info { background-color: #f9f9f9; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; }
.horarios-list { margin-top: 1rem; }
.horario-item { border: 1px solid #ccc; padding: 1rem; margin-bottom: 0.5rem; border-radius: 4px; cursor: pointer; }
.horario-item:hover { background-color: #f5f5f5; }
.horario-item.selected { border-color: #337ab7; background-color: #eaf2fa; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
.btn-success { background-color: #5cb85c; }
.btn-secondary { background-color: #6c757d; }
.form-actions { margin-top: 1rem; text-align: right; }
</style>

<div class="details-container">
    <h2>Cambiar Horario de Matrícula #<?php echo htmlspecialchars($matricula['id_matricula']); ?></h2>

    <div class="current-info">
        <h4>Información Actual</h4>
        <p><strong>Alumno:</strong> <?php echo htmlspecialchars($matricula['alumno_nombre']); ?></p>
        <p><strong>Curso:</strong> <?php echo htmlspecialchars($matricula['curso_nombre']); ?></p>
        <p><strong>Horario Actual:</strong> <?php echo htmlspecialchars($matricula['tipo_horario_nombre']); ?> de <?php echo htmlspecialchars(date('h:i A', strtotime($matricula['hora_inicio']))) . ' a ' . htmlspecialchars(date('h:i A', strtotime($matricula['hora_fin']))); ?> (Prof. <?php echo htmlspecialchars($matricula['profesor_nombre']); ?>)</p>
    </div>

    <h4>Seleccione un Nuevo Horario Disponible para "<?php echo htmlspecialchars($matricula['curso_nombre']); ?>"</h4>

    <form id="form-change-horario" action="index.php?url=matriculas/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <input type="hidden" name="id_matricula" value="<?php echo htmlspecialchars($matricula['id_matricula']); ?>">
        <input type="hidden" id="id_horario" name="id_horario" required>

        <div class="horarios-list">
            <?php if (empty($horarios_disponibles)): ?>
                <p>No hay otros horarios con vacantes disponibles para este curso.</p>
            <?php else: ?>
                <?php foreach ($horarios_disponibles as $horario): ?>
                    <div class="horario-item" data-id="<?php echo $horario['id_horario']; ?>">
                        <strong>Profesor:</strong> <?php echo htmlspecialchars($horario['profesor_nombre']); ?><br>
                        <strong>Lugar:</strong> <?php echo htmlspecialchars($horario['carril_nombre']); ?><br>
                        <strong>Días:</strong> <?php echo htmlspecialchars($horario['tipo_horario_nombre']); ?><br>
                        <strong>Horario:</strong> <?php echo htmlspecialchars(date('h:i A', strtotime($horario['hora_inicio']))) . ' - ' . htmlspecialchars(date('h:i A', strtotime($horario['hora_fin']))); ?><br>
                        <strong>Vacantes:</strong> <?php echo htmlspecialchars($horario['vacantes_disponibles']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <a href="index.php?url=matriculas" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success" <?php echo empty($horarios_disponibles) ? 'disabled' : ''; ?>>Confirmar Cambio de Horario</button>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.horario-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.horario-item').forEach(el => el.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('id_horario').value = this.dataset.id;
    });
});

document.getElementById('form-change-horario').addEventListener('submit', function(event) {
    const horarioId = document.getElementById('id_horario').value;
    if (!horarioId) {
        alert('Por favor, seleccione un nuevo horario.');
        event.preventDefault();
    }
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
