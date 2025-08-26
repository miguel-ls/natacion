<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class FormaPagoController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_all_formas_pago()");
        $stmt->execute();
        $formas_pago = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/formas_pago/index.php';
    }

    public function create() {
        require_once __DIR__ . '/../views/formas_pago/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_forma_pago(?)");
                $stmt->execute([$_POST['nombre']]);
                header('Location: index.php?url=formas_pago');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear la forma de pago: " . $e->getMessage();
                header('Location: index.php?url=formas_pago/create');
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_forma_pago_by_id(?)");
        $stmt->execute([$id]);
        $forma_pago = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($forma_pago) {
            require_once __DIR__ . '/../views/formas_pago/edit.php';
        } else {
            http_response_code(404);
            echo "Forma de Pago no encontrada.";
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_forma_pago'];
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_forma_pago(?, ?)");
                $stmt->execute([
                    $id,
                    $_POST['nombre']
                ]);
                header('Location: index.php?url=formas_pago');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar: " . $e->getMessage();
                header('Location: index.php?url=formas_pago/edit&id=' . $id);
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_forma_pago(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar si está en uso. " . $e->getMessage();
            }
        }
        header('Location: index.php?url=formas_pago');
        exit;
    }
}
?>
