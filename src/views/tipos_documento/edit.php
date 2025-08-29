<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<style>
/* Estilos para formularios (reutilizados) */
.form-container { max-width: 600px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; }
.form-group input { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.form-actions { margin-top: 1.5rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
.btn-success { background-color: #5cb85c; }
.btn-secondary { background-color: #6c757d; }
</style>

<div class="form-container">
    <h2>Editar Tipo de Documento</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=tipos_documento/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($tipo_documento['id']); ?>">
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($tipo_documento['descripcion']); ?>" required>
        </div>
        <div class="form-group">
            <label for="longitud">Longitud (número de caracteres)</label>
            <input type="number" id="longitud" name="longitud" value="<?php echo htmlspecialchars($tipo_documento['longitud']); ?>" required>
        </div>
        <div class="form-group">
            <label for="sunat">Código SUNAT</label>
            <input type="text" id="sunat" name="sunat" value="<?php echo htmlspecialchars($tipo_documento['sunat']); ?>" maxlength="3">
        </div>
        <div class="form-actions">
            <a href="index.php?url=tipos_documento" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Actualizar</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
