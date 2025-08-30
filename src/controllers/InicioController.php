<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class InicioController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();

        // Obtener filtros de la URL o usar valores por defecto
        $selected_year = (int)($_GET['year'] ?? date('Y'));
        $selected_month = (int)($_GET['month'] ?? 0); // 0 para "Todos"

        $chart_data = [];
        $years = [];
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // Determinar el texto para los títulos de los gráficos
        if ($selected_month == 0) {
            $title_period_text = "Año " . $selected_year;
        } else {
            $title_period_text = $months[$selected_month] . " " . $selected_year;
        }

        try {
            // Obtener los años disponibles para el filtro
            $stmt_years = $db->prepare("CALL sp_get_matricula_years()");
            $stmt_years->execute();
            $years = $stmt_years->fetchAll(PDO::FETCH_ASSOC);
            $stmt_years->closeCursor();

            // Datos para Gráfico 1: Ventas mensuales por curso
            $stmt1 = $db->prepare("CALL sp_get_ventas_por_curso_mensual_anual(?)");
            $stmt1->execute([$selected_year]);
            $chart_data['ventas_mensuales_por_curso'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            $stmt1->closeCursor();

            // Datos para Gráfico 2: Ventas totales por curso
            $stmt2 = $db->prepare("CALL sp_get_ventas_por_curso_anual(?, ?)");
            $stmt2->execute([$selected_year, $selected_month]);
            $chart_data['ventas_por_curso'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            $stmt2->closeCursor();

            // Datos para Gráfico 3: Ventas por forma de pago
            $stmt3 = $db->prepare("CALL sp_get_ventas_por_forma_pago_anual(?, ?)");
            $stmt3->execute([$selected_year, $selected_month]);
            $chart_data['ventas_por_forma_pago'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            $stmt3->closeCursor();

            // Datos para Gráfico 4: Ventas por tipo de piscina
            $stmt4 = $db->prepare("CALL sp_get_ventas_por_piscina_anual(?, ?)");
            $stmt4->execute([$selected_year, $selected_month]);
            $chart_data['ventas_por_piscina'] = $stmt4->fetchAll(PDO::FETCH_ASSOC);
            $stmt4->closeCursor();

            // Datos para Gráfico 5: Ventas por tipo de curso
            $stmt5 = $db->prepare("CALL sp_get_ventas_by_tipo_curso(?, ?)");
            $stmt5->execute([$selected_year, $selected_month]);
            $chart_data['ventas_por_tipo_curso'] = $stmt5->fetchAll(PDO::FETCH_ASSOC);
            $stmt5->closeCursor();

        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al cargar los datos para los gráficos: " . $e->getMessage();
        }

        require_once __DIR__ . '/../views/inicio/index.php';
    }

    public function getVentasByTipoCurso() {
        header('Content-Type: application/json');
        $year = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? 0);

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_ventas_by_tipo_curso(?, ?)");
            $stmt->execute([$year, $month]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($data);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
