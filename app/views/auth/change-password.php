<?php
// Cargar configuración de rutas
require_once __DIR__ . '/../../../config/path.php';

session_start();

// Verificar que el usuario esté autenticado
if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Debes iniciar sesión para cambiar tu contraseña.';
    header('Location: ' . url('login.php'));
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
    <meta name="description" content="Cambiar contraseña - Fashion Store">
    <title>Cambiar Contraseña - Fashion Store</title>
    
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

    <div class="change-password-container">
        <div>
            <div class="change-password-header">
                <div class="change-password-icon">
                    <i class="fa fa-shield"></i>
                </div>
                <h2>Cambiar Contraseña</h2>
                <p>Actualiza tu contraseña regularmente para mantener tu cuenta segura.</p>
            </div>
            
            <div class="security-tips">
                <h6><i class="fa fa-lightbulb-o"></i> Consejos de seguridad:</h6>
                <ul>
                    <li>Usa al menos 8 caracteres</li>
                    <li>Combina letras mayúsculas, minúsculas, números y símbolos</li>
                    <li>No reutilices contraseñas de otras cuentas</li>
                    <li>Evita información personal obvia</li>
                </ul>
            </div>
            
            <form method="POST" action="<?= url('app/controllers/AuthController.php?action=change-password') ?>" id="changePasswordForm" novalidate>
                <div class="form-group">
                    <label for="current_password">Contraseña Actual</label>
                    <div class="input-group">
                        <i class="fa fa-lock"></i>
                        <input type="password" 
                               class="form-control" 
                               id="current_password" 
                               name="current_password" 
                               placeholder="Ingresa tu contraseña actual"
                               autocomplete="current-password"
                               required
                               autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <div class="input-group">
                        <i class="fa fa-key"></i>
                        <input type="password" 
                               class="form-control" 
                               id="new_password" 
                               name="new_password" 
                               placeholder="Ingresa tu nueva contraseña"
                               minlength="6"
                               autocomplete="new-password"
                               required>
                    </div>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-meter">
                            <div class="strength-meter-fill" id="strengthMeterFill"></div>
                        </div>
                        <span id="strengthText"></span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nueva Contraseña</label>
                    <div class="input-group">
                        <i class="fa fa-key"></i>
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Repite tu nueva contraseña"
                               minlength="6"
                               autocomplete="new-password"
                               required>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fa fa-check"></i> Cambiar Contraseña
                </button>
            </form>
            
            <div class="back-links">
                <a href="<?= url('admin.php') ?>">
                    <i class="fa fa-arrow-left"></i> Volver al Panel
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
        // Validación de contraseñas en tiempo real
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const strengthMeter = document.getElementById('passwordStrength');
        const strengthMeterFill = document.getElementById('strengthMeterFill');
        const strengthText = document.getElementById('strengthText');
        
        // Calcular fuerza de contraseña
        newPassword.addEventListener('input', function() {
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
            if(confirmPassword.value && newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
                confirmPassword.classList.add('error');
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.classList.remove('error');
            }
        }
        
        newPassword.addEventListener('input', validatePasswordMatch);
        confirmPassword.addEventListener('input', validatePasswordMatch);
        
        // Validación del formulario
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            if(newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if(newPassword.value === document.getElementById('current_password').value) {
                e.preventDefault();
                alert('La nueva contraseña debe ser diferente a la actual');
                return false;
            }
        });
    </script>
    
    <!-- Validación de Formularios -->
    <script src="<?php echo url('public/assets/js/form-validation.js'); ?>"></script>
</body>
</html>
