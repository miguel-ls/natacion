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
        $search_term = $_GET['search'] ?? '';
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("CALL sp_get_all_horarios_details(?)");
        $stmt->execute([$search_term]);
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

            // Llamada al nuevo SP de validación unificada
            $stmt = $db->prepare("CALL sp_check_schedule_conflict(?, ?, ?, ?, ?, ?, ?, @conflict_type, @conflicting_id)");
            $stmt->execute([
                $_POST['id_profesor'],
                $_POST['id_carril'],
                $_POST['fecha_inicio'],
                $_POST['fecha_fin'],
                $_POST['hora_inicio'],
                $_POST['hora_fin'],
                0 // id_horario_a_excluir = 0 porque estamos creando uno nuevo
            ]);
            $stmt->closeCursor();

            // Obtener los resultados de los parámetros OUT
            $result = $db->query("SELECT @conflict_type AS conflict_type, @conflicting_id AS conflicting_id")->fetch(PDO::FETCH_ASSOC);
            $conflict_type = $result['conflict_type'];

            if ($conflict_type !== 'NONE') {
                $message = "Error de validación desconocido.";
                if ($conflict_type === 'TEACHER') {
                    $message = "Conflicto de Horario: El profesor ya está asignado a otro curso en un horario que se cruza.";
                } elseif ($conflict_type === 'LANE') {
                    $message = "Conflicto de Horario: El carril ya está ocupado por otro curso en un horario que se cruza.";
                }
                $_SESSION['error_message'] = $message;
                // Guardar los datos del formulario para no perderlos
                $_SESSION['form_data'] = $_POST;
                header('Location: index.php?url=horarios/create');
                exit;
            }

            // Si no hay conflictos, proceder a crear el horario
            $stmt_create = $db->prepare("CALL sp_create_horario(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_create->execute([
                $_POST['id_curso'],
                $_POST['id_profesor'],
                $_POST['id_carril'],
                $_POST['id_tipo_horario'],
                $_POST['hora_inicio'],
                $_POST['hora_fin'],
                $_POST['fecha_inicio'],
                $_POST['fecha_fin']
            ]);
            unset($_SESSION['form_data']); // Limpiar datos guardados
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

            // Llamada al nuevo SP de validación unificada
            $stmt = $db->prepare("CALL sp_check_schedule_conflict(?, ?, ?, ?, ?, ?, ?, @conflict_type, @conflicting_id)");
            $stmt->execute([
                $_POST['id_profesor'],
                $_POST['id_carril'],
                $_POST['fecha_inicio'],
                $_POST['fecha_fin'],
                $_POST['hora_inicio'],
                $_POST['hora_fin'],
                $id // id_horario_a_excluir, para que no compare consigo mismo
            ]);
            $stmt->closeCursor();

            // Obtener los resultados de los parámetros OUT
            $result = $db->query("SELECT @conflict_type AS conflict_type, @conflicting_id AS conflicting_id")->fetch(PDO::FETCH_ASSOC);
            $conflict_type = $result['conflict_type'];

            if ($conflict_type !== 'NONE') {
                $message = "Error de validación desconocido.";
                if ($conflict_type === 'TEACHER') {
                    $message = "Conflicto de Horario: El profesor ya está asignado a otro curso en un horario que se cruza.";
                } elseif ($conflict_type === 'LANE') {
                    $message = "Conflicto de Horario: El carril ya está ocupado por otro curso en un horario que se cruza.";
                }
                $_SESSION['error_message'] = $message;
                $_SESSION['form_data'] = $_POST;
                header('Location: index.php?url=horarios/edit&id=' . $id);
                exit;
            }

            // Si no hay conflictos, proceder a actualizar
            $stmt_update = $db->prepare("CALL sp_update_horario(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_update->execute([
                $id,
                $_POST['id_curso'],
                $_POST['id_profesor'],
                $_POST['id_carril'],
                $_POST['id_tipo_horario'],
                $_POST['hora_inicio'],
                $_POST['hora_fin'],
                $_POST['fecha_inicio'],
                $_POST['fecha_fin']
            ]);
            unset($_SESSION['form_data']);
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
        $stmt_cursos = $db->prepare("CALL sp_get_all_cursos(?)");
        $stmt_cursos->execute(['']);
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
        $stmt_cursos->closeCursor();

        $stmt_profesores = $db->prepare("CALL sp_get_all_profesores(?)");
        $stmt_profesores->execute(['']);
        $profesores = $stmt_profesores->fetchAll(PDO::FETCH_ASSOC);
        $stmt_profesores->closeCursor();

        $stmt_carriles = $db->prepare("CALL sp_get_all_carriles(?)");
        $stmt_carriles->execute(['']);
        $carriles = $stmt_carriles->fetchAll(PDO::FETCH_ASSOC);
        $stmt_carriles->closeCursor();

        $stmt_tipos_horario = $db->prepare("CALL sp_get_all_tipos_horario(?)");
        $stmt_tipos_horario->execute(['']);
        $tipos_horario = $stmt_tipos_horario->fetchAll(PDO::FETCH_ASSOC);
        $stmt_tipos_horario->closeCursor();

        return compact('cursos', 'profesores', 'carriles', 'tipos_horario');
    }

}
?>
