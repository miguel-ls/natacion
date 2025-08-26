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

            // Validar conflicto de horario para el profesor
            $isConflict = $this->checkProfesorConflict($db, $_POST['id_profesor'], $_POST['id_tipo_horario'], $_POST['hora_inicio'], $_POST['hora_fin']);
            if ($isConflict) {
                $_SESSION['error_message'] = "Conflicto de horario: El profesor ya tiene una clase asignada en un día y hora que se superponen.";
                header('Location: index.php?url=horarios/create');
                exit;
            }

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

            // Validar conflicto de horario para el profesor, excluyendo el horario actual
            $isConflict = $this->checkProfesorConflict($db, $_POST['id_profesor'], $_POST['id_tipo_horario'], $_POST['hora_inicio'], $_POST['hora_fin'], $id);
            if ($isConflict) {
                $_SESSION['error_message'] = "Conflicto de horario: El profesor ya tiene una clase asignada en un día y hora que se superponen.";
                header('Location: index.php?url=horarios/edit&id=' . $id);
                exit;
            }

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

    private function checkProfesorConflict($db, $id_profesor, $id_tipo_horario, $hora_inicio, $hora_fin, $id_horario_excluir = 0) {
        // Obtener los días para el nuevo horario
        $stmt_dias_nuevos = $db->prepare("SELECT dias_semana FROM tipos_horario WHERE id_tipo_horario = ?");
        $stmt_dias_nuevos->execute([$id_tipo_horario]);
        $dias_nuevos_str = $stmt_dias_nuevos->fetchColumn();
        $stmt_dias_nuevos->closeCursor();
        if (!$dias_nuevos_str) return false;
        $dias_nuevos = explode(',', $dias_nuevos_str);

        // Obtener todos los horarios existentes para el profesor
        $stmt_existentes = $db->prepare("CALL sp_get_horarios_by_profesor(?)");
        $stmt_existentes->execute([$id_profesor]);
        $horarios_existentes = $stmt_existentes->fetchAll(PDO::FETCH_ASSOC);
        $stmt_existentes->closeCursor();

        foreach ($horarios_existentes as $h_existente) {
            // Omitir el mismo horario si estamos actualizando
            if ($h_existente['id_horario'] == $id_horario_excluir) {
                continue;
            }

            // Revisar si hay superposición de días
            $dias_existentes = explode(',', $h_existente['dias_semana']);
            $dias_comunes = array_intersect($dias_nuevos, $dias_existentes);

            if (!empty($dias_comunes)) {
                // Si los días se superponen, revisar si las horas se superponen
                $inicio_existente = strtotime($h_existente['hora_inicio']);
                $fin_existente = strtotime($h_existente['hora_fin']);
                $inicio_nuevo = strtotime($hora_inicio);
                $fin_nuevo = strtotime($hora_fin);

                if ($inicio_nuevo < $fin_existente && $fin_nuevo > $inicio_existente) {
                    return true; // Conflicto encontrado
                }
            }
        }

        return false; // No hay conflicto
    }
}
?>
