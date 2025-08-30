<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class CursoController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $search_term = $_GET['search'] ?? '';
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_cursos(?)");
            $stmt->execute([$search_term]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../views/cursos/index.php';
        } catch (PDOException $e) {
            echo "Error al cargar los cursos: " . $e->getMessage();
        }
    }

    public function create() {
        require_once __DIR__ . '/../views/cursos/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_curso(?, ?, ?)");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['codigo_erp']
                ]);
                header('Location: index.php?url=cursos');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear el curso: " . $e->getMessage();
                header('Location: index.php?url=cursos/create');
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=cursos');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_curso_by_id(?)");
            $stmt->execute([$id]);
            $curso = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($curso) {
                require_once __DIR__ . '/../views/cursos/edit.php';
            } else {
                http_response_code(404);
                echo "Curso no encontrado.";
            }
        } catch (PDOException $e) {
            echo "Error al buscar el curso: " . $e->getMessage();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_curso'] ?? null;
            if (!$id) {
                header('Location: index.php?url=cursos');
                exit;
            }

            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_curso(?, ?, ?, ?)");
                $stmt->execute([
                    $id,
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['codigo_erp']
                ]);
                header('Location: index.php?url=cursos');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar el curso: " . $e->getMessage();
                header('Location: index.php?url=cursos/edit&id=' . $id);
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_curso(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar el curso si está siendo utilizado en horarios. " . $e->getMessage();
            }
        }
        header('Location: index.php?url=cursos');
        exit;
    }

    /**
     * Busca cursos para una llamada AJAX y devuelve JSON.
     */
    public function search() {
        header('Content-Type: application/json');
        $search_term = $_GET['term'] ?? '';

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_cursos(?)");
            $stmt->execute([$search_term]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($cursos);
        } catch (PDOException $e) {
            echo json_encode([]); // Devuelve array vacío en caso de error
        }
        exit;
    }
}
?>
