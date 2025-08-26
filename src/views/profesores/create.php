<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<style>
/* Reutilizamos los estilos de formularios */
.form-container { max-width: 800px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.form-row { display: flex; flex-wrap: wrap; margin: -0.5rem; }
.form-group { flex: 1 1 300px; padding: 0.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; }
.form-group input { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.form-actions { padding: 0.5rem; margin-top: 1rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
.btn-success { background-color: #5cb85c; }
.btn-secondary { background-color: #6c757d; }
</style>

<div class="form-container">
    <h2>Añadir Nuevo Profesor</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=profesores/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="nombres">Nombres</label>
                <input type="text" id="nombres" name="nombres" required>
            </div>
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="documento_identidad">Documento de Identidad</label>
                <input type="text" id="documento_identidad" name="documento_identidad" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono">
            </div>
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_contratacion">Fecha de Contratación</label>
                <input type="date" id="fecha_contratacion" name="fecha_contratacion" required>
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?url=profesores" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar Profesor</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
