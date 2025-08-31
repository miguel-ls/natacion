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
                // Asignación de color determinista por horario
                $event_color = $pastel_colors[$evento['id_horario'] % count($pastel_colors)];

                // Crear el título directamente
                $formatted_time = date('h:i A', strtotime($evento['hora_inicio']));
                $title = sprintf(
                    "%s %s - %s\n%s\nAlumno: %s",
                    $formatted_time,
                    $evento['curso_nombre'],
                    $evento['profesor_nombre'],
                    $evento['area_nombre'] . ': ' . $evento['sub_area_descripcion'] . ' ' . $evento['sub_area_numero'],
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
