<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
// Mapa de días corregido según DAYOFWEEK() de MySQL
$dias_semana_map = ['2' => 'Lunes', '3' => 'Martes', '4' => 'Miércoles', '5' => 'Jueves', '6' => 'Viernes', '7' => 'Sábado', '1' => 'Domingo'];
$dias_seleccionados = explode(',', $tipo_horario['dias_semana']);
?>

<style>
/* Reutilizamos los estilos de formularios */
.form-container { max-width: 600px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; }
.form-group input[type="text"] { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.checkbox-group label { margin-right: 15px; }
.form-actions { margin-top: 1rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
.btn-success { background-color: #5cb85c; }
.btn-secondary { background-color: #6c757d; }
</style>

<div class="form-container">
    <h2>Editar Tipo de Horario</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=tipos_horario/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <input type="hidden" name="id_tipo_horario" value="<?php echo htmlspecialchars($tipo_horario['id_tipo_horario']); ?>">

        <div class="form-group">
            <label for="nombre">Nombre del Horario</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($tipo_horario['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label>Días de la Semana</label>
            <div class="checkbox-group">
                <?php foreach ($dias_semana_map as $value => $label): ?>
                    <label>
                        <input type="checkbox" name="dias_semana[]" value="<?php echo $value; ?>" <?php echo in_array($value, $dias_seleccionados) ? 'checked' : ''; ?>>
                        <?php echo $label; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?url=tipos_horario" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Actualizar Tipo de Horario</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
