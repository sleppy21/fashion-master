<?php
// Cargar configuración de rutas
require_once __DIR__ . '/../../../config/path.php';

session_start();

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#1a1a1a">
    <meta name="description" content="Recuperar contraseña - Fashion Store">
    <title>Recuperar Contraseña - Fashion Store</title>
    
    <!-- Preconnect para mejorar performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 para alertas modernas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- CSS Moderno Personalizado (después de Bootstrap para sobrescribir) -->
    <link rel="stylesheet" href="<?= asset('css/auth-modern.css') ?>">
</head>
<body>
    <!-- Alertas en la esquina superior derecha (fuera de contenedores) -->
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fa fa-exclamation-circle"></i> <?= e($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if(!empty($success)): ?>
        <div class="alert alert-success" role="alert">
            <i class="fa fa-check-circle"></i> <?= e($success) ?>
        </div>
    <?php endif; ?>

    <!-- Formas Geométricas Animadas -->
    <div class="geometric-shapes">
        <div class="shape shape-circle"></div>
        <div class="shape shape-triangle"></div>
        <div class="shape shape-square"></div>
        <div class="shape shape-hexagon"></div>
    </div>

    <div class="forgot-container">
        <div>
            <div class="forgot-header">
                <div class="forgot-icon">
                    <i class="fa fa-key"></i>
                </div>
                <h2>¿Olvidaste tu Contraseña?</h2>
                <p>No te preocupes. Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.</p>
            </div>
            
            <div class="info-box">
                <p>
                    <i class="fa fa-info-circle"></i>
                    <span>Te enviaremos un enlace de recuperación que será válido por 1 hora y podrá usarse una sola vez. Revisa tu bandeja de entrada y spam.</span>
                </p>
            </div>
            
            <form method="POST" action="<?= url('app/controllers/AuthController.php?action=forgot-password') ?>" novalidate>
                <div class="form-group">
                    <label for="email">Email Registrado</label>
                    <div class="input-group">
                        <i class="fa fa-envelope"></i>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="tu@email.com"
                               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                               title="Ingresa un email válido (ejemplo: usuario@gmail.com)"
                               autocomplete="email"
                               required
                               autofocus>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fa fa-paper-plane"></i> Enviar Enlace de Recuperación
                </button>
            </form>
            
            <div class="back-links">
                <a href="<?= url('login.php') ?>">
                    <i class="fa fa-arrow-left"></i> Volver al Login
                </a>
                <a href="<?= url('index.php') ?>">
                    <i class="fa fa-home"></i> Ir al Inicio
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.3 JS Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <script>
        // Auto-ocultar alertas después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.classList.add('auto-hide');
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 5000);
            });
        });
    </script>
    
    <!-- Validación de Formularios -->
    <script src="<?php echo url('public/assets/js/form-validation.js'); ?>"></script>
</body>
</html>
