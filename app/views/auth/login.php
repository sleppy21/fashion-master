<?php
// Cargar configuración de rutas
require_once __DIR__ . '/../../../config/path.php';

session_start();

// Si ya está logueado, redirigir al dashboard
if(isset($_SESSION['user_id'])) {
    header('Location: ' . url('index.php'));
    exit;
}

// Verificar si hay cookie de "recordar sesión"
if(!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $authController = new AuthController();
    $authController->loginFromCookie();
}

// Helper function para escapar HTML
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Obtener mensajes de sesión
$error = $_SESSION['error'] ?? $_SESSION['login_error'] ?? '';
$success = $_SESSION['success'] ?? $_SESSION['success_message'] ?? '';
$last_username = $_SESSION['last_username'] ?? '';

// Limpiar mensajes
unset($_SESSION['login_error'], $_SESSION['success_message'], $_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#1a1a1a">
    <meta name="description" content="Inicia sesión en SleppyStore - Tu tienda de moda online">
    <title>Iniciar Sesión - SleppyStore</title>
    
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
    
    <!-- Mobile Override - MÁXIMA PRIORIDAD para checkboxes pequeños -->
    <link rel="stylesheet" href="<?= asset('css/auth-mobile-override.css') ?>">
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

    <div class="login-container">
        <div class="d-flex flex-column flex-md-row">
            <!-- Left Side - Branding -->
            <div class="login-left">
                <div class="brand-logo">
                    <i class="fa fa-shopping-bag"></i>
                    <span>Fashion Store</span>
                </div>
                <div class="welcome-text text-center text-md-start">
                    <h3>¡Bienvenido de vuelta!</h3>
                    <p>Descubre las últimas tendencias en moda.</p>
                    <p>Encuentra tu estilo perfecto con nosotros.</p>
                </div>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fa fa-shield-alt"></i>
                        <span>Compra 100% segura</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fa fa-truck"></i>
                        <span>Envíos rápidos y seguros</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fa fa-tags"></i>
                        <span>Mejores precios y ofertas</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <div class="login-right">
                <div class="login-header">
                    <h2>Iniciar Sesión</h2>
                    <p>Ingresa tus credenciales para continuar</p>
                </div>
                
                <!-- Login Form -->
                <form method="POST" action="<?= url('app/controllers/AuthController.php') ?>" id="loginForm" class="login-form" novalidate>
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="username">Usuario o Email</label>
                        <div class="input-group">
                            <i class="fa fa-user"></i>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   value="<?= e($last_username) ?>"
                                   placeholder="Ingresa tu usuario o email"
                                   autocomplete="username"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-group input-password">
                            <i class="fa fa-lock" onclick="togglePasswordWithLock('password')" title="Mostrar/Ocultar contraseña"></i>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Ingresa tu contraseña"
                                   autocomplete="current-password"
                                   required>
                        </div>
                    </div>
                    
                    <div class="remember-forgot">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="remember" name="remember" value="1">
                            <label for="remember">Recordar sesión</label>
                        </div>
                        <a href="<?= url('forgot-password.php') ?>" class="forgot-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fa fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                    
                    <div class="register-link">
                        ¿No tienes cuenta? <a href="<?= url('register.php') ?>">
                            <i class="fa fa-user-plus"></i> Regístrate aquí
                        </a>
                    </div>
                    
                    <div class="back-home">
                        <a href="<?= url('index.php') ?>">
                            <i class="fa fa-home"></i> Volver al inicio
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- JavaScript -->
    <script>
        // Función para mostrar/ocultar contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggle-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Nueva función: toggle con el icono del candado
        function togglePasswordWithLock(inputId) {
            const passwordInput = document.getElementById(inputId);
            const lockIcon = passwordInput.parentElement.querySelector('.fa-lock, .fa-lock-open');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                lockIcon.classList.remove('fa-lock');
                lockIcon.classList.add('fa-lock-open');
            } else {
                passwordInput.type = 'password';
                lockIcon.classList.remove('fa-lock-open');
                lockIcon.classList.add('fa-lock');
            }
        }
        
        // Auto focus en el primer campo vacío
        document.addEventListener('DOMContentLoaded', function() {
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            
            if(!username.value) {
                username.focus();
            } else {
                password.focus();
            }
            
            // Validación del formulario
            const form = document.getElementById('loginForm');
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
            
            // Auto-ocultar alertas después de 5 segundos
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
    <script src="<?= asset('js/form-validation.js') ?>"></script>
</body>
</html>
