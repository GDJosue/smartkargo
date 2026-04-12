// main.js - Client-Side Logic for Masair Ticketing System

// Login Form Handling
if (document.getElementById('loginForm')) {
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const usernameInput = document.getElementById('username').value;
        const passwordInput = document.getElementById('password').value;
        const errorMsg = document.getElementById('errorMsg');
        
        errorMsg.style.display = 'none';
        
        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: usernameInput,
                    password: passwordInput
                })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                // Redirect to dashboard on success
                window.location.href = '/dashboard';
            } else {
                // Show error message
                errorMsg.style.display = 'block';
                errorMsg.textContent = data.message || 'Error de autenticación';
            }
        } catch (err) {
            console.error('Login error:', err);
            errorMsg.style.display = 'block';
            errorMsg.textContent = 'Error al conectar con el servidor';
        }
    });
}

// shared global logic for Masair
console.log('Masair Ticketing System Loaded');
