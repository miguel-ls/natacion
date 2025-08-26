<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class TipoPiscinaController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_tipos_piscina()");
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../views/tipos_piscina/index.php';
        } catch (PDOException $e) {
            echo "Error al cargar los tipos de piscina: " . $e->getMessage();
        }
    }

    public function create() {
        require_once __DIR__ . '/../views/tipos_piscina/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_tipo_piscina(?, ?)");
                $stmt->execute([$_POST['nombre'], $_POST['descripcion']]);
                header('Location: index.php?url=tipos_piscina');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear el tipo de piscina: " . $e->getMessage();
                header('Location: index.php?url=tipos_piscina/create');
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=tipos_piscina');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_tipo_piscina_by_id(?)");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item) {
                require_once __DIR__ . '/../views/tipos_piscina/edit.php';
            } else {
                http_response_code(404);
                echo "Tipo de piscina no encontrado.";
            }
        } catch (PDOException $e) {
            echo "Error al buscar el tipo de piscina: " . $e->getMessage();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_tipo_piscina'] ?? null;
            if (!$id) {
                header('Location: index.php?url=tipos_piscina');
                exit;
            }

            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_tipo_piscina(?, ?, ?)");
                $stmt->execute([$id, $_POST['nombre'], $_POST['descripcion']]);
                header('Location: index.php?url=tipos_piscina');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar: " . $e->getMessage();
                header('Location: index.php?url=tipos_piscina/edit&id=' . $id);
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_tipo_piscina(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar si está en uso. " . $e->getMessage();
            }
        }
        header('Location: index.php?url=tipos_piscina');
        exit;
    }
}
?>
