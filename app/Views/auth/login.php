<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mas Cargo | Login</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="bg-blobs">
        <div class="blob blob1"></div>
        <div class="blob blob2"></div>
    </div>
    
    <div class="login-container">
        <div class="card" id="loginCard">
            <div class="logo-container" style="text-align: center; margin-bottom: 1rem;">
                <img src="/assets/img/logo.png" alt="Mas Cargo Logo" style="max-width: 140px; display: block; margin: 0 auto;">
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
                    <p style="font-size: 0.8rem; color: #888; margin-top: 1rem; text-align: center;">Introduzca el correo registrado para recibir su clave temporal.</p>
                </div>

                <!-- Paso 2: Código (Oculto inicialmente) -->
                <div id="codeStep" style="display: none;">
                    <div class="form-group">
                        <label for="accessCode">Código de 6 dígitos</label>
                        <input type="text" id="accessCode" placeholder="000000" maxlength="6" style="text-align: center; font-size: 1.5rem; letter-spacing: 10px;">
                    </div>
                    <button type="submit" class="btn">Verificar e Ingresar</button>
                    <button type="button" id="btnBackToEmail" style="background: none; border: none; color: var(--primary-color); cursor: pointer; display: block; margin: 1rem auto; font-size: 0.9rem;">Volver a ingresar correo</button>
                </div>
                
                <div id="errorMsg" class="error-msg"></div>
                <div id="successMsg" style="color: green; font-size: 0.85rem; margin-top: 1rem; text-align: center; display: none;"></div>
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
