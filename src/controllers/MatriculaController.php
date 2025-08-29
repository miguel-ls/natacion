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
     * Muestra la lista de todas las matrículas con filtros.
     */
    public function index() {
        $db = Database::getInstance()->getConnection();

        // Valores por defecto para los filtros
        $filters = [
            'id_alumno' => $_GET['id_alumno'] ?? 0,
            'id_curso' => $_GET['id_curso'] ?? 0,
            'fecha_inicio_desde' => $_GET['fecha_inicio_desde'] ?? null,
            'fecha_inicio_hasta' => $_GET['fecha_inicio_hasta'] ?? null,
            'estado' => $_GET['estado'] ?? 'Todos',
            'alumno_nombre' => '', // Nuevo
            'curso_nombre' => ''  // Nuevo
        ];

        try {
            // Cargar nombres para filtros si los IDs están presentes
            if ($filters['id_alumno'] != 0) {
                $stmt_alumno = $db->prepare("CALL sp_get_alumno_by_id(?)");
                $stmt_alumno->execute([$filters['id_alumno']]);
                $alumno_data = $stmt_alumno->fetch(PDO::FETCH_ASSOC);
                if ($alumno_data) {
                    $filters['alumno_nombre'] = $alumno_data['nombres'] . ' ' . $alumno_data['apellidos'];
                }
                $stmt_alumno->closeCursor();
            }

            if ($filters['id_curso'] != 0) {
                $stmt_curso = $db->prepare("CALL sp_get_curso_by_id(?)");
                $stmt_curso->execute([$filters['id_curso']]);
                $curso_data = $stmt_curso->fetch(PDO::FETCH_ASSOC);
                if ($curso_data) {
                    $filters['curso_nombre'] = $curso_data['nombre'];
                }
                $stmt_curso->closeCursor();
            }

            // Cargar datos para los filtros
            $stmt_alumnos = $db->prepare("CALL sp_get_all_alumnos(?)");
            $stmt_alumnos->execute(['']);
            $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);
            $stmt_alumnos->closeCursor();

            $stmt_cursos = $db->prepare("CALL sp_get_all_cursos(?)");
            $stmt_cursos->execute(['']);
            $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
            $stmt_cursos->closeCursor();

            // Cargar matrículas filtradas
            $stmt = $db->prepare("CALL sp_get_matriculas_filtradas(?, ?, ?, ?, ?)");
            $stmt->execute([
                $filters['id_alumno'],
                $filters['id_curso'],
                $filters['fecha_inicio_desde'],
                $filters['fecha_inicio_hasta'],
                $filters['estado']
            ]);
            $matriculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $csrf_token = $this->auth->getCsrfToken();
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

            // Obtener otros horarios disponibles para ese curso, excluyendo el actual
            $sql = "
                SELECT
                    h.id_horario,
                    c.nombre AS curso_nombre,
                    h.fecha_inicio,
                    h.fecha_fin,
                    CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
                    CONCAT(pi.nombre, ' - Carril ', ca.numero_carril) as carril_nombre,
                    th.nombre as tipo_horario_nombre,
                    h.hora_inicio,
                    h.hora_fin,
                    (ca.capacidad_maxima - (
                        SELECT COUNT(*) FROM matriculas m WHERE m.id_horario = h.id_horario AND m.estado IN ('activa', 'vigente')
                    )) AS vacantes_disponibles
                FROM horarios h
                JOIN cursos c ON h.id_curso = c.id_curso
                JOIN profesores p ON h.id_profesor = p.id_profesor
                JOIN carriles ca ON h.id_carril = ca.id_carril
                JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
                JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
                WHERE h.id_curso = :id_curso AND h.id_horario != :id_horario_a_excluir
                HAVING vacantes_disponibles > 0
            ";
            $stmt_horarios = $db->prepare($sql);
            $stmt_horarios->execute([':id_curso' => $curso_id, ':id_horario_a_excluir' => $matricula['id_horario']]);
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
        $id_profesor = (int)($_GET['id_profesor'] ?? 0); // Casting a entero para robustez
        $hora_inicio = !empty($_GET['hora_inicio']) ? $_GET['hora_inicio'] : null;
        $hora_fin = !empty($_GET['hora_fin']) ? $_GET['hora_fin'] : null;

        if (!$id_curso) {
            echo json_encode([]);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $sql = "
                SELECT
                    h.id_horario,
                    c.nombre AS curso_nombre,
                    COALESCE(
                        (SELECT pc.precio FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND h.fecha_inicio >= pc.fecha_inicio AND h.fecha_fin <= pc.fecha_fin LIMIT 1),
                        0.00
                    ) AS precio_actual,
                    h.fecha_inicio,
                    h.fecha_fin,
                    CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
                    CONCAT(pi.nombre, ' - Carril ', ca.numero_carril) as carril_nombre,
                    th.nombre as tipo_horario_nombre,
                    th.dias_semana,
                    h.hora_inicio,
                    h.hora_fin,
                    (ca.capacidad_maxima - (
                        SELECT COUNT(*) FROM matriculas m WHERE m.id_horario = h.id_horario AND m.estado IN ('activa', 'vigente')
                    )) AS vacantes_disponibles
                FROM horarios h
                JOIN cursos c ON h.id_curso = c.id_curso
                JOIN profesores p ON h.id_profesor = p.id_profesor
                JOIN carriles ca ON h.id_carril = ca.id_carril
                JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
                JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
                WHERE h.id_curso = :id_curso
            ";

            $params = [':id_curso' => $id_curso];

            if ($id_profesor > 0) {
                $sql .= " AND h.id_profesor = :id_profesor";
                $params[':id_profesor'] = $id_profesor;
            }
            if ($hora_inicio) {
                $sql .= " AND h.hora_inicio >= :hora_inicio";
                $params[':hora_inicio'] = $hora_inicio;
            }
            if ($hora_fin) {
                $sql .= " AND h.hora_fin <= :hora_fin";
                $params[':hora_fin'] = $hora_fin;
            }

            $sql .= " HAVING vacantes_disponibles > 0";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($horarios);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode([]);
        }
        exit;
    }

    private function validateDni($dni) {
        if (empty($dni)) return true; // Permite DNI vacío
        return preg_match('/^[0-9]{8}$/', $dni);
    }

    /**
     * Almacena la nueva matrícula y genera los días de clase.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();

            // Validar DNI de nuevo alumno si se proporciona
            if (empty($_POST['id_alumno']) && !empty($_POST['nuevo_alumno_nombres'])) {
                $dni = $_POST['nuevo_alumno_documento'];

                if (!$this->validateDni($dni)) {
                    $_SESSION['error_message'] = "El Documento de Identidad del nuevo alumno debe tener 8 dígitos numéricos.";
                    $_SESSION['form_data'] = $_POST;
                    header('Location: index.php?url=matriculas/create');
                    exit;
                }

                // Verificar duplicado
                $stmt_check_dni = $db->prepare("CALL sp_check_alumno_by_dni(?, NULL)");
                $stmt_check_dni->execute([$dni]);
                $result = $stmt_check_dni->fetch(PDO::FETCH_ASSOC);
                $stmt_check_dni->closeCursor();

                if ($result['count'] > 0) {
                    $_SESSION['error_message'] = "El Documento de Identidad del nuevo alumno ya está registrado.";
                    $_SESSION['form_data'] = $_POST;
                    header('Location: index.php?url=matriculas/create');
                    exit;
                }
            }

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

                // 1. Crear la matrícula (Lógica corregida)
                $stmt_matricula = $db->prepare("CALL sp_create_matricula(?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_matricula->execute([
                    $id_alumno,
                    $_POST['id_horario'],
                    $_SESSION['user_id'],
                    $_POST['fecha_inicio'],
                    $_POST['fecha_fin'],
                    $_POST['precio_base'], // Se envía el precio base
                    $_POST['descuento'],
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

    /**
     * Elimina permanentemente una matrícula.
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id_matricula = $_POST['id_matricula'] ?? null;

            if ($id_matricula) {
                $db = Database::getInstance()->getConnection();
                try {
                    $stmt = $db->prepare("CALL sp_delete_matricula(?)");
                    $stmt->execute([$id_matricula]);
                } catch (PDOException $e) {
                    // Consider logging the error message for debugging
                    // error_log($e->getMessage());
                    $_SESSION['error_message'] = "Error al eliminar la matrícula. Es posible que tenga registros dependientes.";
                }
            }
        }
        header('Location: index.php?url=matriculas');
        exit;
    }

    /**
     * Obtiene el precio de un curso basado en la fecha de inicio (AJAX).
     */
    public function getPrecioByFecha() {
        header('Content-Type: application/json');
        $id_curso = $_GET['id_curso'] ?? null;
        $fecha = $_GET['fecha'] ?? null;

        if (!$id_curso || !$fecha) {
            echo json_encode(['precio' => 0.00]);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_precio_by_curso_and_fecha(?, ?)");
            $stmt->execute([$id_curso, $fecha]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {
            // En caso de error, devolver 0.00 y registrar el error
            error_log($e->getMessage());
            echo json_encode(['precio' => 0.00]);
        }
        exit;
    }
}
?>
