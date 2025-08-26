<?php
// Iniciar sesión para poder usar variables $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar la configuración y clases base
require_once '../config/database.php';
require_once '../src/core/Database.php';
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
require_once '../src/controllers/ReporteController.php';

// Simple enrutador basado en el parámetro 'url'
$url = $_GET['url'] ?? 'home';

// Dividir la URL para manejar rutas como "alumnos/create"
$parts = explode('/', $url);
$route = $parts[0];

// Inicializar controladores
$authController = new AuthController();
$alumnoController = new AlumnoController();
$profesorController = new ProfesorController();
$cursoController = new CursoController();
$tipoPiscinaController = new TipoPiscinaController();
$piscinaController = new PiscinaController();
$carrilController = new CarrilController();
$tipoHorarioController = new TipoHorarioController();
$horarioController = new HorarioController();
$formaPagoController = new FormaPagoController();
$usuarioController = new UsuarioController();
$matriculaController = new MatriculaController();
$asistenciaProfesorController = new AsistenciaProfesorController();
$reporteController = new ReporteController();

switch ($route) {
    // Rutas de Autenticación
    case 'login':
        $authController->login();
        break;
    case 'verify_2fa':
        $authController->verify_2fa();
        break;
    case 'dashboard':
        $authController->dashboard();
        break;
    case 'logout':
        $authController->logout();
        break;

    // Rutas de Alumnos
    case 'alumnos':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $alumnoController->index();
                break;
            case 'create':
                $alumnoController->create();
                break;
            case 'store':
                $alumnoController->store();
                break;
            case 'edit':
                $alumnoController->edit();
                break;
            case 'update':
                $alumnoController->update();
                break;
            case 'delete':
                $alumnoController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Alumnos</h1>";
                break;
        }
        break;

    // Rutas de Profesores
    case 'profesores':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $profesorController->index();
                break;
            case 'create':
                $profesorController->create();
                break;
            case 'store':
                $profesorController->store();
                break;
            case 'edit':
                $profesorController->edit();
                break;
            case 'update':
                $profesorController->update();
                break;
            case 'delete':
                $profesorController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Profesores</h1>";
                break;
        }
        break;

    // Rutas de Cursos
    case 'cursos':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $cursoController->index();
                break;
            case 'create':
                $cursoController->create();
                break;
            case 'store':
                $cursoController->store();
                break;
            case 'edit':
                $cursoController->edit();
                break;
            case 'update':
                $cursoController->update();
                break;
            case 'delete':
                $cursoController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Cursos</h1>";
                break;
        }
        break;

    // Rutas de Tipos de Piscina
    case 'tipos_piscina':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $tipoPiscinaController->index();
                break;
            case 'create':
                $tipoPiscinaController->create();
                break;
            case 'store':
                $tipoPiscinaController->store();
                break;
            case 'edit':
                $tipoPiscinaController->edit();
                break;
            case 'update':
                $tipoPiscinaController->update();
                break;
            case 'delete':
                $tipoPiscinaController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Tipos de Piscina</h1>";
                break;
        }
        break;

    // Rutas de Piscinas
    case 'piscinas':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $piscinaController->index();
                break;
            case 'create':
                $piscinaController->create();
                break;
            case 'store':
                $piscinaController->store();
                break;
            case 'edit':
                $piscinaController->edit();
                break;
            case 'update':
                $piscinaController->update();
                break;
            case 'delete':
                $piscinaController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Piscinas</h1>";
                break;
        }
        break;

    // Rutas de Carriles
    case 'carriles':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $carrilController->index();
                break;
            case 'create':
                $carrilController->create();
                break;
            case 'store':
                $carrilController->store();
                break;
            case 'edit':
                $carrilController->edit();
                break;
            case 'update':
                $carrilController->update();
                break;
            case 'delete':
                $carrilController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Carriles</h1>";
                break;
        }
        break;

    // Rutas de Tipos de Horario
    case 'tipos_horario':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $tipoHorarioController->index();
                break;
            case 'create':
                $tipoHorarioController->create();
                break;
            case 'store':
                $tipoHorarioController->store();
                break;
            case 'edit':
                $tipoHorarioController->edit();
                break;
            case 'update':
                $tipoHorarioController->update();
                break;
            case 'delete':
                $tipoHorarioController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Tipos de Horario</h1>";
                break;
        }
        break;

    // Rutas de Horarios
    case 'horarios':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $horarioController->index();
                break;
            case 'create':
                $horarioController->create();
                break;
            case 'store':
                $horarioController->store();
                break;
            case 'edit':
                $horarioController->edit();
                break;
            case 'update':
                $horarioController->update();
                break;
            case 'delete':
                $horarioController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Horarios</h1>";
                break;
        }
        break;

    // Rutas de Formas de Pago
    case 'formas_pago':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $formaPagoController->index();
                break;
            case 'create':
                $formaPagoController->create();
                break;
            case 'store':
                $formaPagoController->store();
                break;
            case 'edit':
                $formaPagoController->edit();
                break;
            case 'update':
                $formaPagoController->update();
                break;
            case 'delete':
                $formaPagoController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Formas de Pago</h1>";
                break;
        }
        break;

    // Rutas de Usuarios
    case 'usuarios':
        $action = $parts[1] ?? 'index';
        switch ($action) {
            case 'index':
                $usuarioController->index();
                break;
            case 'create':
                $usuarioController->create();
                break;
            case 'store':
                $usuarioController->store();
                break;
            case 'edit':
                $usuarioController->edit();
                break;
            case 'update':
                $usuarioController->update();
                break;
            case 'delete':
                $usuarioController->delete();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Usuarios</h1>";
                break;
        }
        break;

    // Rutas de Matrículas
    case 'matriculas':
        $action = $parts[1] ?? 'index';
        switch($action) {
            case 'index':
                $matriculaController->index();
                break;
            case 'create':
                $matriculaController->create();
                break;
            case 'store':
                $matriculaController->store();
                break;
            case 'getHorariosByCurso':
                $matriculaController->getHorariosByCurso();
                break;
            case 'show':
                $matriculaController->show();
                break;
            case 'cancel':
                $matriculaController->cancel();
                break;
            case 'edit':
                $matriculaController->edit();
                break;
            case 'update':
                $matriculaController->update();
                break;
            case 'updateDiaClase':
                $matriculaController->updateDiaClase();
                break;
            case 'addRecuperacion':
                $matriculaController->addRecuperacion();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Matrículas</h1>";
                break;
        }
        break;

    // Rutas de Asistencia de Profesores
    case 'asistencias_profesor':
        $action = $parts[1] ?? 'index';
        switch($action) {
            case 'index':
                $asistenciaProfesorController->index();
                break;
            case 'save':
                $asistenciaProfesorController->save();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Acción no encontrada en Asistencia de Profesores</h1>";
                break;
        }
        break;

    // Rutas de Reportes
    case 'reportes':
        $action = $parts[1] ?? 'ventas'; // Por defecto, el reporte de ventas
        switch($action) {
            case 'ventas':
                $reporteController->ventas();
                break;
            case 'profesores':
                $reporteController->profesores();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 - Reporte no encontrado</h1>";
                break;
        }
        break;

    case 'home':
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?url=dashboard');
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
