<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class PrecioCursoController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $search_term = $_GET['search'] ?? '';
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_all_precios_cursos(?)");
        $stmt->execute([$search_term]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/precios_cursos/index.php';
    }

    public function create() {
        $db = Database::getInstance()->getConnection();
        $stmt_cursos = $db->prepare("CALL sp_get_all_cursos()");
        $stmt_cursos->execute();
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_cursos->closeCursor();

        $stmt_tipos = $db->prepare("CALL sp_get_all_tipos_precio()");
        $stmt_tipos->execute();
        $tipos_precio = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_tipos->closeCursor();

        require_once __DIR__ . '/../views/precios_cursos/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_precio_curso(?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['id_curso'],
                    $_POST['id_tipo_precio'],
                    $_POST['precio'],
                    $_POST['fecha_inicio'],
                    $_POST['fecha_fin']
                ]);
                header('Location: index.php?url=precios_cursos');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear: " . $e->getMessage();
                header('Location: index.php?url=precios_cursos/create');
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: index.php?url=precios_cursos'); exit; }

        $db = Database::getInstance()->getConnection();
        $stmt_item = $db->prepare("CALL sp_get_precio_curso_by_id(?)");
        $stmt_item->execute([$id]);
        $item = $stmt_item->fetch(PDO::FETCH_ASSOC);
        $stmt_item->closeCursor();

        if ($item) {
            $stmt_cursos = $db->prepare("CALL sp_get_all_cursos()");
            $stmt_cursos->execute();
            $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
            $stmt_cursos->closeCursor();

            $stmt_tipos = $db->prepare("CALL sp_get_all_tipos_precio()");
            $stmt_tipos->execute();
            $tipos_precio = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);
            $stmt_tipos->closeCursor();

            require_once __DIR__ . '/../views/precios_cursos/edit.php';
        } else {
            http_response_code(404);
            echo "Precio de Curso no encontrado.";
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_precio_curso'];
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_precio_curso(?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $id,
                    $_POST['id_curso'],
                    $_POST['id_tipo_precio'],
                    $_POST['precio'],
                    $_POST['fecha_inicio'],
                    $_POST['fecha_fin']
                ]);
                header('Location: index.php?url=precios_cursos');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar: " . $e->getMessage();
                header('Location: index.php?url=precios_cursos/edit&id=' . $id);
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("CALL sp_delete_precio_curso(?)");
            $stmt->execute([$id]);
        }
        header('Location: index.php?url=precios_cursos');
        exit;
    }
}
?>
