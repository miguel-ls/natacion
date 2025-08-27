<?php
// Cargar la configuración para tener acceso a BASE_URL, si existe.
if (file_exists(__DIR__ . '/../../config/database.php')) {
    require_once __DIR__ . '/../../config/database.php';
}

// Definir BASE_URL si no está definida para evitar errores.
if (!defined('BASE_URL')) {
    // Intenta autodetectar la URL base, esto puede necesitar ajustes en entornos complejos.
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $protocol . "://" . $host . $script_name);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Matrícula de Natación</title>

    <!-- jQuery y jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
    /* Estilos para el menú desplegable */
    .dropdown {
        position: relative;
        display: inline-block;
    }
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #004a99;
        min-width: 200px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        border-radius: 4px;
        padding: 5px 0;
    }
    .dropdown-content a {
        color: white;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        text-align: left;
    }
    .dropdown-content a:hover {
        background-color: #0056b3;
    }
    .dropdown:hover .dropdown-content {
        display: block;
    }
    .nav-links {
        flex-grow: 1;
    }
    .nav-user {
        margin-left: auto;
    }
    header nav {
        justify-content: space-between;
    }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1><a href="<?php echo BASE_URL; ?>index.php?url=dashboard">NataciónSys</a></h1>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>index.php?url=dashboard">Dashboard</a>

                    <div class="dropdown">
                        <a href="#">Configuración &#9662;</a>
                        <div class="dropdown-content">
                            <a href="<?php echo BASE_URL; ?>index.php?url=tipos_piscina">Tipos de piscina</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=piscinas">Piscina</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=carriles">Carriles</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=tipos_precio">Tipos de pago</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=formas_pago">Forma de pago</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=tipos_horario">Tipo de horario</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=cursos">Cursos</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=precios_cursos">Lista de Precios</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=profesores">Profesores</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a href="#">Operaciones &#9662;</a>
                        <div class="dropdown-content">
                            <a href="<?php echo BASE_URL; ?>index.php?url=alumnos">Alumnos</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=matriculas">Matriculas</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=horarios">Programar horarios</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=asistencias_profesor">Asistencia prof.</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a href="#">Reportes &#9662;</a>
                        <div class="dropdown-content">
                            <a href="<?php echo BASE_URL; ?>index.php?url=reportes/ventas">Reporte de ventas</a>
                            <a href="<?php echo BASE_URL; ?>index.php?url=reportes/profesores">Reporte de profesores</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a href="#">Seguridad &#9662;</a>
                        <div class="dropdown-content">
                            <a href="<?php echo BASE_URL; ?>index.php?url=usuarios">Usuarios</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="nav-user">
                 <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>index.php?url=logout">Cerrar Sesión</a>
                 <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>index.php?url=login">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>
    <div class="container"> <!-- Añadido container para envolver el contenido principal -->
