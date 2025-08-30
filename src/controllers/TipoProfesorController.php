<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class TipoProfesorController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_tipos_profesor()");
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../views/tipos_profesor/index.php';
        } catch (PDOException $e) {
            echo "Error al cargar los tipos de profesor: " . $e->getMessage();
        }
    }

    public function create() {
        require_once __DIR__ . '/../views/tipos_profesor/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_tipo_profesor(?)");
                $stmt->execute([$_POST['descripcion']]);
                header('Location: index.php?url=tipos_profesor');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear el tipo de profesor: " . $e->getMessage();
                header('Location: index.php?url=tipos_profesor/create');
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=tipos_profesor');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_tipo_profesor_by_id(?)");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                require_once __DIR__ . '/../views/tipos_profesor/edit.php';
            } else {
                http_response_code(404);
                echo "Tipo de profesor no encontrado.";
            }
        } catch (PDOException $e) {
            echo "Error al buscar el tipo de profesor: " . $e->getMessage();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id'] ?? null;
            if (!$id) {
                header('Location: index.php?url=tipos_profesor');
                exit;
            }

            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_tipo_profesor(?, ?)");
                $stmt->execute([$id, $_POST['descripcion']]);
                header('Location: index.php?url=tipos_profesor');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar el tipo de profesor: " . $e->getMessage();
                header('Location: index.php?url=tipos_profesor/edit&id=' . $id);
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_tipo_profesor(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar el tipo de profesor. Es posible que esté en uso.";
            }
        }
        header('Location: index.php?url=tipos_profesor');
        exit;
    }
}
?>
