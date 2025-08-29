<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
?>

<style>
/* Estilos para formularios */
.form-container { max-width: 800px; margin: 2rem auto; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; }
.form-row { display: flex; flex-wrap: wrap; margin: -0.5rem; }
.form-group { flex: 1 1 300px; padding: 0.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; }
.form-group input, .form-group select { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
.form-actions { padding: 0.5rem; margin-top: 1rem; text-align: right; }
.btn { padding: 10px 15px; border: none; border-radius: 4px; color: white; text-decoration: none; cursor: pointer; }
.btn-success { background-color: #5cb85c; }
.btn-secondary { background-color: #6c757d; }
.error-text { color: red; font-size: 0.875em; margin-top: 0.25rem; }
</style>

<div class="form-container">
    <h2>Añadir Nuevo Alumno</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=alumnos/store" method="POST">
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
                <input type="text" id="documento_identidad" name="documento_identidad">
                <div id="dni-error" class="error-text" style="display: none;"></div>
            </div>
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="grupo_sanguineo">Grupo Sanguíneo</label>
                <input type="text" id="grupo_sanguineo" name="grupo_sanguineo">
            </div>
             <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email">
            </div>
             <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion">
            </div>
        </div>
        <hr>
        <h4>Información de Contacto de Emergencia</h4>
        <div class="form-row">
            <div class="form-group">
                <label for="nombre_padre_tutor">Nombre del Padre o Tutor</label>
                <input type="text" id="nombre_padre_tutor" name="nombre_padre_tutor">
            </div>
            <div class="form-group">
                <label for="telefono_emergencia">Teléfono de Emergencia</label>
                <input type="tel" id="telefono_emergencia" name="telefono_emergencia">
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?url=alumnos" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar Alumno</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dniInput = document.getElementById('documento_identidad');
    const dniError = document.getElementById('dni-error');
    const submitButton = document.querySelector('button[type="submit"]');

    dniInput.addEventListener('blur', function() {
        const dni = this.value.trim();
        if (dni === '') {
            dniError.style.display = 'none';
            submitButton.disabled = false;
            return;
        }

        fetch(`index.php?url=alumnos/checkDni&dni=${dni}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    dniError.textContent = 'Este documento de identidad ya está registrado.';
                    dniError.style.display = 'block';
                    submitButton.disabled = true;
                } else {
                    dniError.style.display = 'none';
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error al verificar el DNI:', error);
                // En caso de error de red, permitir el envío para que la validación del backend actúe
                submitButton.disabled = false;
            });
    });
});
</script>
