<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error del servidor</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/font-awesome.min.css">
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        .error-content {
            text-align: center;
            color: white;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .error-title {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        .btn-home {
            background: white;
            color: #dc3545;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            color: #dc3545;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="container">
            <div class="error-content">
                <div class="error-code">500</div>
                <h1 class="error-title">Error del servidor</h1>
                <p class="error-message">
                    <?= e($data['message'] ?? 'Ha ocurrido un error interno en el servidor. Por favor, intenta más tarde.') ?>
                </p>
                <a href="/" class="btn-home">
                    <i class="fa fa-home"></i> Volver al inicio
                </a>
            </div>
        </div>
    </div>
</body>
</html>