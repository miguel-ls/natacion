<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class CalendarioController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    /**
     * Muestra la página principal del calendario.
     */
    public function index() {
        require_once __DIR__ . '/../views/calendario/index.php';
    }

    /**
     * Proporciona los eventos (clases) al calendario en formato JSON.
     */
    public function getEventos() {
        header('Content-Type: application/json');

        $start_date = $_GET['start'] ?? null;
        $end_date = $_GET['end'] ?? null;

        if (!$start_date || !$end_date) {
            echo json_encode([]);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_clases_for_calendar(?, ?)");
            $stmt->execute([$start_date, $end_date]);
            $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $calendar_events = [];
            foreach ($eventos as $evento) {
                $calendar_events[] = [
                    'title' => $evento['title'],
                    'start' => $evento['start_datetime'],
                    'end' => $evento['end_datetime']
                ];
            }

            echo json_encode($calendar_events);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['error' => 'Error al cargar los eventos del calendario.']);
        }
        exit;
    }
}
?>
