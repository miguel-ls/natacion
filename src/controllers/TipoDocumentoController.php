<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class TipoDocumentoController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_tipos_documento()");
            $stmt->execute();
            $tipos_documento = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../views/tipos_documento/index.php';
        } catch (PDOException $e) {
            echo "Error al cargar los tipos de documento: " . $e->getMessage();
        }
    }

    public function create() {
        require_once __DIR__ . '/../views/tipos_documento/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_tipo_documento(?, ?, ?)");
                $stmt->execute([
                    $_POST['descripcion'],
                    $_POST['longitud'],
                    $_POST['sunat']
                ]);
                header('Location: index.php?url=tipos_documento');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear el tipo de documento: " . $e->getMessage();
                header('Location: index.php?url=tipos_documento/create');
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=tipos_documento');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_tipo_documento_by_id(?)");
            $stmt->execute([$id]);
            $tipo_documento = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($tipo_documento) {
                require_once __DIR__ . '/../views/tipos_documento/edit.php';
            } else {
                http_response_code(404);
                echo "Tipo de documento no encontrado.";
            }
        } catch (PDOException $e) {
            echo "Error al buscar el tipo de documento: " . $e->getMessage();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id'] ?? null;
            if (!$id) {
                header('Location: index.php?url=tipos_documento');
                exit;
            }

            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_tipo_documento(?, ?, ?, ?)");
                $stmt->execute([
                    $id,
                    $_POST['descripcion'],
                    $_POST['longitud'],
                    $_POST['sunat']
                ]);
                header('Location: index.php?url=tipos_documento');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar el tipo de documento: " . $e->getMessage();
                header('Location: index.php?url=tipos_documento/edit&id=' . $id);
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_tipo_documento(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar el tipo de documento. Es posible que esté en uso.";
            }
        }
        header('Location: index.php?url=tipos_documento');
        exit;
    }
}
?>
