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
    
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Cargando historial...</td></tr>';
    
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
                    <td>${new Date(ticket.created_at).toLocaleDateString()}</td>
                    <td><span class="badge">${ticket.flight_num}</span><br><small>${ticket.dep_day}</small></td>
                    <td>${ticket.orig_code} &rarr; ${ticket.dest_code}</td>
                    <td><small>${paxStr}</small></td>
                    <td>
                        <button class="btn btn-sm" onclick='loadTicketToForm(${JSON.stringify(ticket).replace(/'/g, "&apos;")})'>Cargar PDF</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay boletos generados aún.</td></tr>';
        }
    } catch (err) {
        console.error('Error fetching history:', err);
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color: red;">Error al cargar historial.</td></tr>';
    }
}

window.loadTicketToForm = function(ticket) {
    // Fill the inputs
    document.getElementById('f-date1').value = ticket.date_out || '';
    document.getElementById('f-date2').value = ticket.date_return || '';
    document.getElementById('f-dest').value = ticket.main_dest || '';
    
    const pax = JSON.parse(ticket.passengers || '[]');
    document.getElementById('f-passengers').value = Array.isArray(pax) ? pax.join('\\n') : pax;
    
    document.getElementById('f-res').value = ticket.res_code || '';
    document.getElementById('f-day').value = ticket.dep_day || '';
    document.getElementById('f-flight').value = ticket.flight_num || '';
    document.getElementById('f-duration').value = ticket.duration || '';
    document.getElementById('f-cabin').value = ticket.cabin || '';
    document.getElementById('f-status').value = ticket.status || '';
    
    document.getElementById('f-orig-code').value = ticket.orig_code || '';
    document.getElementById('f-orig-city').value = ticket.orig_city || '';
    document.getElementById('f-dest-code').value = ticket.dest_code || '';
    document.getElementById('f-dest-city').value = ticket.dest_city || '';
    
    document.getElementById('f-orig-time').value = ticket.orig_time || '';
    document.getElementById('f-orig-term').value = ticket.orig_term || '';
    document.getElementById('f-dest-time').value = ticket.dest_time || '';
    document.getElementById('f-dest-term').value = ticket.dest_term || '';
    
    document.getElementById('f-aircraft').value = ticket.aircraft || '';
    document.getElementById('f-miles').value = ticket.miles || '';
    
    // Trigger input events to update the live preview
    const inputs = [
        'f-date1', 'f-date2', 'f-dest', 'f-res', 'f-day', 'f-flight', 'f-duration', 
        'f-cabin', 'f-status', 'f-orig-code', 'f-orig-city', 'f-dest-code', 
        'f-dest-city', 'f-orig-time', 'f-orig-term', 'f-dest-time', 'f-dest-term', 
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
    
    // Keep the old ticket ID
    document.getElementById('prev-ticket-id').textContent = ticket.ticket_id;
    
    // Warning or status
    const resultDiv = document.getElementById('ticketResult');
    resultDiv.innerHTML = '<p style="color:var(--text-primary)">Boleto cargado del historial. Puedes volver a descargarlo.</p>';
    resultDiv.style.display = 'block';
    
    // We scroll up to see it
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
