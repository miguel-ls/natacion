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
    <h2>Añadir Nuevo Carril</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=carriles/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <div class="form-group">
            <label for="id_piscina">Piscina</label>
            <select id="id_piscina" name="id_piscina" required>
                <option value="">Seleccione una piscina</option>
                <?php foreach ($piscinas as $piscina): ?>
                    <option value="<?php echo htmlspecialchars($piscina['id_piscina']); ?>">
                        <?php echo htmlspecialchars($piscina['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="numero_carril">Número de Carril</label>
            <input type="number" id="numero_carril" name="numero_carril" required>
        </div>
        <div class="form-group">
            <label for="capacidad_maxima">Capacidad Máxima de Alumnos</label>
            <input type="number" id="capacidad_maxima" name="capacidad_maxima" required>
        </div>
        <div class="form-actions">
            <a href="index.php?url=carriles" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar Carril</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
