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
.form-group input, .form-group select { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.form-actions { padding: 0.5rem; margin-top: 1rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
.btn-success { background-color: #5cb85c; }
.btn-secondary { background-color: #6c757d; }
</style>

<div class="form-container">
    <h2>Añadir Nuevo Horario</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    // Recuperar datos del formulario en caso de error
    $form_data = $_SESSION['form_data'] ?? [];
    unset($_SESSION['form_data']);
    ?>

    <form action="index.php?url=horarios/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="id_curso">Curso</label>
                <select id="id_curso" name="id_curso" required>
                    <option value="">Seleccione un curso</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo htmlspecialchars($curso['id_curso']); ?>" <?php echo (isset($form_data['id_curso']) && $form_data['id_curso'] == $curso['id_curso']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($curso['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_profesor">Profesor</label>
                <select id="id_profesor" name="id_profesor" required>
                    <option value="">Seleccione un profesor</option>
                    <?php foreach ($profesores as $profesor): ?>
                        <option value="<?php echo htmlspecialchars($profesor['id_profesor']); ?>" <?php echo (isset($form_data['id_profesor']) && $form_data['id_profesor'] == $profesor['id_profesor']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="id_carril">Area y Sub Area</label>
                <select id="id_carril" name="id_carril" required>
                    <option value="">Seleccione una sub area</option>
                    <?php foreach ($carriles as $carril): ?>
                        <option value="<?php echo htmlspecialchars($carril['id_carril']); ?>" <?php echo (isset($form_data['id_carril']) && $form_data['id_carril'] == $carril['id_carril']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($carril['piscina_nombre'] . ' - Sub Area ' . $carril['numero_carril']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_tipo_horario">Tipo de Horario (Días)</label>
                <select id="id_tipo_horario" name="id_tipo_horario" required>
                    <option value="">Seleccione los días</option>
                    <?php foreach ($tipos_horario as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo['id_tipo_horario']); ?>" <?php echo (isset($form_data['id_tipo_horario']) && $form_data['id_tipo_horario'] == $tipo['id_tipo_horario']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipo['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="hora_inicio">Hora de Inicio</label>
                <input type="time" id="hora_inicio" name="hora_inicio" value="<?php echo htmlspecialchars($form_data['hora_inicio'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="hora_fin">Hora de Fin</label>
                <input type="time" id="hora_fin" name="hora_fin" value="<?php echo htmlspecialchars($form_data['hora_fin'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_inicio">Fecha de Inicio del Horario</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($form_data['fecha_inicio'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="fecha_fin">Fecha de Fin del Horario</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($form_data['fecha_fin'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?url=horarios" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar Horario</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
