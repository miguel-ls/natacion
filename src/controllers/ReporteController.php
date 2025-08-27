<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class ReporteController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        // $this->auth->checkAuth(); // Movido a cada método para la prueba
    }

    /**
     * Muestra el formulario de filtros y los resultados del reporte de ventas.
     */
    public function ventas() {
        $this->auth->checkAuth(); // Comprobación de autenticación
        $db = Database::getInstance()->getConnection();

        // Valores por defecto y captura de filtros del GET
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $id_alumno = (int)($_GET['id_alumno'] ?? 0);
        $id_curso = (int)($_GET['id_curso'] ?? 0);
        $id_forma_pago = (int)($_GET['id_forma_pago'] ?? 0);

        // Cargar datos para los menús desplegables de los filtros
        $stmt_alumnos = $db->prepare("CALL sp_get_all_alumnos(?)");
        $stmt_alumnos->execute(['']);
        $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_alumnos->closeCursor();

        $stmt_cursos = $db->prepare("CALL sp_get_all_cursos(?)");
        $stmt_cursos->execute(['']);
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_cursos->closeCursor();

        $stmt_formas_pago = $db->prepare("CALL sp_get_all_formas_pago(?)");
        $stmt_formas_pago->execute([$id_forma_pago]);

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
        $this->auth->checkAuth(); // Comprobación de autenticación
        $db = Database::getInstance()->getConnection();

        // Valores por defecto y captura de filtros del GET
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $id_profesor = (int)($_GET['id_profesor'] ?? 0);

        // Cargar profesores para el filtro
        try {
            $stmt_profesores = $db->prepare("CALL sp_get_all_profesores(?)");
            $stmt_profesores->execute(['']);
            $profesores = $stmt_profesores->fetchAll(PDO::FETCH_ASSOC);
            $stmt_profesores->closeCursor();
        } catch (PDOException $e) {
            $profesores = [];
            $_SESSION['error_message'] = "Error al cargar la lista de profesores: " . $e->getMessage();
        }

        // Obtener los datos del reporte con los filtros aplicados
        try {
            $stmt_reporte = $db->prepare("CALL sp_reporte_horas_profesor(?, ?, ?)");
            $stmt_reporte->execute([$fecha_inicio, $fecha_fin, $id_profesor]);
            $reporte_data = $stmt_reporte->fetchAll(PDO::FETCH_ASSOC);
            $stmt_reporte->closeCursor();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al generar el reporte: " . $e->getMessage();
            $reporte_data = [];
        }

        require_once __DIR__ . '/../views/reportes/profesores.php';
    }

    /**
     * Exporta el reporte de ventas a un archivo CSV.
     */
    public function exportarVentas() {
        $this->auth->checkAuth(); // Se añade la autenticación aquí
        $db = Database::getInstance()->getConnection();

        // Captura de filtros del GET
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $id_alumno = (int)($_GET['id_alumno'] ?? 0);
        $id_curso = (int)($_GET['id_curso'] ?? 0);
        $id_forma_pago = (int)($_GET['id_forma_pago'] ?? 0);

        // Obtener los datos del reporte
        try {
            $stmt_reporte = $db->prepare("CALL sp_reporte_ventas(?, ?, ?, ?, ?)");
            $stmt_reporte->execute([$fecha_inicio, $fecha_fin, $id_alumno, $id_curso, $id_forma_pago]);
            $reporte_data = $stmt_reporte->fetchAll(PDO::FETCH_ASSOC);
            $stmt_reporte->closeCursor();
        } catch (PDOException $e) {
            // Manejar el error, quizás redirigir con un mensaje
            $_SESSION['error_message'] = "Error al exportar el reporte: " . $e->getMessage();
            header('Location: index.php?url=reportes/ventas');
            exit;
        }

        // Generar el archivo CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Escribir la cabecera
        fputcsv($output, ['Fecha Matrícula', 'Alumno', 'Curso', 'Fecha Inicio', 'Fecha Fin', 'Precio Original', 'Descuento', 'Precio Final']);

        // Escribir los datos
        $total_base = 0;
        $total_descuentos = 0;
        $total_ventas = 0;
        foreach ($reporte_data as $row) {
            fputcsv($output, [
                date('d/m/Y', strtotime($row['fecha_matricula'])),
                $row['alumno_nombre'],
                $row['curso_nombre'],
                date('d/m/Y', strtotime($row['fecha_inicio'])),
                date('d/m/Y', strtotime($row['fecha_fin'])),
                number_format($row['precio_base'], 2),
                number_format($row['descuento'], 2),
                number_format($row['precio_final'], 2)
            ]);
            $total_base += $row['precio_base'];
            $total_descuentos += $row['descuento'];
            $total_ventas += $row['precio_final'];
        }

        // Escribir la fila de totales
        fputcsv($output, []); // Línea en blanco
        fputcsv($output, ['TOTALES', '', '', '', '', number_format($total_base, 2), number_format($total_descuentos, 2), number_format($total_ventas, 2)]);

        fclose($output);
        exit;
    }

    /**
     * Muestra el reporte de ventas agrupado por forma de pago.
     */
    public function ventasPorFormaPago() {
        $this->auth->checkAuth();
        $db = Database::getInstance()->getConnection();

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        try {
            $stmt = $db->prepare("CALL sp_reporte_ventas_por_forma_pago(?, ?)");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            $reporte_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al generar el reporte: " . $e->getMessage();
            $reporte_data = [];
        }

        require_once __DIR__ . '/../views/reportes/ventas_por_forma_pago.php';
    }

    /**
     * Muestra el reporte de ventas agrupado por curso.
     */
    public function ventasPorCurso() {
        $this->auth->checkAuth();
        $db = Database::getInstance()->getConnection();

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        try {
            $stmt = $db->prepare("CALL sp_reporte_ventas_por_curso(?, ?)");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            $reporte_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al generar el reporte: " . $e->getMessage();
            $reporte_data = [];
        }

        require_once __DIR__ . '/../views/reportes/ventas_por_curso.php';
    }

    /**
     * Muestra el reporte de ventas agrupado por profesor.
     */
    public function ventasPorProfesor() {
        $this->auth->checkAuth();
        $db = Database::getInstance()->getConnection();

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        try {
            $stmt = $db->prepare("CALL sp_reporte_ventas_por_profesor(?, ?)");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            $reporte_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al generar el reporte: " . $e->getMessage();
            $reporte_data = [];
        }

        require_once __DIR__ . '/../views/reportes/ventas_por_profesor.php';
    }

    /**
     * Muestra el reporte de ventas agrupado por piscina y carril.
     */
    public function ventasPorPiscinaCarril() {
        $this->auth->checkAuth();
        $db = Database::getInstance()->getConnection();

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        try {
            $stmt = $db->prepare("CALL sp_reporte_ventas_por_piscina_carril(?, ?)");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            $reporte_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al generar el reporte: " . $e->getMessage();
            $reporte_data = [];
        }

        require_once __DIR__ . '/../views/reportes/ventas_por_piscina_carril.php';
    }
}
?>
