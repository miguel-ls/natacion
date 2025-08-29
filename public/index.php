<?php
// Iniciar sesión para poder usar variables $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar la configuración y clases base
require_once '../config/database.php';
require_once '../src/core/Database.php';

// Requerir todos los controladores
require_once '../src/controllers/AuthController.php';
require_once '../src/controllers/AlumnoController.php';
require_once '../src/controllers/ProfesorController.php';
require_once '../src/controllers/CursoController.php';
require_once '../src/controllers/TipoPiscinaController.php';
require_once '../src/controllers/PiscinaController.php';
require_once '../src/controllers/CarrilController.php';
require_once '../src/controllers/TipoHorarioController.php';
require_once '../src/controllers/HorarioController.php';
require_once '../src/controllers/FormaPagoController.php';
require_once '../src/controllers/UsuarioController.php';
require_once '../src/controllers/MatriculaController.php';
require_once '../src/controllers/AsistenciaProfesorController.php';
require_once '../src/controllers/AsistenciaAlumnoController.php';
require_once '../src/controllers/ReporteController.php';
require_once '../src/controllers/TipoPrecioController.php';
require_once '../src/controllers/PrecioCursoController.php';
require_once '../src/controllers/DashboardController.php';
require_once '../src/controllers/InicioController.php';


// Simple enrutador basado en el parámetro 'url'
$url = $_GET['url'] ?? 'home';

// Dividir la URL para manejar rutas como "alumnos/create"
$parts = explode('/', $url);
$route = $parts[0];

// La inicialización de los controladores se mueve dentro de cada case
// para evitar la verificación de autenticación en páginas públicas.

