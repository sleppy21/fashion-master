<?php
// Cargar configuración de rutas
require_once __DIR__ . '/../../../config/path.php';

session_start();

// Si ya está logueado, redirigir al dashboard
if(isset($_SESSION['user_id'])) {
    header('Location: ' . url('index.php'));
    exit;
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['error'], $_SESSION['success'], $_SESSION['form_data']);

// Función helper para escapar HTML
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
    <meta name="description" content="Registrate en Fashion Store - Tu tienda de moda online">
    <title>Registrarse - Fashion Store</title>
    
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

    <div class="register-container">
        <div class="d-flex flex-column flex-md-row">
            <!-- Left Side - Benefits -->
            <div class="register-left">
                <div class="brand-logo">
                    <i class="fa fa-shopping-bag"></i>
                    <span>Fashion Store</span>
                </div>
                <div class="welcome-text text-center text-md-start">
                    <h3>¡Únete a nosotros!</h3>
                    <p>Crea tu cuenta y disfruta de beneficios exclusivos.</p>
                </div>
                
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fa fa-percent"></i>
                        <span>Descuentos exclusivos para miembros</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fa fa-truck"></i>
                        <span>Envío gratis en tu primera compra</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fa fa-star"></i>
                        <span>Programa de puntos y recompensas</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fa fa-heart"></i>
                        <span>Guarda tus productos favoritos</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fa fa-bell"></i>
                        <span>Notificaciones de nuevos productos</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <div class="register-right">
                <div class="register-header">
                    <h2>Crear Cuenta</h2>
                    <p>Completa tus datos para registrarte</p>
                </div>
                
                <!-- Register Form -->
                <form method="POST" action="<?= url('app/controllers/AuthController.php?action=register') ?>" id="registerForm" novalidate>
                    <!-- Nombres en una fila -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="required">*</span></label>
                            <div class="input-group">
                                <i class="fa fa-user"></i>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?= e($form_data['nombre'] ?? '') ?>"
                                       placeholder="Tu nombre"
                                       pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}"
                                       title="Solo letras (mínimo 2 caracteres)"
                                       autocomplete="given-name"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="apellido">Apellido <span class="required">*</span></label>
                            <div class="input-group">
                                <i class="fa fa-user"></i>
                                <input type="text" 
                                       class="form-control" 
                                       id="apellido" 
                                       name="apellido" 
                                       value="<?= e($form_data['apellido'] ?? '') ?>"
                                       placeholder="Tu apellido"
                                       pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}"
                                       title="Solo letras (mínimo 2 caracteres)"
                                       autocomplete="family-name"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email y Username -->
                    <div class="form-group">
                        <label for="email">Correo Electrónico <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fa fa-envelope"></i>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= e($form_data['email'] ?? '') ?>"
                                   placeholder="tu@email.com"
                                   pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                                   title="Ingresa un email válido (ejemplo: usuario@gmail.com)"
                                   autocomplete="email"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Nombre de Usuario <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fa fa-at"></i>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   value="<?= e($form_data['username'] ?? '') ?>"
                                   placeholder="Elige un nombre de usuario único"
                                   pattern="[a-zA-Z0-9_]{3,20}"
                                   title="Solo letras, números y guión bajo (3-20 caracteres)"
                                   autocomplete="username"
                                   required>
                        </div>
                        <small class="form-text">Solo letras, números y guión bajo (3-20 caracteres)</small>
                    </div>
                    
                    <!-- Teléfono y Fecha de Nacimiento en una fila -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <div class="input-group">
                                <i class="fa fa-phone"></i>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?= e($form_data['telefono'] ?? '') ?>"
                                       placeholder="9XXXXXXXX"
                                       pattern="9\d{8}"
                                       title="Debe empezar con 9 y tener 9 dígitos"
                                       maxlength="9"
                                       autocomplete="tel">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <div class="input-group">
                                <i class="fa fa-calendar"></i>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_nacimiento" 
                                       name="fecha_nacimiento" 
                                       value="<?= e($form_data['fecha_nacimiento'] ?? '') ?>"
                                       max="<?= date('Y-m-d', strtotime('-13 years')) ?>"
                                       autocomplete="bday">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Género -->
                    <div class="form-group">
                        <label for="genero">Género</label>
                        <div class="input-group">
                            <i class="fa fa-venus-mars"></i>
                            <select class="form-control" id="genero" name="genero" autocomplete="sex">
                                <option value="Otro" <?= ($form_data['genero'] ?? '') == 'Otro' ? 'selected' : '' ?>>Prefiero no especificar</option>
                                <option value="M" <?= ($form_data['genero'] ?? '') == 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= ($form_data['genero'] ?? '') == 'F' ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Contraseñas en una fila -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Contraseña <span class="required">*</span></label>
                            <div class="input-group input-password">
                                <i class="fa fa-lock" onclick="togglePasswordWithLock('password')" title="Mostrar/Ocultar contraseña"></i>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Mínimo 6 caracteres"
                                       minlength="6"
                                       autocomplete="new-password"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirm">Confirmar Contraseña <span class="required">*</span></label>
                            <div class="input-group input-password">
                                <i class="fa fa-lock" onclick="togglePasswordWithLock('password_confirm')" title="Mostrar/Ocultar contraseña"></i>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       placeholder="Repite tu contraseña"
                                       minlength="6"
                                       autocomplete="new-password"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Strength Meter -->
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-meter">
                            <div class="strength-meter-fill" id="strengthMeterFill"></div>
                        </div>
                        <span id="strengthText"></span>
                    </div>
                    
                    <!-- Términos y Condiciones -->
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="terms" name="terms" value="1" required>
                        <label for="terms">
                            Acepto los <a href="<?= url('terms.php') ?>" target="_blank" rel="noopener">Términos y Condiciones</a> 
                            y la <a href="<?= url('privacy.php') ?>" target="_blank" rel="noopener">Política de Privacidad</a>
                        </label>
                    </div>
                    
                    <!-- Botón de Registro -->
                    <button type="submit" class="btn-register" id="btnRegister">
                        <i class="fa fa-user-plus"></i> Crear Cuenta
                    </button>
                    
                    <div class="login-link">
                        ¿Ya tienes cuenta? <a href="<?= url('login.php') ?>">
                            <i class="fa fa-sign-in-alt"></i> Inicia sesión aquí
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
        // Validación de contraseñas en tiempo real
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');
        const strengthMeter = document.getElementById('passwordStrength');
        const strengthMeterFill = document.getElementById('strengthMeterFill');
        const strengthText = document.getElementById('strengthText');
        const btnRegister = document.getElementById('btnRegister');
        
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
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if(password.value !== passwordConfirm.value) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if(!document.getElementById('terms').checked) {
                e.preventDefault();
                alert('Debes aceptar los términos y condiciones');
                return false;
            }
        });
        
        // Auto focus
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nombre').focus();
        });
        
        // Función para mostrar/ocultar contraseña
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
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
