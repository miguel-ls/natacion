<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php';

class ProfesorController {

    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->checkAuth();
    }

    /**
     * Muestra la lista de profesores.
     */
    public function index() {
        $search_term = $_GET['search'] ?? '';
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_profesores(?)");
            $stmt->execute([$search_term]);
            $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../views/profesores/index.php';
        } catch (PDOException $e) {
            echo "Error al cargar los profesores: " . $e->getMessage();
        }
    }

    /**
     * Muestra el formulario para crear un nuevo profesor.
     */
    public function create() {
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_tipos_profesor()");
            $stmt->execute();
            $tipos_profesor = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $tipos_profesor = [];
            $_SESSION['error_message'] = "Error al cargar los tipos de profesor: " . $e->getMessage();
        }
        require_once __DIR__ . '/../views/profesores/create.php';
    }

    /**
     * Almacena un nuevo profesor.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_create_profesor(?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['id_tipo_profesor'],
                    $_POST['documento_identidad'],
                    $_POST['telefono'],
                    $_POST['email'],
                    $_POST['direccion'],
                    $_POST['fecha_contratacion']
                ]);
                header('Location: index.php?url=profesores');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al crear el profesor: " . $e->getMessage();
                header('Location: index.php?url=profesores/create');
                exit;
            }
        }
    }

    /**
     * Muestra el formulario para editar un profesor.
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?url=profesores');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            // Fetch professor types
            $stmt_tipos = $db->prepare("CALL sp_get_all_tipos_profesor()");
            $stmt_tipos->execute();
            $tipos_profesor = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);
            $stmt_tipos->closeCursor();

            // Fetch professor data
            $stmt_profesor = $db->prepare("CALL sp_get_profesor_by_id(?)");
            $stmt_profesor->execute([$id]);
            $profesor = $stmt_profesor->fetch(PDO::FETCH_ASSOC);
            $stmt_profesor->closeCursor();

            if ($profesor) {
                require_once __DIR__ . '/../views/profesores/edit.php';
            } else {
                http_response_code(404);
                echo "Profesor no encontrado.";
            }
        } catch (PDOException $e) {
            echo "Error al buscar el profesor: " . $e->getMessage();
        }
    }

    /**
     * Actualiza un profesor.
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->auth->verifyCsrfToken();
            $id = $_POST['id_profesor'] ?? null;
            if (!$id) {
                header('Location: index.php?url=profesores');
                exit;
            }

            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_update_profesor(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $id,
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['id_tipo_profesor'],
                    $_POST['documento_identidad'],
                    $_POST['telefono'],
                    $_POST['email'],
                    $_POST['direccion'],
                    $_POST['fecha_contratacion'],
                    $_POST['estado']
                ]);
                header('Location: index.php?url=profesores');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al actualizar el profesor: " . $e->getMessage();
                header('Location: index.php?url=profesores/edit&id=' . $id);
                exit;
            }
        }
    }

    /**
     * "Elimina" un profesor (lo marca como inactivo).
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("CALL sp_delete_profesor(?)");
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al eliminar el profesor: " . $e->getMessage();
            }
        }
        header('Location: index.php?url=profesores');
        exit;
    }

    /**
     * Busca profesores para un campo de autocompletar (AJAX).
     */
    public function search() {
        header('Content-Type: application/json');
        $searchTerm = $_GET['term'] ?? '';

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_all_profesores(?)");
            $stmt->execute([$searchTerm]);
            $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [];
            foreach ($profesores as $profesor) {
                // Formato esperado por muchos plugins de autocompletar (ej. jQuery UI)
                $results[] = [
                    'id' => $profesor['id_profesor'],
                    'label' => $profesor['nombres'] . ' ' . $profesor['apellidos'],
                    'value' => $profesor['nombres'] . ' ' . $profesor['apellidos']
                ];
            }
            echo json_encode($results);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Obtiene profesores filtrados por tipo para una llamada AJAX.
     */
    public function getByTipo() {
        header('Content-Type: application/json');
        $id_tipo = $_GET['id_tipo'] ?? 0;

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_profesores_by_tipo(?)");
            $stmt->execute([$id_tipo]);
            $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($profesores);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
