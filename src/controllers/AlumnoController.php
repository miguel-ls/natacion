<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class AlumnoController {

    private $auth;

    public function __construct() {
        // Asegurarse de que el usuario esté autenticado para acceder a cualquier método de este controlador
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    /**
     * Muestra la lista de todos los alumnos.
     */
    public function index() {
        $search_term = $_GET['search'] ?? '';
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_alumnos(?)");
            $stmt->execute([$search_term]);
            $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Cargar la vista y pasarle los datos
            require_once __DIR__ . '/../views/alumnos/index.php';
        } catch (PDOException $e) {
            // Manejar el error apropiadamente
            echo "Error al cargar los alumnos: " . $e->getMessage();
        }
    }

    /**
     * Muestra el formulario para crear un nuevo alumno.
     */
    public function create() {
        require_once __DIR__ . '/../views/alumnos/create.php';
    }

    private function validateDni($dni) {
        if (empty($dni)) return true; // Permite DNI vacío, se puede hacer requerido en el frontend/BD
        return preg_match('/^[0-9]{8}$/', $dni);
    }

    /**
     * Almacena un nuevo alumno en la base de datos.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();

            $dni = $_POST['documento_identidad'];
            if (!$this->validateDni($dni)) {
                $_SESSION['error_message'] = "El Documento de Identidad debe tener 8 dígitos numéricos.";
                // Guardar los datos del post en la sesión para repoblar el formulario
                $_SESSION['form_data'] = $_POST;
                header('Location: index.php?url=alumnos/create');
                exit;
            }

            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_alumno(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $dni,
                    $_POST['fecha_nacimiento'],
                    $_POST['grupo_sanguineo'],
                    $_POST['direccion'],
                    $_POST['telefono'],
                    $_POST['email'],
                    $_POST['nombre_padre_tutor'],
                    $_POST['telefono_emergencia']
                ]);
                unset($_SESSION['form_data']); // Limpiar datos del formulario en éxito
                header('Location: index.php?url=alumnos');
                exit;
            } catch (PDOException $e) {
                // Manejar error, quizás con un mensaje en la sesión
                $_SESSION['error_message'] = "Error al crear el alumno: " . $e->getMessage();
                $_SESSION['form_data'] = $_POST;
                header('Location: index.php?url=alumnos/create');
                exit;
            }
        }
    }

    /**
     * Muestra el formulario para editar un alumno.
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=alumnos');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_alumno_by_id(?)");
            $stmt->execute([$id]);
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($alumno) {
                require_once __DIR__ . '/../views/alumnos/edit.php';
            } else {
                http_response_code(404);
                echo "Alumno no encontrado.";
            }
        } catch (PDOException $e) {
            echo "Error al buscar el alumno: " . $e->getMessage();
        }
    }

    /**
     * Actualiza un alumno en la base de datos.
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_alumno'] ?? null;
            if (!$id) {
                header('Location: index.php?url=alumnos');
                exit;
            }

            $dni = $_POST['documento_identidad'];
            if (!$this->validateDni($dni)) {
                $_SESSION['error_message'] = "El Documento de Identidad debe tener 8 dígitos numéricos.";
                header('Location: index.php?url=alumnos/edit&id=' . $id);
                exit;
            }

            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_alumno(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $id,
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $dni,
                    $_POST['fecha_nacimiento'],
                    $_POST['grupo_sanguineo'],
                    $_POST['direccion'],
                    $_POST['telefono'],
                    $_POST['email'],
                    $_POST['nombre_padre_tutor'],
                    $_POST['telefono_emergencia']
                ]);
                header('Location: index.php?url=alumnos');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar el alumno: " . $e->getMessage();
                header('Location: index.php?url=alumnos/edit&id=' . $id);
                exit;
            }
        }
    }

    /**
     * Muestra los detalles de un alumno y sus matrículas.
     */
    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=alumnos');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            // Obtener detalles del alumno
            $stmt_alumno = $db->prepare("CALL sp_get_alumno_by_id(?)");
            $stmt_alumno->execute([$id]);
            $alumno = $stmt_alumno->fetch(PDO::FETCH_ASSOC);
            $stmt_alumno->closeCursor();

            if (!$alumno) {
                http_response_code(404);
                echo "Alumno no encontrado.";
                exit;
            }

            // Obtener matrículas del alumno
            $stmt_matriculas = $db->prepare("CALL sp_get_matriculas_by_alumn_id(?)");
            $stmt_matriculas->execute([$id]);
            $matriculas = $stmt_matriculas->fetchAll(PDO::FETCH_ASSOC);
            $stmt_matriculas->closeCursor();

            require_once __DIR__ . '/../views/alumnos/show.php';

        } catch (PDOException $e) {
            echo "Error al cargar los detalles del alumno: " . $e->getMessage();
        }
    }

    /**
     * Elimina un alumno.
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_alumno(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                // Si hay un error de clave foránea, no se podrá borrar.
                $_SESSION['error_message'] = "No se puede eliminar el alumno porque tiene matrículas asociadas. " . $e->getMessage();
            }
        }
        header('Location: index.php?url=alumnos');
        exit;
    }

    /**
     * Busca alumnos para una llamada AJAX y devuelve JSON.
     */
    public function search() {
        header('Content-Type: application/json');
        $search_term = $_GET['term'] ?? '';

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_alumnos(?)");
            $stmt->execute([$search_term]);
            $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($alumnos);
        } catch (PDOException $e) {
            echo json_encode([]); // Devuelve array vacío en caso de error
        }
        exit;
    }

    /**
     * Verifica si un DNI ya existe (para AJAX).
     */
    public function checkDni() {
        header('Content-Type: application/json');
        $dni = $_GET['dni'] ?? '';
        $id = $_GET['id'] ?? null; // ID del alumno que se está editando

        if (empty($dni)) {
            echo json_encode(['exists' => false]);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_check_alumno_by_dni(?, ?)");
            $stmt->execute([$dni, $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            echo json_encode(['exists' => ($result['count'] > 0)]);
        } catch (PDOException $e) {
            // En caso de error, asumimos que no existe para no bloquear al usuario,
            // pero registramos el error para depuración.
            error_log("Error en checkDni: " . $e->getMessage());
            echo json_encode(['exists' => false, 'error' => 'Error al verificar DNI.']);
        }
        exit;
    }
}
?>
