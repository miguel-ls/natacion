<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<div class="form-container">
    <h2>Editar Fechas de la Matrícula #<?php echo htmlspecialchars($matricula['id_matricula']); ?></h2>
    <p><strong>Alumno:</strong> <?php echo htmlspecialchars($matricula['alumno_nombre']); ?></p>
    <p><strong>Curso:</strong> <?php echo htmlspecialchars($matricula['curso_nombre']); ?></p>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=matriculas/updateDates" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <input type="hidden" name="id_matricula" value="<?php echo htmlspecialchars($matricula['id_matricula']); ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="fecha_inicio">Nueva Fecha de Inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($matricula['fecha_inicio']); ?>" required>
            </div>
            <div class="form-group">
                <label for="fecha_fin">Nueva Fecha de Fin</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($matricula['fecha_fin']); ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php?url=matriculas/show&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Actualizar Fechas y Regenerar Clases</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
