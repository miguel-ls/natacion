<?php
// Incluir el controlador de autenticación para usar el método checkAuth
require_once __DIR__ . '/../controllers/AuthController.php';
$auth = new AuthController();
$auth->checkAuth(); // Proteger esta página

// Incluir la cabecera de la página
require_once __DIR__ . '/partials/header.php';
?>

<div class="dashboard-container" style="padding: 2rem;">
    <h2>Panel de Administración</h2>
    <p>¡Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
    <p>Desde aquí podrás gestionar todo el sistema de matrículas de natación.</p>

    <!-- Aquí se podrían añadir tarjetas o enlaces a las diferentes secciones -->
    <div class="dashboard-links" style="margin-top: 2rem;">
        <a href="index.php?url=alumnos" style="margin-right: 1rem;">Gestionar Alumnos</a>
        <a href="index.php?url=profesores" style="margin-right: 1rem;">Gestionar Profesores</a>
        <a href="index.php?url=cursos">Gestionar Cursos</a>
        <!-- Y así sucesivamente para las demás entidades -->
    </div>
</div>

<?php
// Incluir el pie de página
require_once __DIR__ . '/partials/footer.php';
?>
