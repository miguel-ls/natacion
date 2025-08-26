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
        $fecha_seleccionada = $_GET['fecha'] ?? date('Y-m-d');
        $horarios_del_dia = [];

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_horarios_for_day(?)");
            $stmt->execute([$fecha_seleccionada]);
            $horarios_del_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al cargar los horarios del día: " . $e->getMessage();
        }

        require_once __DIR__ . '/../views/asistencias_profesor/index.php';
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
