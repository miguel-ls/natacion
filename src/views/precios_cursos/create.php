<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<div class="form-container">
    <h2>Añadir Nuevo Precio a un Curso</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=precios_cursos/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">

        <div class="form-group">
            <label for="id_curso">Curso</label>
            <select id="id_curso" name="id_curso" required>
                <option value="">Seleccione un curso</option>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?php echo htmlspecialchars($curso['id_curso']); ?>"><?php echo htmlspecialchars($curso['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="id_tipo_precio">Tipo de Precio</label>
            <select id="id_tipo_precio" name="id_tipo_precio" required>
                <option value="">Seleccione un tipo</option>
                <?php foreach ($tipos_precio as $tipo): ?>
                    <option value="<?php echo htmlspecialchars($tipo['id_tipo_precio']); ?>"><?php echo htmlspecialchars($tipo['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="precio">Precio (S/)</label>
            <input type="number" id="precio" name="precio" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="fecha_inicio">Vigencia Desde</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required>
        </div>
        <div class="form-group">
            <label for="fecha_fin">Vigencia Hasta</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required>
        </div>

        <div class="form-actions">
            <a href="index.php?url=precios_cursos" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar Precio</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
