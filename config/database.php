<?php
// URL base del proyecto. ¡Asegúrate de que termine con una barra inclinada /!
// Ejemplo: http://localhost/natacion/public/
define('BASE_URL', 'http://localhost/natacion/public/');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Colocar la contraseña si es necesaria
define('DB_NAME', 'sistema_natacion');

// Configuración para el envío de correos (para 2FA)
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_USER', 'no-reply@example.com');
define('MAIL_PASS', 'your-email-password');
define('MAIL_PORT', 587); // o 465
?>
