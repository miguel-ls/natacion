<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();

// Repoblar datos del formulario si existen en la sesión
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
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
    <h2>Añadir Nuevo Cliente</h2>

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
                <input type="text" id="nombres" name="nombres" value="<?php echo htmlspecialchars($form_data['nombres'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($form_data['apellidos'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="id_tipo_documento">Tipo de Documento</label>
                <select id="id_tipo_documento" name="id_tipo_documento" required>
                    <?php foreach ($tipos_documento as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo['id']); ?>" data-longitud="<?php echo htmlspecialchars($tipo['longitud']); ?>" <?php echo (isset($form_data['id_tipo_documento']) && $form_data['id_tipo_documento'] == $tipo['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipo['descripcion']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="documento_identidad">Número de Documento</label>
                <input type="text" id="documento_identidad" name="documento_identidad" value="<?php echo htmlspecialchars($form_data['documento_identidad'] ?? ''); ?>" required>
                <div id="dni-error" class="error-text" style="display: none;"></div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($form_data['fecha_nacimiento'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="grupo_sanguineo">Grupo Sanguíneo</label>
                <input type="text" id="grupo_sanguineo" name="grupo_sanguineo" value="<?php echo htmlspecialchars($form_data['grupo_sanguineo'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($form_data['telefono'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($form_data['direccion'] ?? ''); ?>">
            </div>
        </div>
        <hr>
        <h4>Información de Contacto de Emergencia</h4>
        <div class="form-row">
            <div class="form-group">
                <label for="nombre_padre_tutor">Nombre del Padre o Tutor</label>
                <input type="text" id="nombre_padre_tutor" name="nombre_padre_tutor" value="<?php echo htmlspecialchars($form_data['nombre_padre_tutor'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="telefono_emergencia">Teléfono de Emergencia</label>
                <input type="tel" id="telefono_emergencia" name="telefono_emergencia" value="<?php echo htmlspecialchars($form_data['telefono_emergencia'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?url=alumnos" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar Cliente</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const tipoDocumentoSelect = document.getElementById('id_tipo_documento');
    const documentoInput = document.getElementById('documento_identidad');
    const documentoError = document.getElementById('dni-error');
    const submitButton = document.querySelector('button[type="submit"]');

    let isDocumentoDuplicate = false;
    let isDocumentoInvalid = false;

    function updateDocumentoValidation() {
        const selectedOption = tipoDocumentoSelect.options[tipoDocumentoSelect.selectedIndex];
        const longitud = selectedOption.getAttribute('data-longitud');

        documentoInput.maxLength = longitud;
        documentoInput.pattern = `^[0-9]{${longitud}}$`; // Asumiendo que todos son numéricos por ahora
        documentoInput.title = `El documento debe contener ${longitud} dígitos.`;

        // Disparar la validación del campo de documento inmediatamente
        validateDocumentoFormat();
    }

    function validateDocumentoFormat() {
        const documento = documentoInput.value.trim();
        const longitud = documentoInput.maxLength;

        if (documento === '') {
            documentoError.style.display = 'none';
            isDocumentoInvalid = false;
            updateSubmitButtonState();
            return;
        }

        const regex = new RegExp(`^[a-zA-Z0-9]{${longitud}}$`);
        if (documento.length !== parseInt(longitud)) {
            documentoError.textContent = `El número de documento debe tener exactamente ${longitud} caracteres.`;
            documentoError.style.display = 'block';
            isDocumentoInvalid = true;
        } else {
            documentoError.style.display = 'none';
            isDocumentoInvalid = false;
        }
        updateSubmitButtonState();
    }

    function checkDocumentoDuplication() {
        const documento = documentoInput.value.trim();
        if (isDocumentoInvalid || documento === '') {
            isDocumentoDuplicate = false;
            updateSubmitButtonState();
            return;
        }

        // El endpoint no cambia, la unicidad se sigue verificando en la misma columna
        fetch(`index.php?url=alumnos/checkDni&dni=${documento}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    documentoError.textContent = 'Este número de documento ya está registrado.';
                    documentoError.style.display = 'block';
                    isDocumentoDuplicate = true;
                } else {
                    if (!isDocumentoInvalid) {
                        documentoError.style.display = 'none';
                    }
                    isDocumentoDuplicate = false;
                }
                updateSubmitButtonState();
            })
            .catch(error => {
                console.error('Error al verificar el documento:', error);
                isDocumentoDuplicate = false;
                updateSubmitButtonState();
            });
    }

    function updateSubmitButtonState() {
        submitButton.disabled = isDocumentoDuplicate || isDocumentoInvalid;
    }

    // Event listeners
    tipoDocumentoSelect.addEventListener('change', updateDocumentoValidation);
    documentoInput.addEventListener('input', validateDocumentoFormat);
    documentoInput.addEventListener('blur', checkDocumentoDuplication);

    // Initial validation setup on page load
    updateDocumentoValidation();

    form.addEventListener('submit', function(event) {
        validateDocumentoFormat();

        if (isDocumentoInvalid) {
            event.preventDefault();
            const longitud = documentoInput.maxLength;
            alert(`Por favor, corrija los errores. El número de documento debe tener ${longitud} caracteres.`);
        } else if (isDocumentoDuplicate) {
            event.preventDefault();
            alert('No se puede guardar el cliente porque el número de documento ya existe.');
        }
    });
});
</script>
