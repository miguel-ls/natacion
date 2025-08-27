<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class AsistenciaAlumnoController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    /**
     * Muestra la lista filtrable de matrículas de alumnos.
     */
    public function index() {
        $id_alumno = (int)($_GET['id_alumno'] ?? 0);
        $id_curso = (int)($_GET['id_curso'] ?? 0);
        $estado = $_GET['estado'] ?? 'Todos';

        $db = Database::getInstance()->getConnection();
        $matriculas = [];
        $alumnos = [];
        $cursos = [];

        try {
            // 1. Obtener las matrículas filtradas
            $stmt = $db->prepare("CALL sp_listar_matriculas_alumno_filtrado(?, ?, ?)");
            $stmt->execute([$id_alumno, $id_curso, $estado]);
            $matriculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // 2. Obtener todos los alumnos para el dropdown de filtro
            $stmt_alumnos = $db->prepare("CALL sp_get_all_alumnos('')");
            $stmt_alumnos->execute();
            $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);
            $stmt_alumnos->closeCursor();

            // 3. Obtener todos los cursos para el dropdown de filtro
            $stmt_cursos = $db->prepare("CALL sp_get_all_cursos('')");
            $stmt_cursos->execute();
            $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
            $stmt_cursos->closeCursor();

        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al cargar datos de asistencia de alumnos: " . $e->getMessage();
        }

        require_once __DIR__ . '/../views/asistencias_alumnos/index.php';
    }

    /**
     * Muestra la página de detalle para marcar la asistencia de un alumno para una matrícula específica.
     */
    public function show() {
        $id_matricula = $_GET['id'] ?? null;
        if (!$id_matricula) {
            header('Location: index.php?url=asistencias_alumnos');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $matricula = null;
        $dias_clase = [];

        try {
            // 1. Obtener detalles de la matrícula y del horario asociado
            $stmt_matricula = $db->prepare("
                SELECT m.*, a.nombres as alumno_nombres, a.apellidos as alumno_apellidos,
                       c.nombre as curso_nombre, th.dias_semana, h.hora_inicio, h.hora_fin
                FROM matriculas m
                JOIN alumnos a ON m.id_alumno = a.id_alumno
                JOIN horarios h ON m.id_horario = h.id_horario
                JOIN cursos c ON h.id_curso = c.id_curso
                JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
                WHERE m.id_matricula = ?
            ");
            $stmt_matricula->execute([$id_matricula]);
            $matricula = $stmt_matricula->fetch(PDO::FETCH_ASSOC);
            $stmt_matricula->closeCursor();

            if (!$matricula) {
                throw new Exception("Matrícula no encontrada.");
            }

            // 2. Obtener los días de clase y sus estados desde `matricula_dias`
            $stmt_dias = $db->prepare("CALL sp_get_matricula_dias(?)");
            $stmt_dias->execute([$id_matricula]);
            $dias_clase = $stmt_dias->fetchAll(PDO::FETCH_ASSOC);
            $stmt_dias->closeCursor();

        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header('Location: index.php?url=asistencias_alumnos');
            exit;
        }

        require_once __DIR__ . '/../views/asistencias_alumnos/show.php';
    }

    /**
     * Guarda la asistencia de un alumno (vía AJAX), reutilizando la lógica de Matrícula.
     */
    public function save() {
        header('Content-Type: application/json');
        $this->auth->verifyCsrfToken();

        $id_matricula_dia = $_POST['id_matricula_dia'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $observaciones = $_POST['observaciones'] ?? '';

        if (!$id_matricula_dia || !$estado) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            // Llamar al mismo SP que usa la página de detalles de matrícula para consistencia
            $stmt = $db->prepare("CALL sp_update_asistencia_alumno(?, ?, ?)");
            $stmt->execute([$id_matricula_dia, $estado, $observaciones]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la asistencia: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
