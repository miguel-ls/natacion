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
.form-group input, .form-group select { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.form-actions { margin-top: 1rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
.btn-success { background-color: #5cb85c; }
.btn-secondary { background-color: #6c757d; }
</style>

<div class="form-container">
    <h2>Editar Area</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=piscinas/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <input type="hidden" name="id_piscina" value="<?php echo htmlspecialchars($piscina['id_piscina']); ?>">

        <div class="form-group">
            <label for="nombre">Nombre del Area</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($piscina['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label for="id_tipo_piscina">Tipo de Area</label>
            <select id="id_tipo_piscina" name="id_tipo_piscina" required>
                <option value="">Seleccione un tipo</option>
                <?php foreach ($tipos_piscina as $tipo): ?>
                    <option value="<?php echo htmlspecialchars($tipo['id_tipo_piscina']); ?>" <?php echo ($piscina['id_tipo_piscina'] == $tipo['id_tipo_piscina']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($tipo['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions">
            <a href="index.php?url=piscinas" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Actualizar Area</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
