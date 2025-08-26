<?php
// Cargar la configuración para tener acceso a BASE_URL
require_once __DIR__ . '/../../../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Matrícula de Natación</title>
    <!-- La URL del CSS también debe ser absoluta -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
</head>
<body>
    <header>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_URL; ?>index.php?url=dashboard">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>index.php?url=alumnos">Alumnos</a>
                <a href="<?php echo BASE_URL; ?>index.php?url=profesores">Profesores</a>
                <a href="<?php echo BASE_URL; ?>index.php?url=cursos">Cursos</a>
                <a href="<?php echo BASE_URL; ?>index.php?url=matriculas">Matrículas</a>
                <a href="<?php echo BASE_URL; ?>index.php?url=reportes/ventas">Reporte Ventas</a>
                <a href="<?php echo BASE_URL; ?>index.php?url=asistencias_profesor">Asistencia Prof.</a>
                <a href="<?php echo BASE_URL; ?>index.php?url=logout">Cerrar Sesión</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>index.php?url=login">Iniciar Sesión</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
