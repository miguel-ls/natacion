<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class PiscinaController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_all_piscinas()");
        $stmt->execute();
        $piscinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/piscinas/index.php';
    }

    public function create() {
        $db = Database::getInstance()->getConnection();
        // Fetch pool types for the dropdown
        $stmt = $db->prepare("CALL sp_get_all_tipos_piscina()");
        $stmt->execute();
        $tipos_piscina = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/piscinas/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("CALL sp_create_piscina(?, ?)");
            $stmt->execute([
                $_POST['nombre'],
                $_POST['id_tipo_piscina']
            ]);
            header('Location: index.php?url=piscinas');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        $db = Database::getInstance()->getConnection();

        // Fetch the pool to edit
        $stmt_piscina = $db->prepare("CALL sp_get_piscina_by_id(?)");
        $stmt_piscina->execute([$id]);
        $piscina = $stmt_piscina->fetch(PDO::FETCH_ASSOC);
        $stmt_piscina->closeCursor();

        if ($piscina) {
            // Fetch all pool types for the dropdown
            $stmt_tipos = $db->prepare("CALL sp_get_all_tipos_piscina()");
            $stmt_tipos->execute();
            $tipos_piscina = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../views/piscinas/edit.php';
        } else {
            http_response_code(404);
            echo "Piscina no encontrada.";
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_piscina'];
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("CALL sp_update_piscina(?, ?, ?)");
            $stmt->execute([
                $id,
                $_POST['nombre'],
                $_POST['id_tipo_piscina']
            ]);
            header('Location: index.php?url=piscinas');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_piscina(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar la piscina si tiene carriles asociados. " . $e->getMessage();
            }
        }
        header('Location: index.php?url=piscinas');
        exit;
    }
}
?>
