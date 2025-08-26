<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<div class="form-container">
    <h2>Editar Tipo de Precio</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=tipos_precio/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <input type="hidden" name="id_tipo_precio" value="<?php echo htmlspecialchars($item['id_tipo_precio']); ?>">

        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($item['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3" style="width: 100%;"><?php echo htmlspecialchars($item['descripcion']); ?></textarea>
        </div>
        <div class="form-actions">
            <a href="index.php?url=tipos_precio" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Actualizar Tipo de Precio</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
