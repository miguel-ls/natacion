<?php
require_once __DIR__ . '/../core/Database.php';

class AuthController {

    /**
     * Muestra el formulario de login o procesa el envío del mismo.
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            require_once __DIR__ . '/../views/login.php';
        }
    }

    /**
     * Muestra la página de dashboard si el usuario está autenticado.
     */
    public function dashboard() {
        $this->checkAuth(); // Proteger la página
        require_once __DIR__ . '/../views/dashboard.php';
    }

    /**
     * Procesa la lógica de inicio de sesión.
     */
    private function handleLogin() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error_message'] = 'Por favor, complete todos los campos.';
            header('Location: index.php?url=login');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("CALL sp_get_user_by_email(?)");
            $stmt->bindParam(1, $email, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Para probar, necesitamos un usuario. Asumamos que la contraseña es 'admin123'
            // En un caso real, un usuario se crearía con una contraseña hasheada.
            // echo password_hash('admin123', PASSWORD_DEFAULT); para generar el hash.

            if ($user && password_verify($password, $user['password'])) {
                // Contraseña correcta. Iniciar el proceso de 2FA.
                $this->initiate2FA($user);
            } else {
                $_SESSION['error_message'] = 'Credenciales incorrectas.';
                header('Location: index.php?url=login');
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error del servidor: ' . $e->getMessage();
            header('Location: index.php?url=login');
            exit;
        }
    }

    /**
     * Inicia el proceso de 2FA.
     */
    private function initiate2FA($user) {
        $code = rand(100000, 999999);
        $db = Database::getInstance()->getConnection();

        try {
            // Guardar el código en la BD
            $stmt = $db->prepare("CALL sp_update_user_2fa_code(?, ?)");
            $stmt->execute([$user['id_usuario'], $code]);

            // Enviar correo (simulación por ahora)
            // En un proyecto real, usar una librería como PHPMailer
            $mailSent = mail($user['email'], "Su código de acceso", "Su código es: $code");

            if (true) { // Simular que el correo siempre se envía por ahora
                $_SESSION['2fa_user_id'] = $user['id_usuario'];
                // Redirigir a la página de verificación de 2FA
                header('Location: index.php?url=verify_2fa');
                exit;
            } else {
                $_SESSION['error_message'] = 'No se pudo enviar el código de verificación.';
                header('Location: index.php?url=login');
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error del servidor al iniciar 2FA: ' . $e->getMessage();
            header('Location: index.php?url=login');
            exit;
        }
    }

    /**
     * Muestra el formulario de 2FA o procesa la verificación.
     */
    public function verify_2fa() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle2FAVerification();
        } else {
            if (empty($_SESSION['2fa_user_id'])) {
                header('Location: index.php?url=login');
                exit;
            }
            require_once __DIR__ . '/../views/verify_2fa.php';
        }
    }

    /**
     * Procesa la verificación del código 2FA.
     */
    private function handle2FAVerification() {
        $code = $_POST['2fa_code'] ?? '';
        $userId = $_SESSION['2fa_user_id'] ?? null;

        if (empty($code) || !$userId) {
            $_SESSION['error_message'] = 'El código es inválido o la sesión ha expirado.';
            header('Location: index.php?url=verify_2fa');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("CALL sp_get_user_by_id(?)");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($user && $user['auth_code_2fa'] === $code) {
                // Éxito: limpiar código 2FA y establecer sesión final
                $stmt_clean = $db->prepare("CALL sp_update_user_2fa_code(?, NULL)");
                $stmt_clean->execute([$userId]);

                unset($_SESSION['2fa_user_id']);
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_role'] = $user['rol'];

                header('Location: index.php?url=dashboard');
                exit;
            } else {
                $_SESSION['error_message'] = 'El código de verificación es incorrecto.';
                header('Location: index.php?url=verify_2fa');
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error del servidor: ' . $e->getMessage();
            header('Location: index.php?url=verify_2fa');
            exit;
        }
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header('Location: index.php?url=login');
        exit;
    }

    /**
     * Verifica si el usuario está autenticado. Si no, lo redirige al login.
     */
    public function checkAuth() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=login');
            exit;
        }
    }

    /**
     * Genera y/o retorna un token CSRF para la sesión actual.
     */
    public function getCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifica el token CSRF enviado en un formulario.
     * Si no es válido, termina la ejecución.
     */
    public function verifyCsrfToken() {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            // El token es inválido o no existe.
            // Limpiar el token de la sesión por seguridad.
            unset($_SESSION['csrf_token']);
            // Terminar la ejecución para prevenir el ataque.
            die('Error de validación CSRF. La solicitud ha sido rechazada por motivos de seguridad.');
        }
    }
}
?>
