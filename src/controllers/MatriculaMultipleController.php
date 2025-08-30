<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class MatriculaMultipleController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    /**
     * Muestra la lista de grupos de matrículas múltiples.
     */
    public function index() {
        $db = Database::getInstance()->getConnection();

        // Filtros
        $filters = [
            'id_alumno' => $_GET['id_alumno'] ?? 0,
            'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
            'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null,
            'alumno_nombre' => ''
        ];

        // Cargar nombre del alumno si se ha filtrado por uno
        if ($filters['id_alumno'] != 0) {
            $stmt_alumno = $db->prepare("CALL sp_get_alumno_by_id(?)");
            $stmt_alumno->execute([$filters['id_alumno']]);
            $alumno_data = $stmt_alumno->fetch(PDO::FETCH_ASSOC);
            if ($alumno_data) {
                $filters['alumno_nombre'] = $alumno_data['nombres'] . ' ' . $alumno_data['apellidos'];
            }
            $stmt_alumno->closeCursor();
        }

        // Cargar los grupos de matrícula con filtros
        $stmt = $db->prepare("CALL sp_get_all_matricula_grupos(?, ?, ?)");
        $stmt->execute([
            $filters['id_alumno'],
            $filters['fecha_desde'],
            $filters['fecha_hasta']
        ]);
        $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        require_once __DIR__ . '/../views/matriculas_multiples/index.php';
    }

    /**
     * Muestra el formulario para crear una nueva matrícula múltiple.
     */
    public function create() {
        $db = Database::getInstance()->getConnection();

        // Cargar tipos de área para el filtro
        $stmt_tipos_area = $db->prepare("CALL sp_get_all_tipos_piscina(?)");
        $stmt_tipos_area->execute(['']);
        $tipos_area = $stmt_tipos_area->fetchAll(PDO::FETCH_ASSOC);
        $stmt_tipos_area->closeCursor();

        // Cargar tipos de documento para el formulario de nuevo alumno
        $stmt_tipos_documento = $db->prepare("CALL sp_get_all_tipos_documento()");
        $stmt_tipos_documento->execute();
        $tipos_documento = $stmt_tipos_documento->fetchAll(PDO::FETCH_ASSOC);
        $stmt_tipos_documento->closeCursor();

        require_once __DIR__ . '/../views/matriculas_multiples/create.php';
    }


    /**
     * Manejador AJAX para obtener áreas/sub-áreas disponibles según filtros.
     */
    public function getAvailableAreas() {
        header('Content-Type: application/json');

        $id_tipo_area = $_GET['id_tipo_area'] ?? 0;
        $fecha_inicio = $_GET['fecha_inicio'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? null;
        $hora_inicio = $_GET['hora_inicio'] ?? null;
        $hora_fin = $_GET['hora_fin'] ?? null;

        // Validaciones básicas
        if (!$fecha_inicio || !$fecha_fin || !$hora_inicio || !$hora_fin) {
            echo json_encode(['error' => 'Por favor, complete todos los filtros de fecha y hora.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_available_areas_multiple(?, ?, ?, ?, ?)");
            $stmt->execute([
                $id_tipo_area,
                $fecha_inicio,
                $fecha_fin,
                $hora_inicio,
                $hora_fin
            ]);
            $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($areas);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['error' => 'Error al consultar la base de datos.']);
        }
        exit;
    }

    /**
     * Almacena la nueva matrícula múltiple.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=matricula_multiple');
            exit;
        }

        $this->auth->verifyCsrfToken();
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            $id_alumno = $_POST['id_alumno'];

            // Si no hay ID de alumno pero sí datos de nuevo alumno, crearlo primero
            if (empty($id_alumno) && !empty($_POST['nuevo_alumno_nombres'])) {
                // (Aquí iría la validación de DNI duplicado, omitida por brevedad pero importante en producción)
                $stmt_nuevo_alumno = $db->prepare("CALL sp_create_alumno_simple(?, ?, ?, ?, ?, ?)");
                $stmt_nuevo_alumno->execute([
                    $_POST['nuevo_alumno_nombres'],
                    $_POST['nuevo_alumno_apellidos'],
                    $_POST['nuevo_alumno_id_tipo_documento'],
                    $_POST['nuevo_alumno_documento'],
                    $_POST['nuevo_alumno_telefono'],
                    $_POST['nuevo_alumno_email']
                ]);
                $result_alumno = $stmt_nuevo_alumno->fetch(PDO::FETCH_ASSOC);
                $id_alumno = $result_alumno['nuevo_alumno_id'];
                $stmt_nuevo_alumno->closeCursor();
            }

            if (empty($id_alumno)) {
                throw new Exception("No se ha seleccionado ni creado un cliente.");
            }

            $selected_schedules_json = $_POST['selected_schedules'] ?? '[]';
            $selected_schedules = json_decode($selected_schedules_json);

            if (empty($selected_schedules) || !is_array($selected_schedules)) {
                 throw new Exception("No se ha seleccionado ningún horario.");
            }

            // 1. Crear el grupo de matrícula
            $stmt_grupo = $db->prepare("INSERT INTO matricula_grupos (id_alumno) VALUES (?)");
            $stmt_grupo->execute([$id_alumno]);
            $id_grupo_matricula = $db->lastInsertId();

            // 2. Llamar al stored procedure que crea las matrículas individuales
            $stmt = $db->prepare("CALL sp_create_matricula_multiple(?, ?, ?, ?)");
            $stmt->execute([
                $id_alumno,
                $_SESSION['user_id'],
                $selected_schedules_json,
                $id_grupo_matricula
            ]);

            $db->commit();
            // Redirigir a la lista de grupos de matrícula
            header('Location: index.php?url=matricula_multiple');
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            // Guardar el error y los datos del formulario en la sesión para repoblar
            $_SESSION['error_message'] = "Error al crear la matrícula múltiple: " . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?url=matricula_multiple/create');
            exit;
        }
    }

    /**
     * Muestra los detalles de un grupo de matrícula.
     */
    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=matricula_multiple');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_matricula_grupo_details(?)");
        $stmt->execute([$id]);

        $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->nextRowset(); // Moverse al siguiente conjunto de resultados
        $matriculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->closeCursor();

        require_once __DIR__ . '/../views/matriculas_multiples/show.php';
    }
}
?>
