<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class ReporteController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    /**
     * Muestra el formulario de filtros y los resultados del reporte de ventas.
     */
    public function ventas() {
        $db = Database::getInstance()->getConnection();

        // Valores por defecto y captura de filtros del GET
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $id_alumno = (int)($_GET['id_alumno'] ?? 0);
        $id_curso = (int)($_GET['id_curso'] ?? 0);
        $id_forma_pago = (int)($_GET['id_forma_pago'] ?? 0);

        // Cargar datos para los menús desplegables de los filtros
        $stmt_alumnos = $db->prepare("CALL sp_get_all_alumnos()");
        $stmt_alumnos->execute();
        $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_alumnos->closeCursor();

        $stmt_cursos = $db->prepare("CALL sp_get_all_cursos()");
        $stmt_cursos->execute();
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_cursos->closeCursor();

        $stmt_formas_pago = $db->prepare("CALL sp_get_all_formas_pago()");
        $stmt_formas_pago->execute();
        $formas_pago = $stmt_formas_pago->fetchAll(PDO::FETCH_ASSOC);
        $stmt_formas_pago->closeCursor();

        // Obtener los datos del reporte con los filtros aplicados
        try {
            $stmt_reporte = $db->prepare("CALL sp_reporte_ventas(?, ?, ?, ?, ?)");
            $stmt_reporte->execute([$fecha_inicio, $fecha_fin, $id_alumno, $id_curso, $id_forma_pago]);
            $reporte_data = $stmt_reporte->fetchAll(PDO::FETCH_ASSOC);
            $stmt_reporte->closeCursor();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al generar el reporte: " . $e->getMessage();
            $reporte_data = [];
        }

        require_once __DIR__ . '/../views/reportes/ventas.php';
    }

    /**
     * Muestra el reporte de horas trabajadas por profesor.
     */
    public function profesores() {
        $db = Database::getInstance()->getConnection();

        // Valores por defecto y captura de filtros del GET
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        // Obtener los datos del reporte con los filtros aplicados
        try {
            $stmt_reporte = $db->prepare("CALL sp_reporte_horas_profesor(?, ?)");
            $stmt_reporte->execute([$fecha_inicio, $fecha_fin]);
            $reporte_data = $stmt_reporte->fetchAll(PDO::FETCH_ASSOC);
            $stmt_reporte->closeCursor();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al generar el reporte: " . $e->getMessage();
            $reporte_data = [];
        }

        require_once __DIR__ . '/../views/reportes/profesores.php';
    }
}
?>
