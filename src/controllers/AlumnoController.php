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

    /**
     * Almacena un nuevo alumno en la base de datos.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_alumno(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['documento_identidad'],
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
                // Manejar error, quizás con un mensaje en la sesión
                $_SESSION['error_message'] = "Error al crear el alumno: " . $e->getMessage();
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

            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_alumno(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $id,
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['documento_identidad'],
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
}
?>
