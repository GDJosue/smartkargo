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
            <p class="subtitle">Gestión de Pases a Supernumerarios</p>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" placeholder="Tu usuario" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" placeholder="********" required>
                </div>
                
                <button type="submit" class="btn">Ingresar al Sistema</button>
                <div id="errorMsg" class="error-msg">Credenciales incorrectas</div>
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
