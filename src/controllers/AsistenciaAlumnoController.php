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

            // 2. Generar las fechas de clase esperadas
            $dias_semana_num = explode(',', $matricula['dias_semana']);
            $fecha_actual = new DateTime($matricula['fecha_inicio']);
            $fecha_fin = new DateTime($matricula['fecha_fin']);

            while ($fecha_actual <= $fecha_fin) {
                $dayOfWeek = (int)$fecha_actual->format('w') + 1;
                if (in_array($dayOfWeek, $dias_semana_num)) {
                    $dias_clase[$fecha_actual->format('Y-m-d')] = [
                        'fecha_clase' => $fecha_actual->format('Y-m-d'),
                        'estado' => 'no_marcado',
                        'observaciones' => ''
                    ];
                }
                $fecha_actual->modify('+1 day');
            }

            // 3. Obtener los registros de asistencia que ya existen
            $stmt_asistencias = $db->prepare("
                SELECT fecha_clase, estado, observaciones
                FROM asistencias_alumnos
                WHERE id_matricula = ?
            ");
            $stmt_asistencias->execute([$id_matricula]);
            $asistencias_guardadas = $stmt_asistencias->fetchAll(PDO::FETCH_ASSOC);

            // 4. Fusionar los estados guardados
            foreach ($asistencias_guardadas as $asistencia) {
                if (isset($dias_clase[$asistencia['fecha_clase']])) {
                    $dias_clase[$asistencia['fecha_clase']]['estado'] = $asistencia['estado'];
                    $dias_clase[$asistencia['fecha_clase']]['observaciones'] = $asistencia['observaciones'];
                }
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header('Location: index.php?url=asistencias_alumnos');
            exit;
        }

        require_once __DIR__ . '/../views/asistencias_alumnos/show.php';
    }

    /**
     * Guarda la asistencia de un alumno (vía AJAX).
     */
    public function save() {
        header('Content-Type: application/json');
        $this->auth->verifyCsrfToken();

        $id_matricula = $_POST['id_matricula'] ?? null;
        $id_alumno = $_POST['id_alumno'] ?? null;
        $fecha_clase = $_POST['fecha_clase'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $observaciones = $_POST['observaciones'] ?? '';

        if (!$id_matricula || !$id_alumno || !$fecha_clase || !$estado) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_create_or_update_asistencia_alumno(?, ?, ?, ?, ?)");
            $stmt->execute([$id_matricula, $id_alumno, $fecha_clase, $estado, $observaciones]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la asistencia: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
