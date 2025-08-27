<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class AsistenciaProfesorController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    /**
     * Muestra la interfaz para gestionar la asistencia de profesores para una fecha dada.
     */
    public function index() {
        $search_term = $_GET['search'] ?? '';
        $db = Database::getInstance()->getConnection();
        try {
            // Reutilizamos el sp_get_all_horarios_details que ya tiene un filtro
            $stmt = $db->prepare("CALL sp_get_all_horarios_details(?)");
            $stmt->execute([$search_term]);
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al cargar los horarios: " . $e->getMessage();
            $horarios = [];
        }

        require_once __DIR__ . '/../views/asistencias_profesor/index.php';
    }

    /**
     * Muestra la página de detalle para marcar la asistencia de un profesor para un horario específico.
     */
    public function show() {
        $id_horario = $_GET['id'] ?? null;
        if (!$id_horario) {
            header('Location: index.php?url=asistencias_profesor');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // 1. Obtener detalles del horario
        $stmt_horario = $db->prepare("
            SELECT h.*, th.dias_semana, c.nombre as curso_nombre, CONCAT(p.nombres, ' ', p.apellidos) as profesor_nombre
            FROM horarios h
            JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
            JOIN cursos c ON h.id_curso = c.id_curso
            JOIN profesores p ON h.id_profesor = p.id_profesor
            WHERE h.id_horario = ?
        ");
        $stmt_horario->execute([$id_horario]);
        $horario = $stmt_horario->fetch(PDO::FETCH_ASSOC);
        $stmt_horario->closeCursor();

        if (!$horario) {
            http_response_code(404);
            echo "Horario no encontrado.";
            exit;
        }

        // 2. Generar las fechas de clase esperadas en PHP
        $dias_clase = [];
        if ($horario['fecha_inicio'] && $horario['fecha_fin']) {
            $dias_semana = explode(',', $horario['dias_semana']);
            $fecha_actual = new DateTime($horario['fecha_inicio']);
            $fecha_fin = new DateTime($horario['fecha_fin']);

            while ($fecha_actual <= $fecha_fin) {
                // date('w') es 0-6 (Dom-Sab), DAYOFWEEK() es 1-7 (Dom-Sab). La correspondencia es date('w') + 1.
                $dayOfWeek = (int)$fecha_actual->format('w') + 1;
                if (in_array($dayOfWeek, $dias_semana)) {
                    $dias_clase[$fecha_actual->format('Y-m-d')] = [
                        'fecha_clase' => $fecha_actual->format('Y-m-d'),
                        'estado' => 'no_marcado', // Estado por defecto
                        'observaciones' => ''
                    ];
                }
                $fecha_actual->modify('+1 day');
            }
        }

        // 3. Obtener los registros de asistencia que ya existen
        $stmt_asistencias = $db->prepare("
            SELECT fecha, estado, observaciones
            FROM asistencias_profesores
            WHERE id_horario = ? AND id_profesor = ?
        ");
        $stmt_asistencias->execute([$id_horario, $horario['id_profesor']]);
        $asistencias_guardadas = $stmt_asistencias->fetchAll(PDO::FETCH_ASSOC);
        $stmt_asistencias->closeCursor();

        // 4. Fusionar los estados guardados con la lista generada
        foreach ($asistencias_guardadas as $asistencia) {
            $fecha = $asistencia['fecha'];
            if (isset($dias_clase[$fecha])) {
                $dias_clase[$fecha]['estado'] = $asistencia['estado'];
                $dias_clase[$fecha]['observaciones'] = $asistencia['observaciones'];
            }
        }

        require_once __DIR__ . '/../views/asistencias_profesor/show.php';
    }

    /**
     * Guarda la asistencia de un profesor para un horario y día específico (vía AJAX).
     */
    public function save() {
        header('Content-Type: application/json');
        $this->auth->verifyCsrfToken();

        $id_profesor = $_POST['id_profesor'] ?? null;
        $id_horario = $_POST['id_horario'] ?? null;
        $fecha = $_POST['fecha'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $observaciones = $_POST['observaciones'] ?? '';

        if (!$id_profesor || !$id_horario || !$fecha || !$estado) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_create_or_update_asistencia_profesor(?, ?, ?, ?, ?)");
            $stmt->execute([$id_profesor, $id_horario, $fecha, $estado, $observaciones]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
