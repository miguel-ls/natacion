<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class TipoPrecioController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_all_tipos_precio()");
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/tipos_precio/index.php';
    }

    public function create() {
        require_once __DIR__ . '/../views/tipos_precio/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_tipo_precio(?, ?)");
                $stmt->execute([$_POST['nombre'], $_POST['descripcion']]);
                header('Location: index.php?url=tipos_precio');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear: " . $e->getMessage();
                header('Location: index.php?url=tipos_precio/create');
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: index.php?url=tipos_precio'); exit; }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_tipo_precio_by_id(?)");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            require_once __DIR__ . '/../views/tipos_precio/edit.php';
        } else {
            http_response_code(404);
            echo "Tipo de Precio no encontrado.";
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_tipo_precio'];
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_tipo_precio(?, ?, ?)");
                $stmt->execute([$id, $_POST['nombre'], $_POST['descripcion']]);
                header('Location: index.php?url=tipos_precio');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar: " . $e->getMessage();
                header('Location: index.php?url=tipos_precio/edit&id=' . $id);
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_tipo_precio(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar, puede que esté en uso en una lista de precios.";
            }
        }
        header('Location: index.php?url=tipos_precio');
        exit;
    }
}
?>
