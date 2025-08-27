<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class MatriculaController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    /**
     * Muestra la lista de todas las matrículas.
     */
    public function index() {
        $search_term = $_GET['search'] ?? '';
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_matriculas_details(?)");
            $stmt->execute([$search_term]);
            $matriculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../views/matriculas/index.php';
        } catch (PDOException $e) {
            echo "Error al cargar las matrículas: " . $e->getMessage();
        }
    }

    /**
     * Muestra el formulario para cambiar el horario de una matrícula.
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=matriculas');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            // Obtener detalles de la matrícula actual
            $stmt_details = $db->prepare("CALL sp_get_matricula_details_by_id(?)");
            $stmt_details->execute([$id]);
            $matricula = $stmt_details->fetch(PDO::FETCH_ASSOC);
            $stmt_details->closeCursor();

            if (!$matricula) {
                http_response_code(404);
                echo "Matrícula no encontrada.";
                exit;
            }

            // Obtener el id del curso de la matrícula
            $stmt_curso_id = $db->prepare("SELECT h.id_curso FROM matriculas m JOIN horarios h ON m.id_horario = h.id_horario WHERE m.id_matricula = ?");
            $stmt_curso_id->execute([$id]);
            $curso_id_result = $stmt_curso_id->fetch(PDO::FETCH_ASSOC);
            $curso_id = $curso_id_result['id_curso'];
            $stmt_curso_id->closeCursor();

            // Obtener otros horarios disponibles para ese curso
            $stmt_horarios = $db->prepare("CALL sp_get_horarios_disponibles_por_curso(?, ?, ?, ?)");
            $stmt_horarios->execute([$curso_id, 0, null, null]); // Se pasan valores por defecto para los filtros no usados aquí
            $horarios_disponibles = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);
            $stmt_horarios->closeCursor();

            require_once __DIR__ . '/../views/matriculas/edit.php';

        } catch (PDOException $e) {
            echo "Error al cargar la página de edición: " . $e->getMessage();
        }
    }

    /**
     * Actualiza el horario de una matrícula.
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id_matricula = $_POST['id_matricula'] ?? null;
            $new_id_horario = $_POST['id_horario'] ?? null;
            $fecha_inicio = $_POST['fecha_inicio'] ?? null;
            $fecha_fin = $_POST['fecha_fin'] ?? null;

            if ($id_matricula && $new_id_horario && $fecha_inicio && $fecha_fin) {
                $db = Database::getInstance()->getConnection();
                try {
                    $stmt = $db->prepare("CALL sp_change_horario_matricula(?, ?, ?, ?)");
                    $stmt->execute([$id_matricula, $new_id_horario, $fecha_inicio, $fecha_fin]);
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = "Error al actualizar la matrícula: " . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = "Faltan datos para actualizar la matrícula.";
            }
            header('Location: index.php?url=matriculas/show&id=' . $id_matricula);
            exit;
        }
    }

    /**
     * Muestra el formulario principal para iniciar una nueva matrícula.
     */
    public function create() {
        $db = Database::getInstance()->getConnection();
        $selected_schedule = null;
        $id_horario = $_GET['id_horario'] ?? null;

        if ($id_horario) {
            try {
                $stmt_horario = $db->prepare("CALL sp_get_horario_details_for_enrollment(?)");
                $stmt_horario->execute([$id_horario]);
                $selected_schedule = $stmt_horario->fetch(PDO::FETCH_ASSOC);
                $stmt_horario->closeCursor();
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al cargar los detalles del horario seleccionado: " . $e->getMessage();
            }
        }

        // Cargar datos necesarios para los menús desplegables iniciales
        $stmt_alumnos = $db->prepare("CALL sp_get_all_alumnos(?)");
        $stmt_alumnos->execute(['']); // Pasar string vacío para obtener todos los alumnos
        $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_alumnos->closeCursor();

        $stmt_cursos = $db->prepare("CALL sp_get_all_cursos(?)");
        $stmt_cursos->execute(['']); // Pasar string vacío para obtener todos los cursos
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_cursos->closeCursor();

        $stmt_profesores = $db->prepare("CALL sp_get_all_profesores(?)");
        $stmt_profesores->execute(['']); // Pasar string vacío para obtener todos
        $profesores = $stmt_profesores->fetchAll(PDO::FETCH_ASSOC);
        $stmt_profesores->closeCursor();

        $stmt_formas_pago = $db->prepare("CALL sp_get_all_formas_pago(?)");
        $stmt_formas_pago->execute(['']);
        $formas_pago = $stmt_formas_pago->fetchAll(PDO::FETCH_ASSOC);
        $stmt_formas_pago->closeCursor();

        require_once __DIR__ . '/../views/matriculas/create.php';
    }

    /**
     * Manejador AJAX para obtener horarios disponibles de un curso.
     */
    public function getHorariosByCurso() {
        header('Content-Type: application/json');
        $id_curso = $_GET['id_curso'] ?? 0;
        $id_profesor = $_GET['id_profesor'] ?? 0;
        $hora_inicio = !empty($_GET['hora_inicio']) ? $_GET['hora_inicio'] : null;
        $hora_fin = !empty($_GET['hora_fin']) ? $_GET['hora_fin'] : null;

        if (!$id_curso) {
            echo json_encode([]);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_horarios_disponibles_por_curso(?, ?, ?, ?)");
            $stmt->execute([$id_curso, $id_profesor, $hora_inicio, $hora_fin]);
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($horarios);
        } catch (PDOException $e) {
            // En caso de error, devolver un array vacío y quizás registrar el error
            error_log($e->getMessage());
            echo json_encode([]);
        }
        exit;
    }

    /**
     * Almacena la nueva matrícula y genera los días de clase.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            try {
                $id_alumno = $_POST['id_alumno'];

                // Si no hay ID de alumno pero sí datos de nuevo alumno, crearlo primero
                if (empty($id_alumno) && !empty($_POST['nuevo_alumno_nombres'])) {
                    $stmt_nuevo_alumno = $db->prepare("CALL sp_create_alumno_simple(?, ?, ?, ?, ?)");
                    $stmt_nuevo_alumno->execute([
                        $_POST['nuevo_alumno_nombres'],
                        $_POST['nuevo_alumno_apellidos'],
                        $_POST['nuevo_alumno_documento'],
                        $_POST['nuevo_alumno_telefono'],
                        $_POST['nuevo_alumno_email']
                    ]);
                    $result_alumno = $stmt_nuevo_alumno->fetch(PDO::FETCH_ASSOC);
                    $id_alumno = $result_alumno['nuevo_alumno_id'];
                    $stmt_nuevo_alumno->closeCursor();
                }

                if (empty($id_alumno)) {
                    throw new Exception("No se ha seleccionado ni creado un alumno.");
                }

                // 1. Crear la matrícula
                $stmt_matricula = $db->prepare("CALL sp_create_matricula(?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_matricula->execute([
                    $id_alumno,
                    $_POST['id_horario'],
                    $_SESSION['user_id'],
                    $_POST['fecha_inicio'],
                    $_POST['fecha_fin'],
                    $_POST['precio_final'],
                    $_POST['id_forma_pago'],
                    $_POST['observaciones']
                ]);
                $result = $stmt_matricula->fetch(PDO::FETCH_ASSOC);
                $new_matricula_id = $result['nueva_matricula_id'];
                $stmt_matricula->closeCursor();

                // 2. Generar los días de clase
                // (El SP `sp_get_horarios_disponibles_por_curso` ya nos dio los dias_semana)
                $dias_semana = $_POST['dias_semana_hidden']; // Se pasará desde el form

                $stmt_dias = $db->prepare("CALL sp_generate_dias_clase(?, ?, ?, ?)");
                $stmt_dias->execute([
                    $new_matricula_id,
                    $_POST['fecha_inicio'],
                    $_POST['fecha_fin'],
                    $dias_semana
                ]);
                $stmt_dias->closeCursor();

                $db->commit();
                // Redirigir a una página de éxito o al detalle de la matrícula
                header('Location: index.php?url=matriculas');
                exit;

            } catch (Exception $e) {
                $db->rollBack();
                $_SESSION['error_message'] = "Error al crear la matrícula: " . $e->getMessage();
                header('Location: index.php?url=matriculas/create');
                exit;
            }
        }
    }

    /**
     * Muestra los detalles de una matrícula, incluyendo sus días de clase.
     */
    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=matriculas');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            // Obtener detalles principales
            $stmt_details = $db->prepare("CALL sp_get_matricula_details_by_id(?)");
            $stmt_details->execute([$id]);
            $matricula = $stmt_details->fetch(PDO::FETCH_ASSOC);
            $stmt_details->closeCursor();

            if (!$matricula) {
                http_response_code(404);
                echo "Matrícula no encontrada.";
                exit;
            }

            // Obtener días de clase
            $stmt_dias = $db->prepare("CALL sp_get_matricula_dias(?)");
            $stmt_dias->execute([$id]);
            $dias_clase = $stmt_dias->fetchAll(PDO::FETCH_ASSOC);
            $stmt_dias->closeCursor();

            require_once __DIR__ . '/../views/matriculas/show.php';

        } catch (PDOException $e) {
            echo "Error al cargar los detalles de la matrícula: " . $e->getMessage();
        }
    }

    /**
     * Cancela una matrícula.
     */
    public function cancel() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_cancel_matricula(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al anular la matrícula: " . $e->getMessage();
            }
        }
        header('Location: index.php?url=matriculas');
        exit;
    }

    /**
     * Actualiza el estado de un día de clase (AJAX).
     */
    public function updateDiaClase() {
        header('Content-Type: application/json');
        $this->auth->verifyCsrfToken();
        $id_dia = $_POST['id_dia'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $observacion = $_POST['observacion'] ?? '';

        if (!$id_dia || !$estado) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_update_asistencia_alumno(?, ?, ?)");
            $stmt->execute([$id_dia, $estado, $observacion]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Añade una clase de recuperación (AJAX).
     */
    public function addRecuperacion() {
        header('Content-Type: application/json');
        $this->auth->verifyCsrfToken();
        $id_matricula = $_POST['id_matricula'] ?? null;
        $fecha = $_POST['fecha'] ?? null;
        $observacion = $_POST['observacion'] ?? '';

        if (!$id_matricula || !$fecha) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_add_recuperacion_clase(?, ?, ?)");
            $stmt->execute([$id_matricula, $fecha, $observacion]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

}
?>
