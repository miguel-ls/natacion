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

            // Formatear para FullCalendar y asignar colores pastel de forma determinista
            $calendar_events = [];
            // Paleta de colores pastel actualizada para forzar el reenvío del cambio.
            $pastel_colors = ['#a8d8ea', '#fce38a', '#eaffd0', '#f4b6c2', '#b39ddb', '#ffcc80', '#b2dfdb', '#ffAB91', '#c5e1a5', '#FDD835'];

            foreach ($eventos as $evento) {
                $id_curso = $evento['id_curso'];
                // Asignación de color determinista basada en el ID del curso
                $event_color = $pastel_colors[$id_curso % count($pastel_colors)];

                $calendar_events[] = [
                    'start' => $evento['start_datetime'],
                    'end' => $evento['end_datetime'],
                    'backgroundColor' => $event_color,
                    'borderColor' => $event_color,
                    'textColor' => '#333333', // Un color de texto oscuro para que sea legible
                    'extendedProps' => [
                        'formatted_time' => date('h:i A', strtotime($evento['hora_inicio'])),
                        'curso_nombre' => $evento['curso_nombre'],
                        'profesor_nombre' => $evento['profesor_nombre'],
                        'area_nombre' => $evento['area_nombre'],
                        'sub_area_descripcion' => $evento['sub_area_descripcion'],
                        'sub_area_numero' => $evento['sub_area_numero']
                    ]
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
