<?php
/**
 * Configuración de Email - Gmail SMTP
 * 
 * CONFIGURACIÓN COMPLETADA ✅
 */

return [
    // Configuración SMTP de Gmail
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls', // 'tls' o 'ssl'
    'smtp_auth' => true,
    
    // Credenciales - CONFIGURADAS
    'smtp_username' => 'spiritboom672@gmail.com', // Tu email de Gmail
    'smtp_password' => 'avpfnjukdyphmgfo', // Contraseña de aplicación (sin espacios)
    
    // Información del remitente
    'from_email' => 'spiritboom672@gmail.com', // Mismo que smtp_username
    'from_name' => 'Sleppy Store', // Nombre que aparece en Gmail
    
    // Configuración de emails
    'enable_debug' => false, // true para ver logs de debug
    'charset' => 'UTF-8',
];
