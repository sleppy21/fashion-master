<?php
/**
 * Helper para el envío de emails
 * Maneja el envío de correos electrónicos del sistema usando Gmail SMTP
 */

// Cargar autoloader de vendor
require_once __DIR__ . '/../../vendor/autoload.php';

// Importar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    
    private $config;
    private $mailer;
    
    public function __construct() {
        // Cargar configuración de email
        $this->config = require __DIR__ . '/../../config/email.php';
        
        // Inicializar PHPMailer
        $this->mailer = new PHPMailer(true);
        $this->setupSMTP();
    }
    
    /**
     * Configurar SMTP de Gmail
     */
    private function setupSMTP() {
        try {
            // Configuración del servidor SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config['smtp_host'];
            $this->mailer->SMTPAuth   = $this->config['smtp_auth'];
            $this->mailer->Username   = $this->config['smtp_username'];
            $this->mailer->Password   = $this->config['smtp_password'];
            $this->mailer->SMTPSecure = $this->config['smtp_secure'];
            $this->mailer->Port       = $this->config['smtp_port'];
            
            // Configuración general
            $this->mailer->CharSet = $this->config['charset'];
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            
            // Debug (solo si está habilitado)
            if($this->config['enable_debug']) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
                $this->mailer->Debugoutput = function($str, $level) {
                    $this->logDebug("DEBUG: $str");
                };
            }
        } catch (Exception $e) {
            $this->logError("Error configurando SMTP: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar email de recuperación de contraseña
     * 
     * @param string $to_email Email del destinatario
     * @param string $nombre Nombre del usuario
     * @param string $reset_link Enlace de recuperación
     * @return bool True si se envió correctamente
     */
    public function sendPasswordResetEmail($to_email, $nombre, $reset_link) {
        $subject = 'Recuperación de Contraseña - Fashion Store';
        $message = $this->getPasswordResetEmailTemplate($nombre, $reset_link);
        
        return $this->sendEmail($to_email, $subject, $message);
    }
    
    /**
     * Enviar email de bienvenida
     * 
     * @param string $to_email Email del destinatario
     * @param string $nombre Nombre del usuario
     * @param string $username Username del usuario
     * @return bool True si se envió correctamente
     */
    public function sendWelcomeEmail($to_email, $nombre, $username) {
        $subject = '¡Bienvenido a Fashion Store!';
        $message = $this->getWelcomeEmailTemplate($nombre, $username);
        
        return $this->sendEmail($to_email, $subject, $message);
    }
    
    /**
     * Envía un email personalizado (para pruebas y usos generales)
     * 
     * @param string $to_email Email del destinatario
     * @param string $to_name Nombre del destinatario
     * @param string $subject Asunto del email
     * @param string $message_html Mensaje HTML del email
     * @return bool True si se envió correctamente
     */
    public function sendCustomEmail($to_email, $to_name, $subject, $message_html) {
        return $this->sendEmail($to_email, $subject, $message_html);
    }
    
    /**
     * Función principal para enviar email con PHPMailer
     * 
     * @param string $to Email del destinatario
     * @param string $subject Asunto del email
     * @param string $message Mensaje HTML del email
     * @return bool True si se envió correctamente
     */
    private function sendEmail($to, $subject, $message) {
        try {
            // Limpiar destinatarios anteriores
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Configurar destinatario
            $this->mailer->addAddress($to);
            
            // Contenido del email
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $message;
            $this->mailer->AltBody = strip_tags($message); // Versión texto plano
            
            // Enviar
            $sent = $this->mailer->send();
            
            // Log del envío
            $this->logEmailAttempt($to, $subject, $sent);
            
            return $sent;
            
        } catch (Exception $e) {
            $error = "Error al enviar email a $to: {$this->mailer->ErrorInfo}";
            $this->logError($error);
            $this->logEmailAttempt($to, $subject, false);
            return false;
        }
    }
    
    /**
     * Template para email de recuperación de contraseña
     */
    private function getPasswordResetEmailTemplate($nombre, $reset_link) {
        return '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Fashion Store</h1>
                            <p style="color: #ffffff; margin: 10px 0 0; font-size: 16px;">Recuperación de Contraseña</p>
                        </td>
                    </tr>
                    
                    <!-- Contenido -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333; margin: 0 0 20px; font-size: 24px;">Hola, ' . htmlspecialchars($nombre) . '!</h2>
                            
                            <p style="color: #666; line-height: 1.6; margin: 0 0 20px; font-size: 16px;">
                                Recibimos una solicitud para restablecer la contraseña de tu cuenta en Fashion Store.
                            </p>
                            
                            <p style="color: #666; line-height: 1.6; margin: 0 0 30px; font-size: 16px;">
                                Haz clic en el siguiente botón para crear una nueva contraseña:
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="' . $reset_link . '" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                                            Restablecer Contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #999; line-height: 1.6; margin: 30px 0 0; font-size: 14px;">
                                Si no solicitaste este cambio, puedes ignorar este email. Tu contraseña no será modificada.
                            </p>
                            
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="color: #856404; margin: 0; font-size: 14px;">
                                    <strong>⚠️ Importante:</strong> Este enlace expirará en <strong>1 hora</strong> y solo puede usarse <strong>una vez</strong> por seguridad.
                                </p>
                            </div>
                            
                            <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                            
                            <p style="color: #999; font-size: 12px; margin: 0;">
                                Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:<br>
                                <a href="' . $reset_link . '" style="color: #667eea; word-break: break-all;">' . $reset_link . '</a>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px 20px; text-align: center;">
                            <p style="color: #999; margin: 0; font-size: 14px;">
                                &copy; 2025 Fashion Store. Todos los derechos reservados.
                            </p>
                            <p style="color: #999; margin: 10px 0 0; font-size: 12px;">
                                Este es un email automático, por favor no responder.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
        ';
    }
    
    /**
     * Template para email de bienvenida
     */
    private function getWelcomeEmailTemplate($nombre, $username) {
        return '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Fashion Store</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); padding: 40px 20px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 32px;">¡Bienvenido a Fashion Store!</h1>
                        </td>
                    </tr>
                    
                    <!-- Contenido -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333; margin: 0 0 20px; font-size: 24px;">Hola, ' . htmlspecialchars($nombre) . '!</h2>
                            
                            <p style="color: #666; line-height: 1.8; margin: 0 0 20px; font-size: 16px;">
                                Gracias por unirte a Fashion Store, tu nueva tienda de moda favorita. Estamos emocionados de tenerte con nosotros.
                            </p>
                            
                            <p style="color: #666; line-height: 1.8; margin: 0 0 20px; font-size: 16px;">
                                Tu cuenta ha sido creada exitosamente con el usuario: <strong>' . htmlspecialchars($username) . '</strong>
                            </p>
                            
                            <h3 style="color: #333; margin: 30px 0 15px; font-size: 20px;">¿Qué puedes hacer ahora?</h3>
                            
                            <ul style="color: #666; line-height: 2; font-size: 16px;">
                                <li>Explora nuestro catálogo de productos</li>
                                <li>Agrega artículos a tus favoritos</li>
                                <li>Realiza tu primera compra y disfruta de envío gratis</li>
                                <li>Accede a ofertas exclusivas para miembros</li>
                            </ul>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="http://localhost/fashion-master/index.php" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                                            Comenzar a Comprar
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #999; line-height: 1.6; margin: 30px 0 0; font-size: 14px; text-align: center;">
                                Si tienes alguna pregunta, no dudes en contactarnos.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px 20px; text-align: center;">
                            <p style="color: #999; margin: 0; font-size: 14px;">
                                &copy; 2025 Fashion Store. Todos los derechos reservados.
                            </p>
                            <p style="color: #999; margin: 10px 0 0; font-size: 12px;">
                                Este es un email automático, por favor no responder.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
        ';
    }
    
    /**
     * Registrar intento de envío de email
     */
    private function logEmailAttempt($to, $subject, $success) {
        $log_file = __DIR__ . '/../../logs/email_log.txt';
        $log_dir = dirname($log_file);
        
        // Crear directorio de logs si no existe
        if(!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $status = $success ? 'ENVIADO' : 'FALLIDO';
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$status] To: $to | Subject: $subject\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Registrar errores
     */
    private function logError($error) {
        $log_file = __DIR__ . '/../../logs/email_error.txt';
        $log_dir = dirname($log_file);
        
        if(!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] ERROR: $error\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Registrar debug
     */
    private function logDebug($message) {
        if(!$this->config['enable_debug']) {
            return;
        }
        
        $log_file = __DIR__ . '/../../logs/email_debug.txt';
        $log_dir = dirname($log_file);
        
        if(!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
}
?>
