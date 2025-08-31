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
            $pastel_colors = ['#a8d8ea', '#fce38a', '#eaffd0', '#f4b6c2', '#b39ddb', '#ffcc80', '#b2dfdb', '#ffAB91', '#c5e1a5'];

            foreach ($eventos as $evento) {
                // Asignación de color determinista por matrícula
                $event_color = $pastel_colors[$evento['id_matricula'] % count($pastel_colors)];

                // Crear el título con el nuevo formato solicitado
                $start_time = date('h:i A', strtotime($evento['start_datetime']));
                $end_time = date('h:i A', strtotime($evento['end_datetime']));
                $time_range = $start_time . ' - ' . $end_time;
                $sub_area_info = $evento['sub_area_descripcion'] . ' - ' . $evento['sub_area_numero'];

                $title = sprintf(
                    "%s\n%s\n%s\n%s\nProf: %s\nAlum: %s",
                    $time_range,
                    $evento['curso_nombre'],
                    $evento['area_nombre'],
                    $sub_area_info,
                    $evento['profesor_nombre'],
                    $evento['alumno_nombre']
                );

                $calendar_events[] = [
                    'title' => $title,
                    'start' => $evento['start_datetime'],
                    'end' => $evento['end_datetime'],
                    'backgroundColor' => $event_color,
                    'borderColor' => $event_color,
                    'textColor' => '#333333'
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
