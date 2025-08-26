<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class TipoHorarioController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_all_tipos_horario()");
        $stmt->execute();
        $tipos_horario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/tipos_horario/index.php';
    }

    public function create() {
        require_once __DIR__ . '/../views/tipos_horario/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_tipo_horario(?, ?)");
                // Asegurarse de que 'dias_semana' es un array antes de usar implode
                $dias_semana = isset($_POST['dias_semana']) && is_array($_POST['dias_semana']) ? implode(',', $_POST['dias_semana']) : '';
                $stmt->execute([
                    $_POST['nombre'],
                    $dias_semana
                ]);
                header('Location: index.php?url=tipos_horario');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear el tipo de horario: " . $e->getMessage();
                header('Location: index.php?url=tipos_horario/create');
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_tipo_horario_by_id(?)");
        $stmt->execute([$id]);
        $tipo_horario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tipo_horario) {
            require_once __DIR__ . '/../views/tipos_horario/edit.php';
        } else {
            http_response_code(404);
            echo "Tipo de Horario no encontrado.";
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_tipo_horario'];
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_tipo_horario(?, ?, ?)");
                $dias_semana = isset($_POST['dias_semana']) && is_array($_POST['dias_semana']) ? implode(',', $_POST['dias_semana']) : '';
                $stmt->execute([
                    $id,
                    $_POST['nombre'],
                    $dias_semana
                ]);
                header('Location: index.php?url=tipos_horario');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar el tipo de horario: " . $e->getMessage();
                header('Location: index.php?url=tipos_horario/edit&id=' . $id);
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_tipo_horario(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "No se puede eliminar si está en uso. " . $e->getMessage();
            }
        }
        header('Location: index.php?url=tipos_horario');
        exit;
    }
}
?>
