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

        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al cargar los datos para los gráficos: " . $e->getMessage();
        }

        require_once __DIR__ . '/../views/inicio/index.php';
    }
}
?>
