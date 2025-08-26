<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class HorarioController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_all_horarios_details()");
        $stmt->execute();
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/horarios/index.php';
    }

    public function create() {
        $db = Database::getInstance()->getConnection();
        $data = $this->getRelatedData($db);
        extract($data);
        require_once __DIR__ . '/../views/horarios/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("CALL sp_create_horario(?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['id_curso'],
                $_POST['id_profesor'],
                $_POST['id_carril'],
                $_POST['id_tipo_horario'],
                $_POST['hora_inicio'],
                $_POST['hora_fin']
            ]);
            header('Location: index.php?url=horarios');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        $db = Database::getInstance()->getConnection();

        $stmt_horario = $db->prepare("CALL sp_get_horario_by_id(?)");
        $stmt_horario->execute([$id]);
        $horario = $stmt_horario->fetch(PDO::FETCH_ASSOC);
        $stmt_horario->closeCursor();

        if ($horario) {
            $data = $this->getRelatedData($db);
            extract($data);
            require_once __DIR__ . '/../views/horarios/edit.php';
        } else {
            http_response_code(404);
            echo "Horario no encontrado.";
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_horario'];
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("CALL sp_update_horario(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $_POST['id_curso'],
                $_POST['id_profesor'],
                $_POST['id_carril'],
                $_POST['id_tipo_horario'],
                $_POST['hora_inicio'],
                $_POST['hora_fin']
            ]);
            header('Location: index.php?url=horarios');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_horario(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar el horario si tiene matrículas asociadas. " . $e->getMessage();
            }
        }
        header('Location: index.php?url=horarios');
        exit;
    }

    private function getRelatedData($db) {
        $stmt_cursos = $db->prepare("CALL sp_get_all_cursos()");
        $stmt_cursos->execute();
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_cursos->closeCursor();

        $stmt_profesores = $db->prepare("CALL sp_get_all_profesores()");
        $stmt_profesores->execute();
        $profesores = $stmt_profesores->fetchAll(PDO::FETCH_ASSOC);
        $stmt_profesores->closeCursor();

        $stmt_carriles = $db->prepare("CALL sp_get_all_carriles()");
        $stmt_carriles->execute();
        $carriles = $stmt_carriles->fetchAll(PDO::FETCH_ASSOC);
        $stmt_carriles->closeCursor();

        $stmt_tipos_horario = $db->prepare("CALL sp_get_all_tipos_horario()");
        $stmt_tipos_horario->execute();
        $tipos_horario = $stmt_tipos_horario->fetchAll(PDO::FETCH_ASSOC);
        $stmt_tipos_horario->closeCursor();

        return compact('cursos', 'profesores', 'carriles', 'tipos_horario');
    }
}
?>
