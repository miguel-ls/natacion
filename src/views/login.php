<?php
// Suponiendo que el header inicia la sesión y contiene el doctype, head, etc.
require_once __DIR__ . '/partials/header.php';
?>

<style>
/* Estilos específicos para el login */
.login-container {
    width: 100%;
    max-width: 400px;
    margin: 5rem auto;
    padding: 2rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.login-container h2 {
    text-align: center;
    margin-bottom: 1.5rem;
}
.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
}
.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.btn-submit {
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 4px;
    background-color: #337ab7;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
}
.btn-submit:hover {
    background-color: #286090;
}
.error-message {
    color: #a94442;
    background-color: #f2dede;
    border: 1px solid #ebccd1;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
</style>

<div class="login-container">
    <h2>Acceso al Sistema</h2>

    <?php
    // Mostrar mensajes de error si existen en la sesión
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        // Limpiar el mensaje para que no se muestre de nuevo
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=login" method="POST">
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-submit">Ingresar</button>
    </form>
</div>

<?php
// Incluir el pie de página
require_once __DIR__ . '/partials/footer.php';
?>
