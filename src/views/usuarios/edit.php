<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<style>
/* Reutilizamos los estilos de formularios */
.form-container { max-width: 600px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; }
.form-group input { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.form-actions { margin-top: 1rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
.btn-success { background-color: #5cb85c; }
.btn-secondary { background-color: #6c757d; }
.password-note { font-size: 0.9em; color: #666; }
</style>

<div class="form-container">
    <h2>Editar Usuario</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message" style="color: #a94442; background-color: #f2dede; border: 1px solid #ebccd1; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=usuarios/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>">

        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>
        <hr>
        <p class="password-note">Deje los siguientes campos en blanco si no desea cambiar la contraseña.</p>
        <div class="form-group">
            <label for="password">Nueva Contraseña</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirmar Nueva Contraseña</label>
            <input type="password" id="password_confirm" name="password_confirm">
        </div>
        <div class="form-actions">
            <a href="index.php?url=usuarios" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Actualizar Usuario</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
