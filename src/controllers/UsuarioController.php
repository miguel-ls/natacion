<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class UsuarioController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_all_users()");
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/usuarios/index.php';
    }

    public function create() {
        require_once __DIR__ . '/../views/usuarios/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            if ($_POST['password'] !== $_POST['password_confirm']) {
                $_SESSION['error_message'] = "Las contraseñas no coinciden.";
                header('Location: index.php?url=usuarios/create');
                exit;
            }

            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_user(?, ?, ?)");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['email'],
                    $password
                ]);
                header('Location: index.php?url=usuarios');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear el usuario: " . $e->getMessage();
                header('Location: index.php?url=usuarios/create');
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_user_by_id(?)");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            require_once __DIR__ . '/../views/usuarios/edit.php';
        } else {
            http_response_code(404);
            echo "Usuario no encontrado.";
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_usuario'];
            $db = Database::getInstance()->getConnection();

            try {
                // Actualizar datos del usuario
                $stmt_user = $db->prepare("CALL sp_update_user(?, ?, ?)");
                $stmt_user->execute([$id, $_POST['nombre'], $_POST['email']]);
                $stmt_user->closeCursor();

                // Actualizar contraseña si se proporcionó una nueva
                if (!empty($_POST['password'])) {
                    if ($_POST['password'] !== $_POST['password_confirm']) {
                        $_SESSION['error_message'] = "Las contraseñas no coinciden.";
                        header('Location: index.php?url=usuarios/edit&id=' . $id);
                        exit;
                    }
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt_pass = $db->prepare("CALL sp_change_user_password(?, ?)");
                    $stmt_pass->execute([$id, $password]);
                    $stmt_pass->closeCursor();
                }

                header('Location: index.php?url=usuarios');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar: " . $e->getMessage();
                header('Location: index.php?url=usuarios/edit&id=' . $id);
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        // Prevenir que un usuario se elimine a sí mismo
        if ($id && $id != $_SESSION['user_id']) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_user(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                 $_SESSION['error_message'] = "Error al eliminar el usuario: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = "No puedes eliminar tu propio usuario.";
        }
        header('Location: index.php?url=usuarios');
        exit;
    }
}
?>
