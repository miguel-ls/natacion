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
            // You can log the error or set a message for the user
            $_SESSION['error_message'] = "Error fetching available schedules: " . $e->getMessage();
        }

        // The view will now have access to the $schedules variable
        require_once __DIR__ . '/../views/dashboard.php';
    }
}
?>
