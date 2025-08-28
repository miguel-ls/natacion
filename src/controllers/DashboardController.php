<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class DashboardController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $schedules = [];

        try {
            $stmt = $db->prepare("CALL sp_get_all_horarios_disponibles()");
            $stmt->execute();
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error fetching available schedules: " . $e->getMessage();
        }

        require_once __DIR__ . '/../views/dashboard.php';
    }

    /**
     * Endpoint AJAX para obtener la lista de horarios disponibles.
     * Devuelve solo el HTML de la grilla.
     */
    public function getAvailableSchedules() {
        $db = Database::getInstance()->getConnection();
        $schedules = [];

        try {
            $stmt = $db->prepare("CALL sp_get_all_horarios_disponibles()");
            $stmt->execute();
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            // En caso de error, el array $schedules estará vacío y el parcial mostrará un mensaje.
            error_log("AJAX Error fetching schedules: " . $e->getMessage());
        }

        // Renderizar solo el parcial de la vista
        require __DIR__ . '/../views/dashboard/_schedules_list.php';
        exit; // Terminar la ejecución para no renderizar nada más
    }
}
?>
