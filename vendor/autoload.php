<?php
/**
 * Autoloader para PHPMailer
 */

spl_autoload_register(function ($class) {
    // Solo autoload de clases PHPMailer
    if (strpos($class, 'PHPMailer\\PHPMailer\\') === 0) {
        // Extraer el nombre de la clase sin el namespace
        $className = substr($class, strlen('PHPMailer\\PHPMailer\\'));
        $file = __DIR__ . '/phpmailer/phpmailer/src/' . $className . '.php';
        
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
});
