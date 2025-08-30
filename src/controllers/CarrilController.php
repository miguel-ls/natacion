<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class CarrilController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $search_term = $_GET['search'] ?? '';
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_all_carriles(?)");
        $stmt->execute([$search_term]);
        $carriles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/carriles/index.php';
    }

    public function create() {
        $db = Database::getInstance()->getConnection();
        // Fetch pools for the dropdown
        $stmt = $db->prepare("CALL sp_get_all_piscinas(?)");
        $stmt->execute(['']);
        $piscinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/carriles/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("CALL sp_create_carril(?, ?, ?, ?)");
            $stmt->execute([
                $_POST['id_piscina'],
                $_POST['descripcion'],
                $_POST['numero_carril'],
                $_POST['capacidad_maxima']
            ]);
            header('Location: index.php?url=carriles');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        $db = Database::getInstance()->getConnection();

        // Fetch the lane to edit
        $stmt_carril = $db->prepare("CALL sp_get_carril_by_id(?)");
        $stmt_carril->execute([$id]);
        $carril = $stmt_carril->fetch(PDO::FETCH_ASSOC);
        $stmt_carril->closeCursor();

        if ($carril) {
            // Fetch all pools for the dropdown
            $stmt_piscinas = $db->prepare("CALL sp_get_all_piscinas(?)");
            $stmt_piscinas->execute(['']);
            $piscinas = $stmt_piscinas->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../views/carriles/edit.php';
        } else {
            http_response_code(404);
            echo "Carril no encontrado.";
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_carril'];
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("CALL sp_update_carril(?, ?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $_POST['id_piscina'],
                $_POST['descripcion'],
                $_POST['numero_carril'],
                $_POST['capacidad_maxima']
            ]);
            header('Location: index.php?url=carriles');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_carril(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar el carril si está asignado a horarios. " . $e->getMessage();
            }
        }
        header('Location: index.php?url=carriles');
        exit;
    }
}
?>
