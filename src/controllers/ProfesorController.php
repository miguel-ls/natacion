<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class ProfesorController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    /**
     * Muestra la lista de profesores.
     */
    public function index() {
        $search_term = $_GET['search'] ?? '';
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_profesores(?)");
            $stmt->execute([$search_term]);
            $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../views/profesores/index.php';
        } catch (PDOException $e) {
            echo "Error al cargar los profesores: " . $e->getMessage();
        }
    }

    /**
     * Muestra el formulario para crear un nuevo profesor.
     */
    public function create() {
        require_once __DIR__ . '/../views/profesores/create.php';
    }

    /**
     * Almacena un nuevo profesor.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_profesor(?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['documento_identidad'],
                    $_POST['telefono'],
                    $_POST['email'],
                    $_POST['direccion'],
                    $_POST['fecha_contratacion']
                ]);
                header('Location: index.php?url=profesores');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear el profesor: " . $e->getMessage();
                header('Location: index.php?url=profesores/create');
                exit;
            }
        }
    }

    /**
     * Muestra el formulario para editar un profesor.
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=profesores');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_profesor_by_id(?)");
            $stmt->execute([$id]);
            $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($profesor) {
                require_once __DIR__ . '/../views/profesores/edit.php';
            } else {
                http_response_code(404);
                echo "Profesor no encontrado.";
            }
        } catch (PDOException $e) {
            echo "Error al buscar el profesor: " . $e->getMessage();
        }
    }

    /**
     * Actualiza un profesor.
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_profesor'] ?? null;
            if (!$id) {
                header('Location: index.php?url=profesores');
                exit;
            }

            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_profesor(?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $id,
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['documento_identidad'],
                    $_POST['telefono'],
                    $_POST['email'],
                    $_POST['direccion'],
                    $_POST['fecha_contratacion'],
                    $_POST['estado']
                ]);
                header('Location: index.php?url=profesores');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar el profesor: " . $e->getMessage();
                header('Location: index.php?url=profesores/edit&id=' . $id);
                exit;
            }
        }
    }

    /**
     * "Elimina" un profesor (lo marca como inactivo).
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_profesor(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al eliminar el profesor: " . $e->getMessage();
            }
        }
        header('Location: index.php?url=profesores');
        exit;
    }
}
?>
