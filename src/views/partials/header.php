<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Matrícula de Natación</title>
    <!-- Aquí se podrían enlazar los archivos CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <!-- El menú de navegación irá aquí -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/dashboard">Dashboard</a>
                <a href="/alumnos">Alumnos</a>
                <a href="/profesores">Profesores</a>
                <a href="/cursos">Cursos</a>
                <a href="/matriculas">Matrículas</a>
                <a href="/reportes">Reportes</a>
                <a href="/logout">Cerrar Sesión</a>
            <?php else: ?>
                <a href="/login">Iniciar Sesión</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