switch ($route) {
    // Rutas de Autenticación
    case 'login':
        $authController = new AuthController();
        $authController->login();
        break;
    case 'verify_2fa':
        $authController = new AuthController();
        $authController->verify_2fa();
        break;
    case 'dashboard':
        $dashboardController = new DashboardController();
        $action = $parts[1] ?? 'index';
        if ($action === 'getAvailableSchedules') {
            $dashboardController->getAvailableSchedules();
        } else {
            $dashboardController->index();
        }
        break;
    case 'logout':
        $authController = new AuthController();
        $authController->logout();
        break;

    // Rutas de Alumnos
    case 'alumnos':
        $alumnoController = new AlumnoController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $alumnoController->index(); break;
            case 'create': $alumnoController->create(); break;
            case 'store': $alumnoController->store(); break;
            case 'edit': $alumnoController->edit(); break;
            case 'update': $alumnoController->update(); break;
            case 'delete': $alumnoController->delete(); break;
            case 'search': $alumnoController->search(); break;
            case 'checkDni': $alumnoController->checkDni(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Alumnos</h1>"; break;
        }
        break;

    // Rutas de Precios de Cursos
    case 'precios_cursos':
        $precioCursoController = new PrecioCursoController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $precioCursoController->index(); break;
            case 'create': $precioCursoController->create(); break;
            case 'store': $precioCursoController->store(); break;
            case 'edit': $precioCursoController->edit(); break;
            case 'update': $precioCursoController->update(); break;
            case 'delete': $precioCursoController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Precios</h1>"; break;
        }
        break;

    // Rutas de Tipos de Precio
    case 'tipos_precio':
        $tipoPrecioController = new TipoPrecioController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $tipoPrecioController->index(); break;
            case 'create': $tipoPrecioController->create(); break;
            case 'store': $tipoPrecioController->store(); break;
            case 'edit': $tipoPrecioController->edit(); break;
            case 'update': $tipoPrecioController->update(); break;
            case 'delete': $tipoPrecioController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Tipos de Precio</h1>"; break;
        }
        break;

    // Rutas de Profesores
    case 'profesores':
        $profesorController = new ProfesorController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $profesorController->index(); break;
            case 'create': $profesorController->create(); break;
            case 'store': $profesorController->store(); break;
            case 'edit': $profesorController->edit(); break;
            case 'update': $profesorController->update(); break;
            case 'delete': $profesorController->delete(); break;
            case 'search': $profesorController->search(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Profesores</h1>"; break;
        }
        break;

    // Rutas de Cursos
    case 'cursos':
        $cursoController = new CursoController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $cursoController->index(); break;
            case 'create': $cursoController->create(); break;
            case 'store': $cursoController->store(); break;
            case 'edit': $cursoController->edit(); break;
            case 'update': $cursoController->update(); break;
            case 'delete': $cursoController->delete(); break;
            case 'search': $cursoController->search(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Cursos</h1>"; break;
        }
        break;

    // Rutas de Tipos de Piscina
    case 'tipos_piscina':
        $tipoPiscinaController = new TipoPiscinaController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $tipoPiscinaController->index(); break;
            case 'create': $tipoPiscinaController->create(); break;
            case 'store': $tipoPiscinaController->store(); break;
            case 'edit': $tipoPiscinaController->edit(); break;
            case 'update': $tipoPiscinaController->update(); break;
            case 'delete': $tipoPiscinaController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Tipos de Piscina</h1>"; break;
        }
        break;

    // Rutas de Piscinas
    case 'piscinas':
        $piscinaController = new PiscinaController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $piscinaController->index(); break;
            case 'create': $piscinaController->create(); break;
            case 'store': $piscinaController->store(); break;
            case 'edit': $piscinaController->edit(); break;
            case 'update': $piscinaController->update(); break;
            case 'delete': $piscinaController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Piscinas</h1>"; break;
        }
        break;

    // Rutas de Carriles
    case 'carriles':
        $carrilController = new CarrilController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $carrilController->index(); break;
            case 'create': $carrilController->create(); break;
            case 'store': $carrilController->store(); break;
            case 'edit': $carrilController->edit(); break;
            case 'update': $carrilController->update(); break;
            case 'delete': $carrilController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Carriles</h1>"; break;
        }
        break;

    // Rutas de Tipos de Horario
    case 'tipos_horario':
        $tipoHorarioController = new TipoHorarioController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $tipoHorarioController->index(); break;
            case 'create': $tipoHorarioController->create(); break;
            case 'store': $tipoHorarioController->store(); break;
            case 'edit': $tipoHorarioController->edit(); break;
            case 'update': $tipoHorarioController->update(); break;
            case 'delete': $tipoHorarioController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Tipos de Horario</h1>"; break;
        }
        break;

    // Rutas de Horarios
    case 'horarios':
        $horarioController = new HorarioController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $horarioController->index(); break;
            case 'create': $horarioController->create(); break;
            case 'store': $horarioController->store(); break;
            case 'edit': $horarioController->edit(); break;
            case 'update': $horarioController->update(); break;
            case 'delete': $horarioController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Horarios</h1>"; break;
        }
        break;

    // Rutas de Formas de Pago
    case 'formas_pago':
        $formaPagoController = new FormaPagoController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $formaPagoController->index(); break;
            case 'create': $formaPagoController->create(); break;
            case 'store': $formaPagoController->store(); break;
            case 'edit': $formaPagoController->edit(); break;
            case 'update': $formaPagoController->update(); break;
            case 'delete': $formaPagoController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Formas de Pago</h1>"; break;
        }
        break;

    // Rutas de Usuarios
    case 'usuarios':
        $usuarioController = new UsuarioController();
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index': $usuarioController->index(); break;
            case 'create': $usuarioController->create(); break;
            case 'store': $usuarioController->store(); break;
            case 'edit': $usuarioController->edit(); break;
            case 'update': $usuarioController->update(); break;
            case 'delete': $usuarioController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Usuarios</h1>"; break;
        }
        break;

    // Rutas de Matrículas
    case 'matriculas':
        $matriculaController = new MatriculaController();
        $action = $parts[1] ?? 'index';
        switch($action) {
            case 'index': $matriculaController->index(); break;
            case 'create': $matriculaController->create(); break;
            case 'store': $matriculaController->store(); break;
            case 'show': $matriculaController->show(); break;
            case 'edit': $matriculaController->edit(); break;
            case 'update': $matriculaController->update(); break;
            case 'cancel': $matriculaController->cancel(); break;
            case 'getHorariosByCurso': $matriculaController->getHorariosByCurso(); break;
            case 'updateDiaClase': $matriculaController->updateDiaClase(); break;
            case 'addRecuperacion': $matriculaController->addRecuperacion(); break;
            case 'getPrecioByFecha': $matriculaController->getPrecioByFecha(); break;
            case 'delete': $matriculaController->delete(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Matrículas</h1>"; break;
        }
        break;

    // Rutas de Asistencia de Profesores
    case 'asistencias_profesor':
        $asistenciaProfesorController = new AsistenciaProfesorController();
        $action = $parts[1] ?? 'index';
        switch($action) {
            case 'index': $asistenciaProfesorController->index(); break;
            case 'show': $asistenciaProfesorController->show(); break;
            case 'save': $asistenciaProfesorController->save(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Asistencia de Profesores</h1>"; break;
        }
        break;

    // Rutas de Asistencia de Alumnos
    case 'asistencias_alumnos':
        $asistenciaAlumnoController = new AsistenciaAlumnoController();
        $action = $parts[1] ?? 'index';
        switch($action) {
            case 'index': $asistenciaAlumnoController->index(); break;
            case 'show': $asistenciaAlumnoController->show(); break;
            case 'save': $asistenciaAlumnoController->save(); break;
            default: http_response_code(404); echo "<h1>404 - Acción no encontrada en Asistencia de Alumnos</h1>"; break;
        }
        break;

    // Rutas de Reportes
    case 'reportes':
        $reporteController = new ReporteController();
        $action = $parts[1] ?? 'ventas'; // Por defecto, el reporte de ventas
        switch($action) {
            case 'ventas': $reporteController->ventas(); break;
            case 'profesores': $reporteController->profesores(); break;
            case 'exportarVentas': $reporteController->exportarVentas(); break;
            case 'ventasPorFormaPago': $reporteController->ventasPorFormaPago(); break;
            case 'ventasPorCurso': $reporteController->ventasPorCurso(); break;
            case 'ventasPorProfesor': $reporteController->ventasPorProfesor(); break;
            case 'ventasPorPiscinaCarril': $reporteController->ventasPorPiscinaCarril(); break;
            default: http_response_code(404); echo "<h1>404 - Reporte no encontrado</h1>"; break;
        }
        break;

    case 'inicio':
        $inicioController = new InicioController();
        $inicioController->index();
        break;

    case 'home':
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?url=inicio'); // Redirigir a la nueva página de inicio
        } else {
            header('Location: index.php?url=login');
        }
        exit;

    default:
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
        break;
}
?>
