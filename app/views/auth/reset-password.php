<?php
// Cargar configuración de rutas
require_once __DIR__ . '/../../../config/path.php';

session_start();

// Obtener el token de la URL
$token = $_GET['token'] ?? '';

if(empty($token)) {
    $_SESSION['error'] = 'Token de recuperación inválido o no proporcionado.';
    header('Location: ' . url('forgot-password.php'));
    exit;
}

// Validar el token en la base de datos
try {
    $host = 'localhost';
    $dbname = 'sleppystore';
    $username = 'root';
    $password = '';
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->prepare("
        SELECT id_token, usado, fecha_expiracion 
        FROM password_reset_tokens 
        WHERE token = :token
    ");
    $stmt->execute(['token' => $token]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar si el token existe
    if(!$tokenData) {
        $_SESSION['error'] = 'El enlace de recuperación no es válido.';
        header('Location: ' . url('forgot-password.php'));
        exit;
    }
    
    // Verificar si el token ya fue usado
    if($tokenData['usado'] == 1) {
        $_SESSION['error'] = 'Este enlace de recuperación ya fue utilizado. Por favor solicita uno nuevo.';
        header('Location: ' . url('forgot-password.php'));
        exit;
    }
    
    // Verificar si el token expiró
    if(strtotime($tokenData['fecha_expiracion']) < time()) {
        $_SESSION['error'] = 'Este enlace de recuperación ha expirado. Por favor solicita uno nuevo.';
        header('Location: ' . url('forgot-password.php'));
        exit;
    }
    
} catch(PDOException $e) {
    $_SESSION['error'] = 'Error al verificar el enlace. Intenta nuevamente.';
    header('Location: ' . url('forgot-password.php'));
    exit;
}

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
    <meta name="description" content="Restablecer contraseña - Fashion Store">
    <title>Restablecer Contraseña - Fashion Store</title>
    
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

    <div class="reset-container">
        <div>
            <div class="reset-header">
                <div class="reset-icon">
                    <i class="fa fa-lock"></i>
                </div>
                <h2>Restablecer Contraseña</h2>
                <p>Ingresa tu nueva contraseña para recuperar el acceso a tu cuenta.</p>
            </div>
            
            <div class="password-requirements">
                <h6><i class="fa fa-shield"></i> Requisitos de la contraseña:</h6>
                <ul>
                    <li>Mínimo 6 caracteres</li>
                    <li>Se recomienda usar mayúsculas, minúsculas y números</li>
                    <li>Evita usar información personal obvia</li>
                </ul>
            </div>
            
            <form method="POST" action="<?= url('app/controllers/AuthController.php?action=reset-password') ?>" id="resetForm" novalidate>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                
                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <div class="input-group">
                        <i class="fa fa-lock"></i>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Ingresa tu nueva contraseña"
                               minlength="6"
                               autocomplete="new-password"
                               required
                               autofocus>
                    </div>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-meter">
                            <div class="strength-meter-fill" id="strengthMeterFill"></div>
                        </div>
                        <span id="strengthText"></span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmar Nueva Contraseña</label>
                    <div class="input-group">
                        <i class="fa fa-lock"></i>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirm" 
                               name="password_confirm" 
                               placeholder="Repite tu nueva contraseña"
                               minlength="6"
                               autocomplete="new-password"
                               required>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fa fa-check"></i> Restablecer Contraseña
                </button>
            </form>
            
            <div class="back-links">
                <a href="<?= url('login.php') ?>">
                    <i class="fa fa-arrow-left"></i> Volver al Login
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.3 JS Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <script>
        // Validación de contraseñas en tiempo real
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');
        const strengthMeter = document.getElementById('passwordStrength');
        const strengthMeterFill = document.getElementById('strengthMeterFill');
        const strengthText = document.getElementById('strengthText');
        
        // Calcular fuerza de contraseña
        password.addEventListener('input', function() {
            const value = this.value;
            
            if(value.length === 0) {
                strengthMeter.style.display = 'none';
                return;
            }
            
            strengthMeter.style.display = 'block';
            
            let strength = 0;
            if(value.length >= 6) strength++;
            if(value.length >= 10) strength++;
            if(/[a-z]/.test(value) && /[A-Z]/.test(value)) strength++;
            if(/[0-9]/.test(value)) strength++;
            if(/[^a-zA-Z0-9]/.test(value)) strength++;
            
            strengthMeterFill.className = 'strength-meter-fill';
            
            if(strength <= 2) {
                strengthMeterFill.classList.add('strength-weak');
                strengthText.textContent = 'Contraseña débil';
                strengthText.style.color = '#e74c3c';
            } else if(strength <= 3) {
                strengthMeterFill.classList.add('strength-medium');
                strengthText.textContent = 'Contraseña media';
                strengthText.style.color = '#f39c12';
            } else {
                strengthMeterFill.classList.add('strength-strong');
                strengthText.textContent = 'Contraseña fuerte';
                strengthText.style.color = '#27ae60';
            }
        });
        
        // Validar que las contraseñas coincidan
        function validatePasswordMatch() {
            if(passwordConfirm.value && password.value !== passwordConfirm.value) {
                passwordConfirm.setCustomValidity('Las contraseñas no coinciden');
                passwordConfirm.classList.add('error');
            } else {
                passwordConfirm.setCustomValidity('');
                passwordConfirm.classList.remove('error');
            }
        }
        
        password.addEventListener('input', validatePasswordMatch);
        passwordConfirm.addEventListener('input', validatePasswordMatch);
        
        // Validación del formulario
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            if(password.value !== passwordConfirm.value) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
        });
    </script>
    
    <!-- Validación de Formularios -->
    <script src="<?php echo url('public/assets/js/form-validation.js'); ?>"></script>
</body>
</html>
