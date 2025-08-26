<?php
require_once __DIR__ . '/partials/header.php';
?>

<style>
/* Reutilizamos los estilos del login para consistencia */
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
}
.error-message {
    color: #a94442;
    background-color: #f2dede;
    border: 1px solid #ebccd1;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
.info-message {
    color: #31708f;
    background-color: #d9edf7;
    border-color: #bce8f1;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
</style>

<div class="login-container">
    <h2>Verificación de Dos Factores</h2>

    <div class="info-message">
        Se ha enviado un código de 6 dígitos a su correo electrónico. Por favor, ingréselo a continuación.
    </div>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="index.php?url=verify_2fa" method="POST">
        <div class="form-group">
            <label for="2fa_code">Código de Verificación</label>
            <input type="text" id="2fa_code" name="2fa_code" required pattern="\d{6}" title="El código debe tener 6 dígitos.">
        </div>
        <button type="submit" class="btn-submit">Verificar Código</button>
    </form>
</div>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
