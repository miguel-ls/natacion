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

        // Cargar todos los cursos
        $stmt_cursos = $db->prepare("CALL sp_get_all_cursos(?)");
        $stmt_cursos->execute(['']);
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_cursos->closeCursor();

        // Cargar todos los profesores
        $stmt_profesores = $db->prepare("CALL sp_get_all_profesores(?)");
        $stmt_profesores->execute(['']);
        $profesores = $stmt_profesores->fetchAll(PDO::FETCH_ASSOC);
        $stmt_profesores->closeCursor();

        require_once __DIR__ . '/../views/matriculas_multiples/create.php';
    }


    /**
     * Manejador AJAX para obtener carriles libres según filtros.
     */
    public function getAvailableAreas() {
        header('Content-Type: application/json');

        $id_tipo_area = $_GET['id_tipo_area'] ?? 0;
        $fecha_inicio = $_GET['filtro_fecha_inicio'] ?? null;
        $fecha_fin = $_GET['filtro_fecha_fin'] ?? null;
        $hora_inicio = $_GET['filtro_hora_inicio'] ?? null;
        $hora_fin = $_GET['filtro_hora_fin'] ?? null;

        // Validaciones básicas
        if (!$fecha_inicio || !$fecha_fin || !$hora_inicio || !$hora_fin) {
            echo json_encode(['error' => 'Por favor, complete todos los filtros de fecha y hora.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_find_free_lanes(?, ?, ?, ?, ?)");
            $stmt->execute([
                $id_tipo_area,
                $fecha_inicio,
                $fecha_fin,
                $hora_inicio,
                $hora_fin
            ]);
            $lanes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($lanes);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['error' => 'Error al consultar la base de datos.']);
        }
        exit;
    }

    /**
     * Almacena la nueva matrícula múltiple creando horarios dinámicamente.
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
            // 1. Validar y obtener datos del POST
            $id_alumno = $_POST['id_alumno'];
            $id_curso = $_POST['id_curso'];
            $id_profesor = $_POST['id_profesor'];
            $fecha_inicio = $_POST['filtro_fecha_inicio'];
            $fecha_fin = $_POST['filtro_fecha_fin'];
            $hora_inicio = $_POST['filtro_hora_inicio'];
            $hora_fin = $_POST['filtro_hora_fin'];
            $selected_lanes_json = $_POST['selected_schedules'] ?? '[]';
            $selected_lanes = json_decode($selected_lanes_json);

            // Crear nuevo alumno si es necesario
            if (empty($id_alumno) && !empty($_POST['nuevo_alumno_nombres'])) {
                $stmt_nuevo_alumno = $db->prepare("CALL sp_create_alumno_simple(?, ?, ?, ?, ?, ?)");
                $stmt_nuevo_alumno->execute([
                    $_POST['nuevo_alumno_nombres'], $_POST['nuevo_alumno_apellidos'],
                    $_POST['nuevo_alumno_id_tipo_documento'], $_POST['nuevo_alumno_documento'],
                    $_POST['nuevo_alumno_telefono'], $_POST['nuevo_alumno_email']
                ]);
                $id_alumno = $stmt_nuevo_alumno->fetch(PDO::FETCH_ASSOC)['nuevo_alumno_id'];
                $stmt_nuevo_alumno->closeCursor();
            }

            if (empty($id_alumno) || empty($id_curso) || empty($id_profesor) || empty($selected_lanes)) {
                throw new Exception("Faltan datos requeridos (cliente, curso, profesor o carriles).");
            }

            // 2. Crear el grupo de matrícula
            $stmt_grupo = $db->prepare("INSERT INTO matricula_grupos (id_alumno) VALUES (?)");
            $stmt_grupo->execute([$id_alumno]);
            $id_grupo_matricula = $db->lastInsertId();

            // 3. Calcular días de la semana para el horario (ej. '1,2,3,4,5,6,7' para todos los días en el rango)
            $dias_semana = $this->getDaysOfWeekInRange($fecha_inicio, $fecha_fin);
            $id_tipo_horario_default = 100; // ID del tipo de horario para reservas

            // 4. Iterar sobre cada carril seleccionado y crear horario + matrícula
            foreach ($selected_lanes as $id_carril) {
                // a. Crear un nuevo horario dinámico
                $stmt_horario = $db->prepare(
                    "INSERT INTO horarios (id_curso, id_profesor, id_carril, id_tipo_horario, fecha_inicio, fecha_fin, hora_inicio, hora_fin)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt_horario->execute([
                    $id_curso, $id_profesor, $id_carril, $id_tipo_horario_default,
                    $fecha_inicio, $fecha_fin, $hora_inicio, $hora_fin
                ]);
                $id_horario_nuevo = $db->lastInsertId();

                // b. Crear la matrícula para este nuevo horario
                // (Asumimos precio 0 por ahora, esto podría ser más complejo)
                $stmt_matricula = $db->prepare("CALL sp_create_matricula(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_matricula->execute([
                    $id_alumno, $id_horario_nuevo, $_SESSION['user_id'],
                    $fecha_inicio, $fecha_fin, 0, 0, 1, // precio_base, descuento, forma_pago
                    'Reserva de Matrícula Múltiple', $id_grupo_matricula
                ]);
                $id_matricula_nueva = $stmt_matricula->fetch(PDO::FETCH_ASSOC)['nueva_matricula_id'];
                $stmt_matricula->closeCursor();

                // c. Generar los días de clase
                $stmt_dias = $db->prepare("CALL sp_generate_dias_clase(?, ?, ?, ?)");
                $stmt_dias->execute([$id_matricula_nueva, $fecha_inicio, $fecha_fin, $dias_semana]);
                $stmt_dias->closeCursor();
            }

            $db->commit();
            header('Location: index.php?url=matricula_multiple');
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error_message'] = "Error al crear la matrícula múltiple: " . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?url=matricula_multiple/create');
            exit;
        }
    }

    /**
     * Helper para obtener los días de la semana en un rango de fechas.
     */
    private function getDaysOfWeekInRange($start_date, $end_date) {
        $days = [];
        $current = strtotime($start_date);
        $end = strtotime($end_date);
        while ($current <= $end) {
            $day_of_week = date('N', $current); // 1 (para Lunes) hasta 7 (para Domingo)
            // MySQL DAYOFWEEK es 1=Domingo, 2=Lunes... así que ajustamos
            $mysql_day_of_week = $day_of_week % 7 + 1;
            if (!in_array($mysql_day_of_week, $days)) {
                $days[] = $mysql_day_of_week;
            }
            $current = strtotime('+1 day', $current);
        }
        sort($days);
        return implode(',', $days);
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
