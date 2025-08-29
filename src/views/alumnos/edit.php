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
                <input type="text" id="documento_identidad" name="documento_identidad" value="<?php echo htmlspecialchars($alumno['documento_identidad']); ?>" maxlength="8" pattern="[0-9]{8}" title="El DNI debe contener 8 dígitos numéricos.">
                <div id="dni-error" class="error-text" style="display: none;"></div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const dniInput = document.getElementById('documento_identidad');
    const dniError = document.getElementById('dni-error');
    const submitButton = document.querySelector('button[type="submit"]');
    const studentId = document.querySelector('input[name="id_alumno"]').value;
    const originalDni = "<?php echo htmlspecialchars($alumno['documento_identidad']); ?>";

    let isDniDuplicate = false;
    let isDniInvalid = false;

    function validateDniFormat() {
        const dni = dniInput.value.trim();
        if (dni === '') {
            dniError.style.display = 'none';
            isDniInvalid = false;
            updateSubmitButtonState();
            return;
        }

        if (!/^[0-9]{8}$/.test(dni)) {
            dniError.textContent = 'El DNI debe contener 8 dígitos numéricos.';
            dniError.style.display = 'block';
            isDniInvalid = true;
        } else {
            dniError.style.display = 'none';
            isDniInvalid = false;
        }
        updateSubmitButtonState();
    }

    function checkDniDuplication() {
        const dni = dniInput.value.trim();
        if (isDniInvalid || dni === '' || dni === originalDni) {
            isDniDuplicate = false;
            updateSubmitButtonState();
            return;
        }

        fetch(`index.php?url=alumnos/checkDni&dni=${dni}&id=${studentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    dniError.textContent = 'Este documento de identidad ya está registrado.';
                    dniError.style.display = 'block';
                    isDniDuplicate = true;
                } else {
                    if (!isDniInvalid) {
                        dniError.style.display = 'none';
                    }
                    isDniDuplicate = false;
                }
                updateSubmitButtonState();
            })
            .catch(error => {
                console.error('Error al verificar el DNI:', error);
                isDniDuplicate = false;
                updateSubmitButtonState();
            });
    }

    function updateSubmitButtonState() {
        submitButton.disabled = isDniDuplicate || isDniInvalid;
    }

    dniInput.addEventListener('input', validateDniFormat);
    dniInput.addEventListener('blur', checkDniDuplication);

    form.addEventListener('submit', function(event) {
        validateDniFormat();
        // No llamamos a checkDniDuplication en submit para evitar el delay del fetch,
        // la validación del blur ya debería haber seteado el estado.

        if (isDniInvalid) {
            event.preventDefault();
            alert('Por favor, corrija los errores en el formulario antes de guardar.\nEl DNI debe tener 8 dígitos numéricos.');
        } else if (isDniDuplicate) {
            event.preventDefault();
            alert('No se puede actualizar el alumno porque el documento de identidad ya está en uso por otro alumno.');
        }
    });
});
</script>
