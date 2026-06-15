<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(\App\Core\Security::csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
    <title>Mas Cargo | Login</title>
    <meta name="description" content="Iniciar sesión — Sistema de Trip Pass Mas Cargo Airlines">
    <link rel="icon" href="/assets/img/cropped-site_logo-32x32.png" type="image/png">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="login-body">
    <div class="bg-blobs">
        <div class="blob blob1"></div>
        <div class="blob blob2"></div>
    </div>
    
    <div class="login-container">
        <div class="card" id="loginCard">
            <div class="logo-container">
                <img src="/assets/img/logo.png" alt="Mas Cargo Logo">
            </div>
            <p class="subtitle">Acceso por Código de Seguridad</p>
            
            <form id="loginForm">
                <!-- Paso 1: Correo -->
                <div id="emailStep">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" placeholder="usuario@mascargo.com" required>
                    </div>
                    <button type="button" id="btnRequestCode" class="btn">Enviar Código de Acceso</button>
                    <p class="login-hint">Introduzca el correo registrado para recibir su clave temporal.</p>
                </div>

                <!-- Paso 2: Código (Oculto inicialmente) -->
                <div id="codeStep" style="display: none;">
                    <div class="form-group">
                        <label for="accessCode">Código de 6 dígitos</label>
                        <input type="text" id="accessCode" placeholder="000000" maxlength="6" style="text-align: center; font-size: 1.5rem; letter-spacing: 10px;">
                    </div>
                    <button type="submit" class="btn">Verificar e Ingresar</button>
                    <button type="button" id="btnBackToEmail" class="btn-back">Volver a ingresar correo</button>
                </div>
                
                <div id="errorMsg" class="error-msg"></div>
                <div id="successMsg" class="success-msg"></div>
            </form>
        </div>
    </div>
    
    <script src="/assets/js/main.js"></script>
    <script>
        // Inline check for redirection if already logged in
        document.addEventListener('DOMContentLoaded', async () => {
            const res = await fetch('/api/me');
            if (res.ok) {
                window.location.href = '/dashboard';
            }
        });
    </script>
</body>
</html>
