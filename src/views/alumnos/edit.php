<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<style>
/* Estilos para formularios (reutilizados) */
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
    <h2>Editar Alumno</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=alumnos/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->getCsrfToken(); ?>">
        <input type="hidden" name="id_alumno" value="<?php echo htmlspecialchars($alumno['id_alumno']); ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="nombres">Nombres</label>
                <input type="text" id="nombres" name="nombres" value="<?php echo htmlspecialchars($alumno['nombres']); ?>" required>
            </div>
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($alumno['apellidos']); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="documento_identidad">Documento de Identidad</label>
                <input type="text" id="documento_identidad" name="documento_identidad" value="<?php echo htmlspecialchars($alumno['documento_identidad']); ?>">
            </div>
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($alumno['fecha_nacimiento']); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="grupo_sanguineo">Grupo Sanguíneo</label>
                <input type="text" id="grupo_sanguineo" name="grupo_sanguineo" value="<?php echo htmlspecialchars($alumno['grupo_sanguineo']); ?>">
            </div>
             <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($alumno['telefono']); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($alumno['email']); ?>">
            </div>
             <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($alumno['direccion']); ?>">
            </div>
        </div>
        <hr>
        <h4>Información de Contacto de Emergencia</h4>
        <div class="form-row">
            <div class="form-group">
                <label for="nombre_padre_tutor">Nombre del Padre o Tutor</label>
                <input type="text" id="nombre_padre_tutor" name="nombre_padre_tutor" value="<?php echo htmlspecialchars($alumno['nombre_padre_tutor']); ?>">
            </div>
            <div class="form-group">
                <label for="telefono_emergencia">Teléfono de Emergencia</label>
                <input type="tel" id="telefono_emergencia" name="telefono_emergencia" value="<?php echo htmlspecialchars($alumno['telefono_emergencia']); ?>">
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?url=alumnos" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Actualizar Alumno</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
