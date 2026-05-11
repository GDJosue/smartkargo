// main.js - Client-Side Logic for Masair Ticketing System

// Login Form Handling (OTP Two-Step)
if (document.getElementById('loginForm')) {
    const loginForm = document.getElementById('loginForm');
    const emailStep = document.getElementById('emailStep');
    const codeStep = document.getElementById('codeStep');
    const btnRequestCode = document.getElementById('btnRequestCode');
    const btnBackToEmail = document.getElementById('btnBackToEmail');
    const errorMsg = document.getElementById('errorMsg');
    const successMsg = document.getElementById('successMsg');

    // Step 1: Request Code
    btnRequestCode.addEventListener('click', async () => {
        const email = document.getElementById('email').value;
        if (!email) {
            showError('Por favor ingresa tu correo');
            return;
        }

        btnRequestCode.disabled = true;
        btnRequestCode.textContent = 'Enviando...';
        hideMessages();

        try {
            const response = await fetch('/api/login/request', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            });
            const data = await response.json();

            if (data.success) {
                emailStep.style.display = 'none';
                codeStep.style.display = 'block';
                showSuccess(data.message);
            } else {
                showError(data.message);
            }
        } catch (err) {
            showError('Error al conectar con el servidor');
        } finally {
            btnRequestCode.disabled = false;
            btnRequestCode.textContent = 'Enviar Código de Acceso';
        }
    });

    // Step 2: Verify Code
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const code = document.getElementById('accessCode').value;

        if (!code) return;

        hideMessages();
        const btnSubmit = loginForm.querySelector('button[type="submit"]');
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Verificando...';

        try {
            const response = await fetch('/api/login/verify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, code })
            });
            const data = await response.json();

            if (data.success) {
                window.location.href = '/dashboard';
            } else {
                showError(data.message);
            }
        } catch (err) {
            showError('Error de verificación');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Verificar e Ingresar';
        }
    });

    // Navigation back
    btnBackToEmail.addEventListener('click', () => {
        codeStep.style.display = 'none';
        emailStep.style.display = 'block';
        hideMessages();
    });

    function showError(msg) {
        errorMsg.textContent = msg;
        errorMsg.style.display = 'block';
    }

    function showSuccess(msg) {
        successMsg.textContent = msg;
        successMsg.style.display = 'block';
    }

    function hideMessages() {
        errorMsg.style.display = 'none';
        successMsg.style.display = 'none';
    }
}

// shared global logic for Masair
console.log('Masair Ticketing System Loaded');

// Dashboard Tabs & History Logic
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active classes
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active to current
            btn.classList.add('active');
            document.getElementById(btn.getAttribute('data-target')).classList.add('active');
            
            if (btn.id === 'loadHistoryBtn') {
                loadHistory();
            }
        });
    });
});

