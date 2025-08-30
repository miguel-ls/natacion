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
    <h2>Editar Sub Area</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=carriles/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <input type="hidden" name="id_carril" value="<?php echo htmlspecialchars($carril['id_carril']); ?>">

        <div class="form-group">
            <label for="id_piscina">Area</label>
            <select id="id_piscina" name="id_piscina" required>
                <option value="">Seleccione un area</option>
                <?php foreach ($piscinas as $piscina): ?>
                    <option value="<?php echo htmlspecialchars($piscina['id_piscina']); ?>" <?php echo ($carril['id_piscina'] == $piscina['id_piscina']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($piscina['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($carril['descripcion']); ?>" maxlength="100">
        </div>
        <div class="form-group">
            <label for="numero_carril">Número de Sub Area</label>
            <input type="number" id="numero_carril" name="numero_carril" value="<?php echo htmlspecialchars($carril['numero_carril']); ?>" required>
        </div>
        <div class="form-group">
            <label for="capacidad_maxima">Capacidad Máxima de Clientes</label>
            <input type="number" id="capacidad_maxima" name="capacidad_maxima" value="<?php echo htmlspecialchars($carril['capacidad_maxima']); ?>" required>
        </div>
        <div class="form-actions">
            <a href="index.php?url=carriles" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Actualizar Sub Area</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