async function loadHistory() {
    const tbody = document.getElementById('historyTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Cargando historial...</td></tr>';
    
    try {
        const response = await fetch('/api/tickets');
        const data = await response.json();
        
        if (data.success && data.tickets.length > 0) {
            tbody.innerHTML = '';
            data.tickets.forEach(ticket => {
                const tr = document.createElement('tr');
                const pax = JSON.parse(ticket.passengers || '[]');
                const paxStr = Array.isArray(pax) ? pax.join(', ') : ticket.passengers;
                
                tr.innerHTML = `
                    <td><strong>${ticket.ticket_id}</strong></td>
                    <td>${ticket.created_at_cdmx || new Date(ticket.created_at).toLocaleDateString()}</td>
                    <td>${ticket.created_by_name || 'Desconocido'}</td>
                    <td><span class="badge">${ticket.transportadora}</span><br><small>${ticket.area}</small></td>
                    <td>${ticket.orig_code} &rarr; ${ticket.dest_code}</td>
                    <td><small>${paxStr}</small></td>
                    <td>
                        <button class="btn btn-sm" onclick='loadTicketToForm(${JSON.stringify(ticket).replace(/'/g, "&apos;")})'>Cargar PDF</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No hay boletos generados aún.</td></tr>';
        }
    } catch (err) {
        console.error('Error fetching history:', err);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color: red;">Error al cargar historial.</td></tr>';
    }
}

window.loadTicketToForm = function(ticket) {
    // Fill the inputs
    document.getElementById('f-date1').value = ticket.date_out || '';
    document.getElementById('f-date2').value = ticket.date_return || '';
    document.getElementById('f-approved-by').value = ticket.approved_by || '';
    
    const pax = JSON.parse(ticket.passengers || '[]');
    document.getElementById('f-passengers').value = Array.isArray(pax) ? pax.join('\\n') : pax;
    
    document.getElementById('f-guide').value = ticket.guide_code || '';
    document.getElementById('f-area').value = ticket.area || '';
    document.getElementById('f-transport').value = ticket.transportadora || '';
    document.getElementById('f-passenger-type').value = ticket.passenger_type || '';
    document.getElementById('f-priority').value = ticket.priority || '';
    document.getElementById('f-status').value = ticket.status || '';
    
    document.getElementById('f-orig-code').value = ticket.orig_code || '';
    document.getElementById('f-orig-city').value = ticket.orig_city || '';
    document.getElementById('f-dest-code').value = ticket.dest_code || '';
    document.getElementById('f-dest-city').value = ticket.dest_city || '';
    
    document.getElementById('f-time').value = ticket.time || '';
    document.getElementById('f-flight').value = ticket.flight || '';
    
    document.getElementById('f-aircraft').value = ticket.aircraft || '';
    document.getElementById('f-miles').value = ticket.miles || '';
    
    // Trigger input events to update the live preview
    const inputs = [
        'f-date1', 'f-date2', 'f-approved-by', 'f-guide', 'f-area', 'f-transport', 'f-passenger-type', 'f-priority', 
        'f-status', 'f-orig-code', 'f-orig-city', 'f-dest-code', 
        'f-dest-city', 'f-time', 'f-flight', 
        'f-aircraft', 'f-miles', 'f-passengers'
    ];
    
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.dispatchEvent(new Event('input'));
        }
    });

    // Switch back to generate tab
    document.querySelector('[data-target="tab-generate"]').click();
    
    // Keep the old ticket ID and Audit Fields
    document.getElementById('prev-ticket-id').textContent = ticket.ticket_id;
    document.getElementById('prev-created-by').textContent = ticket.created_by_name || 'Desconocido';
    document.getElementById('prev-created-at').textContent = ticket.created_at_cdmx || ticket.created_at || 'Sin fecha';
    
    // Warning or status
    const resultDiv = document.getElementById('ticketResult');
    resultDiv.innerHTML = '<p style="color:var(--text-primary)">Boleto cargado del historial. Puedes volver a descargarlo.</p>';
    resultDiv.style.display = 'block';
    
    // We scroll up to see it
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// USER MANAGEMENT LOGIC
document.addEventListener('DOMContentLoaded', () => {
    const createUserForm = document.getElementById('createUserForm');
    if (createUserForm) {
        createUserForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = createUserForm.querySelector('button');
            const msg = document.getElementById('createUserMsg');
            
            const data = {
                full_name: document.getElementById('u-name').value,
                username: document.getElementById('u-username').value,
                email: document.getElementById('u-email').value
            };

            btn.disabled = true;
            msg.textContent = 'Creando...';
            msg.style.color = '#333';

            try {
                const response = await fetch('/api/users', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                
                if (result.success) {
                    msg.style.color = 'green';
                    msg.textContent = result.message;
                    createUserForm.reset();
                    loadUsers();
                } else {
                    msg.style.color = 'red';
                    msg.textContent = result.message;
                }
            } catch (err) {
                msg.style.color = 'red';
                msg.textContent = 'Error de conexión';
            } finally {
                btn.disabled = false;
            }
        });
    }

    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.id === 'loadUsersBtn') {
                loadUsers();
            }
        });
    });
});

async function loadUsers() {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Cargando usuarios...</td></tr>';

    try {
        const response = await fetch('/api/users');
        const data = await response.json();

        if (data.success && data.users.length > 0) {
            tbody.innerHTML = '';
            data.users.forEach(u => {
                const tr = document.createElement('tr');
                const role = u.is_admin ? '<span style="color:var(--primary-color); font-weight:bold;">Admin</span>' : 'Usuario';
                
                tr.innerHTML = `
                    <td>${u.id}</td>
                    <td>${u.full_name}</td>
                    <td>${u.username}<br><small>${u.email}</small></td>
                    <td>${role}</td>
                    <td>
                        <button class="btn btn-sm" style="background:#005c42;" onclick="toggleAdmin(${u.id}, ${u.is_admin})">${u.is_admin ? 'Quitar Admin' : 'Hacer Admin'}</button>
                        <button class="btn btn-sm" style="background:#cc0000;" onclick="deleteUser(${u.id})">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay usuarios.</td></tr>';
        }
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Error al cargar.</td></tr>';
    }
}

window.deleteUser = async function(id) {
    if (!confirm('¿Estás seguro de eliminar este usuario?')) return;
    try {
        const res = await fetch('/api/users/' + id, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            loadUsers();
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Error de red');
    }
};

window.toggleAdmin = async function(id, currentStatus) {
    if (!confirm(currentStatus ? '¿Quitar permisos de admin?' : '¿Otorgar permisos de admin?')) return;
    try {
        const res = await fetch('/api/users/' + id + '/admin', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ is_admin: !currentStatus })
        });
        const data = await res.json();
        if (data.success) {
            loadUsers();
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Error de red');
    }
};
