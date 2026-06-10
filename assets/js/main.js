// main.js - Client-Side Logic for Masair Ticketing System

(function hardenSameOriginFetch() {
    const rawFetch = window.fetch.bind(window);
    window.fetch = (resource, options = {}) => {
        const method = String(options.method || 'GET').toUpperCase();
        const url = typeof resource === 'string' ? new URL(resource, window.location.origin) : new URL(resource.url, window.location.origin);
        if (url.origin === window.location.origin && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const headers = new Headers(options.headers || {});
            if (token && !headers.has('X-CSRF-Token')) {
                headers.set('X-CSRF-Token', token);
            }
            options = { ...options, headers };
        }
        return rawFetch(resource, options);
    };
})();

function clearChildren(el) {
    while (el.firstChild) el.removeChild(el.firstChild);
}

function appendTextCell(row, value, options = {}) {
    const cell = document.createElement('td');
    if (options.small) {
        const small = document.createElement('small');
        small.textContent = value ?? '';
        cell.appendChild(small);
    } else if (options.strong) {
        const strong = document.createElement('strong');
        strong.textContent = value ?? '';
        cell.appendChild(strong);
    } else {
        cell.textContent = value ?? '';
    }
    row.appendChild(cell);
    return cell;
}

function appendTableMessage(tbody, colspan, message, color = '') {
    clearChildren(tbody);
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = colspan;
    td.style.textAlign = 'center';
    if (color) td.style.color = color;
    td.textContent = message;
    tr.appendChild(td);
    tbody.appendChild(tr);
}

function safePassengers(passengers) {
    try {
        const parsed = JSON.parse(passengers || '[]');
        return Array.isArray(parsed) ? parsed.join(', ') : String(passengers || '');
    } catch (e) {
        return String(passengers || '');
    }
}

function makeActionButton(label, classes, handler) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = classes;
    button.textContent = label;
    button.addEventListener('click', handler);
    return button;
}

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
console.log('Masair Trip Pass System Loaded');

// Global state for history filtering and charts
window.allTickets = [];
window.carrierChart = null;
window.paxTypeChart = null;

// Dashboard Tabs & History Logic
document.addEventListener('DOMContentLoaded', () => {
    // Call loadDashboard initially
    loadDashboard();

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
            } else if (btn.id === 'tabDashboardBtn') {
                loadDashboard();
            }
        });
    });

    // Form presets listeners
    const presetBtns = document.querySelectorAll('.btn-preset');
    presetBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            presetBtns.forEach(b => b.style.borderColor = 'var(--border)');
            btn.style.borderColor = 'var(--primary)';

            const carrier = btn.getAttribute('data-carrier');
            const flight = btn.getAttribute('data-flight');
            const from = btn.getAttribute('data-from');
            const to = btn.getAttribute('data-to');
            const time = btn.getAttribute('data-time');

            document.getElementById('f-carrier1').value = carrier;
            document.getElementById('f-flight1').value = flight;
            document.getElementById('f-from1').value = from;
            document.getElementById('f-to1').value = to;
            document.getElementById('f-time1').value = time;

            // Trigger events to update preview
            ['f-carrier1', 'f-flight1', 'f-from1', 'f-to1', 'f-time1'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.dispatchEvent(new Event('input'));
            });
        });
    });

    // Tramo add/remove buttons logic
    const btnAddTramo2 = document.getElementById('btnAddTramo2');
    const btnAddTramo3 = document.getElementById('btnAddTramo3');
    const btnRemoveTramo2 = document.getElementById('btnRemoveTramo2');
    const btnRemoveTramo3 = document.getElementById('btnRemoveTramo3');

    if (btnAddTramo2) {
        btnAddTramo2.addEventListener('click', () => {
            const wrapper = document.getElementById('flt2-wrapper');
            if (wrapper) wrapper.style.maxHeight = '500px';
            btnAddTramo2.style.display = 'none';
            if (btnAddTramo3) btnAddTramo3.style.display = 'inline-block';
        });
    }

    if (btnAddTramo3) {
        btnAddTramo3.addEventListener('click', () => {
            const wrapper = document.getElementById('flt3-wrapper');
            if (wrapper) wrapper.style.maxHeight = '500px';
            btnAddTramo3.style.display = 'none';
        });
    }

    if (btnRemoveTramo2) {
        btnRemoveTramo2.addEventListener('click', () => {
            const wrapper2 = document.getElementById('flt2-wrapper');
            const wrapper3 = document.getElementById('flt3-wrapper');
            // Remove tramo 3 first if open
            if (wrapper3 && wrapper3.style.maxHeight !== '0px' && wrapper3.style.maxHeight !== '0') {
                wrapper3.style.maxHeight = '0';
                ['f-carrier3', 'f-flight3', 'f-from3', 'f-to3', 'f-date3', 'f-time3'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) { el.value = ''; el.dispatchEvent(new Event('input')); }
                });
            }
            if (wrapper2) wrapper2.style.maxHeight = '0';
            ['f-carrier2', 'f-flight2', 'f-from2', 'f-to2', 'f-date2', 'f-time2'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.value = ''; el.dispatchEvent(new Event('input')); }
            });
            if (btnAddTramo2) btnAddTramo2.style.display = 'inline-block';
            if (btnAddTramo3) btnAddTramo3.style.display = 'none';
        });
    }

    if (btnRemoveTramo3) {
        btnRemoveTramo3.addEventListener('click', () => {
            const wrapper = document.getElementById('flt3-wrapper');
            if (wrapper) wrapper.style.maxHeight = '0';
            ['f-carrier3', 'f-flight3', 'f-from3', 'f-to3', 'f-date3', 'f-time3'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.value = ''; el.dispatchEvent(new Event('input')); }
            });
            if (btnAddTramo3) btnAddTramo3.style.display = 'inline-block';
        });
    }



    // Auto-uppercase logic for carrier, flight, from, to
    const upperFields = ['f-carrier1', 'f-flight1', 'f-from1', 'f-to1', 'f-carrier2', 'f-flight2', 'f-from2', 'f-to2', 'f-carrier3', 'f-flight3', 'f-from3', 'f-to3'];
    upperFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                el.value = el.value.toUpperCase();
            });
        }
    });

    // Load localStorage defaults for operator fields
    if (localStorage.getItem('masair_last_req_by')) {
        const val = localStorage.getItem('masair_last_req_by');
        const input = document.getElementById('f-req-by');
        if (input) input.value = val;
        const prev = document.getElementById('prev-req-by');
        if (prev) prev.textContent = val.toUpperCase();
    }
    if (localStorage.getItem('masair_last_area')) {
        const val = localStorage.getItem('masair_last_area');
        const input = document.getElementById('f-area');
        if (input) input.value = val;
        const prev = document.getElementById('prev-dept');
        if (prev) prev.textContent = val.toUpperCase();
    }
    if (localStorage.getItem('masair_last_approved_by')) {
        const val = localStorage.getItem('masair_last_approved_by');
        const input = document.getElementById('f-approved-by');
        if (input) input.value = val;
        const prev = document.getElementById('prev-approved');
        if (prev) prev.textContent = val.toUpperCase();
    }

    // Filters event listeners
    ['filter-pax', 'filter-flight', 'filter-date'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', applyFilters);
    });
    ['filter-carrier', 'filter-type'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', applyFilters);
    });
    const clearBtn = document.getElementById('btnClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            ['filter-pax', 'filter-flight', 'filter-date'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            ['filter-carrier', 'filter-type'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            applyFilters();
        });
    }

    // Live system clock tick
    setInterval(() => {
        const clockEl = document.getElementById('kpi-clock');
        if (clockEl) {
            const timeStr = new Date().toLocaleTimeString('es-MX', { timeZone: 'America/Mexico_City', hour12: false });
            clockEl.textContent = 'CDMX ' + timeStr;
        }
    }, 1000);
});

async function loadDashboard() {
    try {
        const response = await fetch('/api/tickets');
        const data = await response.json();
        if (!data.success) return;
        
        const tickets = data.tickets || [];
        window.allTickets = tickets;

        // Compute KPIs
        document.getElementById('kpi-total-tickets').textContent = tickets.length;
        
        // Today's tickets count (CDMX time YYYY-MM-DD)
        const cdmxTodayStr = new Date().toLocaleDateString('en-CA', { timeZone: 'America/Mexico_City' }); // returns YYYY-MM-DD
        const todayCount = tickets.filter(t => {
            const dateStr = t.created_at_cdmx || t.created_at || '';
            return dateStr.startsWith(cdmxTodayStr);
        }).length;
        document.getElementById('kpi-today-tickets').textContent = todayCount;

        // Active operators (distinct created_by_name)
        const operators = [...new Set(tickets.map(t => t.created_by_name).filter(Boolean))];
        try {
            const userRes = await fetch('/api/me');
            const userData = await userRes.json();
            if (userData.isAdmin) {
                const usersRes = await fetch('/api/users');
                const usersData = await usersRes.json();
                if (usersData.success) {
                    document.getElementById('kpi-active-users').textContent = usersData.users.length;
                } else {
                    document.getElementById('kpi-active-users').textContent = Math.max(1, operators.length);
                }
            } else {
                document.getElementById('kpi-active-users').textContent = Math.max(1, operators.length);
            }
        } catch (e) {
            document.getElementById('kpi-active-users').textContent = Math.max(1, operators.length);
        }

        // Render Recent Activity (Last 5 tickets)
        const recentBody = document.getElementById('recentActivityBody');
        if (recentBody) {
            const recent = tickets.slice(0, 5);
            if (recent.length > 0) {
                clearChildren(recentBody);
                recent.forEach(ticket => {
                    const tr = document.createElement('tr');
                    const paxStr = safePassengers(ticket.passengers);

                    appendTextCell(tr, ticket.ticket_id, { strong: true });
                    appendTextCell(tr, paxStr, { small: true });
                    const flightCell = document.createElement('td');
                    const badge = document.createElement('span');
                    badge.className = 'badge';
                    badge.textContent = `${ticket.transportadora || ''} ${ticket.flight || ''}`.trim();
                    flightCell.appendChild(badge);
                    tr.appendChild(flightCell);
                    appendTextCell(tr, `${ticket.orig_code || ''} → ${ticket.dest_code || ''}`);

                    const actionCell = document.createElement('td');
                    const actionWrap = document.createElement('div');
                    actionWrap.style.display = 'flex';
                    actionWrap.style.gap = '5px';
                    const loadBtn = makeActionButton('Cargar', 'btn btn-sm', () => loadTicketToForm(ticket));
                    loadBtn.setAttribute('data-i18n', 'btn_load');
                    actionWrap.appendChild(loadBtn);
                    actionWrap.appendChild(makeActionButton('PDF', 'btn btn-sm btn-secondary', () => downloadTicketPDF(ticket)));
                    actionCell.appendChild(actionWrap);
                    tr.appendChild(actionCell);
                    recentBody.appendChild(tr);
                });
            } else {
                appendTableMessage(recentBody, 5, 'Sin actividad reciente.');
            }
        }

        // Render Charts
        renderCharts(tickets);

    } catch (err) {
        console.error('Error loading dashboard:', err);
    }
}

function renderCharts(tickets) {
    // Carrier distribution
    const carriersMap = {};
    tickets.forEach(t => {
        const c = t.transportadora || 'Otros';
        carriersMap[c] = (carriersMap[c] || 0) + 1;
    });
    const carrierLabels = Object.keys(carriersMap);
    const carrierData = Object.values(carriersMap);

    // Pax Type distribution
    const paxMap = {};
    tickets.forEach(t => {
        const type = t.passenger_type || 'Others';
        paxMap[type] = (paxMap[type] || 0) + 1;
    });
    const paxLabels = Object.keys(paxMap);
    const paxData = Object.values(paxMap);

    // Render Carrier Chart
    const ctxCarrier = document.getElementById('chartCarrier');
    if (ctxCarrier) {
        if (window.carrierChart) {
            window.carrierChart.destroy();
        }
        window.carrierChart = new Chart(ctxCarrier, {
            type: 'doughnut',
            data: {
                labels: carrierLabels,
                datasets: [{
                    data: carrierData,
                    backgroundColor: ['#005c42', '#8cc63f', '#00422f', '#cbd5e0', '#718096'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { family: 'Inter', size: 10 } }
                    }
                }
            }
        });
    }

    // Render Passenger Type Chart
    const ctxPax = document.getElementById('chartPaxType');
    if (ctxPax) {
        if (window.paxTypeChart) {
            window.paxTypeChart.destroy();
        }
        window.paxTypeChart = new Chart(ctxPax, {
            type: 'bar',
            data: {
                labels: paxLabels,
                datasets: [{
                    label: 'Trip Pass',
                    data: paxData,
                    backgroundColor: '#8cc63f',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 9 } } },
                    y: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 9 } } }
                }
            }
        });
    }
}

async function loadHistory() {
    const tbody = document.getElementById('historyTableBody');
    if (!tbody) return;
    
    appendTableMessage(tbody, 7, 'Cargando historial...');
    
    try {
        const response = await fetch('/api/tickets');
        const data = await response.json();
        
        if (data.success && data.tickets.length > 0) {
            window.allTickets = data.tickets;

            // Populate carriers dropdown in filters if empty
            const carriers = [...new Set(window.allTickets.map(t => t.transportadora).filter(Boolean))];
            const filterCarrier = document.getElementById('filter-carrier');
            if (filterCarrier && filterCarrier.options.length <= 1) {
                carriers.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c;
                    opt.textContent = c;
                    filterCarrier.appendChild(opt);
                });
            }

            applyFilters();
        } else {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No hay trip pass generados aún.</td></tr>';
        }
    } catch (err) {
        console.error('Error fetching history:', err);
        appendTableMessage(tbody, 7, 'Error al cargar historial.', 'red');
    }
}

function applyFilters() {
    const tbody = document.getElementById('historyTableBody');
    if (!tbody) return;

    const filterPax = document.getElementById('filter-pax').value.toLowerCase().trim();
    const filterFlight = document.getElementById('filter-flight').value.toLowerCase().trim();
    const filterCarrier = document.getElementById('filter-carrier').value;
    const filterType = document.getElementById('filter-type').value;
    const filterDate = document.getElementById('filter-date').value.toLowerCase().trim();

    const filtered = window.allTickets.filter(ticket => {
        // Passenger filter
        const pax = JSON.parse(ticket.passengers || '[]');
        const paxStr = Array.isArray(pax) ? pax.join(', ').toLowerCase() : (ticket.passengers || '').toLowerCase();
        if (filterPax && !paxStr.includes(filterPax)) return false;

        // Flight filter
        const flight1 = (ticket.flight || '').toLowerCase();
        const flight2 = (ticket.flight2 || '').toLowerCase();
        if (filterFlight && !flight1.includes(filterFlight) && !flight2.includes(filterFlight)) return false;

        // Carrier filter
        if (filterCarrier && ticket.transportadora !== filterCarrier && ticket.carrier2 !== filterCarrier) return false;

        // Passenger Type filter
        if (filterType && ticket.passenger_type !== filterType) return false;

        // Date filter
        const dateOut = (ticket.date_out || '').toLowerCase();
        const dateReturn = (ticket.date_return || '').toLowerCase();
        const createdCdmx = (ticket.created_at_cdmx || '').toLowerCase();
        if (filterDate && !dateOut.includes(filterDate) && !dateReturn.includes(filterDate) && !createdCdmx.includes(filterDate)) return false;

        return true;
    });

    // Populate table
    if (filtered.length > 0) {
        clearChildren(tbody);
        filtered.forEach(ticket => {
            const tr = document.createElement('tr');
            const paxStr = safePassengers(ticket.passengers);

            appendTextCell(tr, ticket.ticket_id, { strong: true });
            appendTextCell(tr, ticket.created_at_cdmx || new Date(ticket.created_at).toLocaleDateString());
            appendTextCell(tr, ticket.created_by_name || 'Desconocido');

            const flightCell = document.createElement('td');
            const flightBadge = document.createElement('span');
            flightBadge.className = 'badge';
            flightBadge.textContent = `${ticket.transportadora || ''} ${ticket.flight || ''}`.trim();
            flightCell.appendChild(flightBadge);
            if (ticket.carrier2 && ticket.flight2) {
                flightCell.appendChild(document.createElement('br'));
                const flightBadge2 = document.createElement('span');
                flightBadge2.className = 'badge';
                flightBadge2.style.marginTop = '2px';
                flightBadge2.style.background = 'rgba(0, 92, 66, 0.08)';
                flightBadge2.textContent = `${ticket.carrier2} ${ticket.flight2}`.trim();
                flightCell.appendChild(flightBadge2);
            }
            tr.appendChild(flightCell);

            const routeCell = document.createElement('td');
            routeCell.appendChild(document.createTextNode(`${ticket.orig_code || ''} → ${ticket.dest_code || ''}`));
            if (ticket.from2 && ticket.to2) {
                routeCell.appendChild(document.createElement('br'));
                const small = document.createElement('small');
                small.style.color = 'var(--text-muted)';
                small.textContent = `${ticket.from2} → ${ticket.to2}`;
                routeCell.appendChild(small);
            }
            tr.appendChild(routeCell);

            appendTextCell(tr, paxStr, { small: true });

            const actionCell = document.createElement('td');
            const actionWrap = document.createElement('div');
            actionWrap.style.display = 'flex';
            actionWrap.style.gap = '5px';
            const loadBtn = makeActionButton('Cargar', 'btn btn-sm', () => loadTicketToForm(ticket));
            loadBtn.setAttribute('data-i18n', 'btn_load');
            actionWrap.appendChild(loadBtn);
            actionWrap.appendChild(makeActionButton('PDF', 'btn btn-sm btn-secondary', () => downloadTicketPDF(ticket)));
            actionCell.appendChild(actionWrap);
            tr.appendChild(actionCell);
            tbody.appendChild(tr);
        });
    } else {
        appendTableMessage(tbody, 7, 'No hay trip pass que coincidan con los filtros.');
    }

    // Update count
    const countEl = document.getElementById('filter-results-count');
    if (countEl) {
        countEl.textContent = `Mostrando ${filtered.length} de ${window.allTickets.length} trip pass`;
    }
}

window.downloadTicketPDF = function(ticket) {
    loadTicketToForm(ticket);
    setTimeout(() => {
        const element = document.querySelector('.ticket-canvas');
        const opt = {
            margin: 5,
            filename: 'TripPass_' + ticket.ticket_id + '.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                scrollY: 0,
                backgroundColor: '#ffffff',
                letterRendering: true
            },
            jsPDF: { unit: 'mm', format: 'letter', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    }, 600);
};

window.loadTicketToForm = function(ticket) {
    // Fill Passenger first/last name
    const pax = JSON.parse(ticket.passengers || '[]');
    let fullName = Array.isArray(pax) ? pax[0] || '' : ticket.passengers || '';
    let parts = fullName.trim().split(' ');
    let firstName = '';
    let lastName = '';
    if (parts.length > 1) {
        lastName = parts.pop();
        firstName = parts.join(' ');
    } else {
        firstName = fullName;
    }
    document.getElementById('f-first-name').value = firstName;
    document.getElementById('f-last-name').value = lastName;
    
    // Fill Operator details
    document.getElementById('f-approved-by').value = ticket.approved_by || '';
    document.getElementById('f-req-by').value = ticket.requested_by || '';
    document.getElementById('f-area').value = ticket.area || '';
    document.getElementById('f-signature').value = ticket.signature || '';
    
    const onFileCheckbox = document.getElementById('f-on-file');
    if (onFileCheckbox) {
        onFileCheckbox.checked = ticket.on_file == 1;
        document.getElementById('prev-on-file-cb').innerHTML = ticket.on_file == 1 ? 'X' : '';
    }

    // Set Passenger Type select option
    const optVal = {
        "Cargo Attendants": "cb-cargo",
        "Company Business": "cb-company",
        "Customers": "cb-customers",
        "Employee off Duty": "cb-offduty",
        "Employee's Dependant": "cb-dependant",
        "Extra Crew": "cb-extracrew",
        "Others": "cb-others"
    }[ticket.passenger_type] || "";
    document.getElementById('f-passenger-type').value = optVal;
    
    document.getElementById('f-priority').value = ticket.priority || '';

    // Fill Flight 1
    document.getElementById('f-carrier1').value = ticket.transportadora || '';
    document.getElementById('f-flight1').value = ticket.flight || '';
    document.getElementById('f-from1').value = ticket.orig_code || '';
    document.getElementById('f-to1').value = ticket.dest_code || '';
    document.getElementById('f-date1').value = ticket.date_out || '';
    document.getElementById('f-time1').value = ticket.time || '';

    // Fill Flight 2
    document.getElementById('f-carrier2').value = ticket.carrier2 || '';
    document.getElementById('f-flight2').value = ticket.flight2 || '';
    document.getElementById('f-from2').value = ticket.from2 || '';
    document.getElementById('f-to2').value = ticket.to2 || '';
    document.getElementById('f-date2').value = ticket.date_return || '';
    document.getElementById('f-time2').value = ticket.time2 || '';

    // Toggle tramo UI based on Flight 2 & Flight 3
    if (ticket.carrier2 || ticket.flight2 || ticket.date_return) {
        const wrapper = document.getElementById('flt2-wrapper');
        if (wrapper) wrapper.style.maxHeight = '500px';
        const btnAdd2 = document.getElementById('btnAddTramo2');
        if (btnAdd2) btnAdd2.style.display = 'none';
        const btnAdd3 = document.getElementById('btnAddTramo3');
        if (btnAdd3) btnAdd3.style.display = 'inline-block';
    } else {
        const wrapper = document.getElementById('flt2-wrapper');
        if (wrapper) wrapper.style.maxHeight = '0';
        const btnAdd2 = document.getElementById('btnAddTramo2');
        if (btnAdd2) btnAdd2.style.display = 'inline-block';
        const btnAdd3 = document.getElementById('btnAddTramo3');
        if (btnAdd3) btnAdd3.style.display = 'none';
    }

    if (ticket.carrier3 || ticket.flight3) {
        const wrapper = document.getElementById('flt3-wrapper');
        if (wrapper) wrapper.style.maxHeight = '500px';
        const btnAdd3 = document.getElementById('btnAddTramo3');
        if (btnAdd3) btnAdd3.style.display = 'none';
    } else {
        const wrapper = document.getElementById('flt3-wrapper');
        if (wrapper) wrapper.style.maxHeight = '0';
    }

    // Trigger input events to update the live preview
    const inputs = [
        'f-first-name', 'f-last-name', 'f-approved-by', 'f-req-by', 'f-area', 'f-signature', 'f-priority',
        'f-carrier1', 'f-flight1', 'f-from1', 'f-to1', 'f-date1', 'f-time1',
        'f-carrier2', 'f-flight2', 'f-from2', 'f-to2', 'f-date2', 'f-time2'
    ];
    
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.dispatchEvent(new Event('input'));
        }
    });

    // Populate passenger type checkboxes in preview
    document.querySelectorAll('.cb-type').forEach(el => el.innerHTML = '');
    if (optVal) {
        const prevCb = document.getElementById('prev-' + optVal);
        if (prevCb) prevCb.innerHTML = 'X';
    }

    // Switch back to generate tab
    document.querySelector('[data-target="tab-generate"]').click();
    
    // Keep the old ticket ID and Audit Fields
    document.getElementById('prev-ticket-id').textContent = ticket.ticket_id;

    // Generate QR code for this ticket's verify URL
    const qrContainer = document.getElementById('qr-code-container');
    if (qrContainer && typeof QRCode !== 'undefined') {
        qrContainer.innerHTML = '';
        const protocol = window.location.protocol;
        const host = window.location.host;
        const verifyUrl = `${protocol}//${host}/verify/${ticket.ticket_id}`;
        new QRCode(qrContainer, {
            text: verifyUrl,
            width: 70,
            height: 70,
            colorDark: '#1a5142',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
    
    // Warning or status
    const resultDiv = document.getElementById('ticketResult');
    resultDiv.innerHTML = '<p style="color:var(--text-primary)">Trip Pass cargado del historial. Puedes volver a descargarlo.</p>';
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

    appendTableMessage(tbody, 5, 'Cargando usuarios...');

    try {
        const response = await fetch('/api/users');
        const data = await response.json();

        if (data.success && data.users.length > 0) {
            clearChildren(tbody);
            data.users.forEach(u => {
                const tr = document.createElement('tr');
                appendTextCell(tr, u.id);
                appendTextCell(tr, u.full_name);

                const userCell = document.createElement('td');
                userCell.appendChild(document.createTextNode(u.username || ''));
                userCell.appendChild(document.createElement('br'));
                const email = document.createElement('small');
                email.textContent = u.email || '';
                userCell.appendChild(email);
                tr.appendChild(userCell);

                const roleCell = document.createElement('td');
                if (u.is_admin) {
                    const role = document.createElement('span');
                    role.style.color = 'var(--primary-color)';
                    role.style.fontWeight = 'bold';
                    role.textContent = 'Admin';
                    roleCell.appendChild(role);
                } else {
                    roleCell.textContent = 'Usuario';
                }
                tr.appendChild(roleCell);

                const actionCell = document.createElement('td');
                const adminBtn = makeActionButton(u.is_admin ? 'Quitar Admin' : 'Hacer Admin', 'btn btn-sm', () => toggleAdmin(Number(u.id), Boolean(Number(u.is_admin))));
                adminBtn.style.background = '#005c42';
                const deleteBtn = makeActionButton('Eliminar', 'btn btn-sm', () => deleteUser(Number(u.id)));
                deleteBtn.style.background = '#cc0000';
                actionCell.appendChild(adminBtn);
                actionCell.appendChild(document.createTextNode(' '));
                actionCell.appendChild(deleteBtn);
                tr.appendChild(actionCell);
                tbody.appendChild(tr);
            });
        } else {
            appendTableMessage(tbody, 5, 'No hay usuarios.');
        }
    } catch (err) {
        appendTableMessage(tbody, 5, 'Error al cargar.', 'red');
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

// --- TRANSLATION LOGIC ---
const translations = {
    es: {
        title: "Generador de Trip Pass",
        logout: "Cerrar Sesión",
        tab_dashboard: "Dashboard",
        tab_generate: "Generar Trip Pass",
        tab_history: "Historial de Trip Pass",
        tab_users: "Gestión de Usuarios",
        flight_details_title: "✈️ Detalles del Trip Pass",
        sec_passengers: "Pasajero",
        lbl_last_name: "Apellidos (Last Name)",
        lbl_first_name: "Nombre (First/Given Name)",
        lbl_requested_by: "Solicitado por (Requested By)",
        lbl_title_dept: "Título / Dept (Title/Dept)",
        lbl_approved_by: "Aprobado por (Approved By)",
        lbl_signature: "Firma (Signature)",
        lbl_on_file: "ON FILE",
        sec_operations: "Detalles Operativos",
        lbl_pax_type: "Tipo de pasajero",
        opt_select: "Seleccionar",
        opt_cargo: "Cargo Attendants",
        opt_company: "Company Business",
        opt_customers: "Customers",
        opt_offduty: "Employee off Duty",
        opt_dependant: "Employee's Dependant",
        opt_extracrew: "Extra Crew",
        opt_others: "Others",
        lbl_priority: "Prioridad",
        sec_route: "Rutas (Tramos)",
        lbl_flight1: "Primer Tramo (FLT 1) *",
        lbl_carrier: "Carrier",
        lbl_flight_num: "Vuelo",
        lbl_from: "From",
        lbl_to: "To",
        lbl_date: "Date",
        lbl_time: "Time",
        lbl_flight2: "Segundo Tramo (FLT 2)",
        lbl_flight3: "Tercer Tramo (FLT 3)",
        btn_generate: "Generar Trip Pass PDF",
        msg_generated: "¡Trip Pass generado!",
        btn_download: "Descargar PDF",
        live_preview: "Previsualización en Vivo",
        history_title: "Trip Pass Generados",
        th_id: "ID Pase",
        th_date: "Fecha Creado (CDMX)",
        th_by: "Generado Por",
        th_flight: "Vuelo / Fecha",
        th_route: "Ruta",
        th_pax: "Pasajeros",
        th_action: "Acción",
        msg_loading_hist: "Cargando historial...",
        users_create_title: "👤 Crear Nuevo Usuario",
        lbl_u_name: "Nombre Completo",
        lbl_u_username: "Nombre de Usuario",
        lbl_u_email: "Correo Electrónico",
        btn_create_user: "Crear Usuario",
        users_list_title: "Usuarios Registrados",
        th_name: "Nombre",
        th_user_email: "Usuario / Correo",
        th_role: "Rol",
        th_actions: "Acciones",
        msg_loading_users: "Cargando usuarios...",
        modal_confirm_payment: "Confirma que el pago aduanal se ha realizado",
        modal_paid: "Pagado",
        modal_no: "No",
        btn_cancel: "Cancelar",
        btn_confirm: "Confirmar",
        
        // New dashboard & UX translations
        kpi_total_tickets: "Total Trip Pass Emitidos",
        kpi_total_tickets_sub: "Histórico acumulado",
        kpi_today_tickets: "Trip Pass de Hoy",
        kpi_today_tickets_sub: "Emitidos en CDMX",
        kpi_active_users: "Operadores Activos",
        kpi_active_users_sub: "Usuarios con emisión",
        kpi_system_status: "Estado del Sistema",
        dash_recent_activity: "🔄 Actividad Reciente",
        badge_live: "En Vivo",
        dash_analytics: "📊 Analítica Operativa",
        chart_carrier_dist: "Distribución por Aerolínea (Carrier)",
        chart_pax_dist: "Emisiones por Tipo de Pasajero",
        msg_loading_act: "Cargando actividad...",
        filters_title: "🔍 Filtros de Búsqueda",
        lbl_filter_pax: "Nombre del Pasajero",
        lbl_filter_flight: "Número de Vuelo",
        lbl_filter_carrier: "Aerolínea (Carrier)",
        lbl_filter_type: "Tipo de Pasajero",
        lbl_filter_date: "Fecha de Vuelo",
        btn_clear_filters: "Limpiar Filtros",
        opt_all: "Todos",
        lbl_quick_presets: "Rutas Rápidas:",
        lbl_trip_type: "Tramos",
        btn_add_tramo2: "+ Agregar Segundo Tramo",
        btn_add_tramo3: "+ Agregar Tercer Tramo",
        btn_load: "Cargar"
    },
    en: {
        title: "Trip Pass Generator",
        logout: "Logout",
        tab_dashboard: "Dashboard",
        tab_generate: "Generate Trip Pass",
        tab_history: "Trip Pass History",
        tab_users: "User Management",
        flight_details_title: "✈️ Trip Pass Details",
        sec_passengers: "Passenger",
        lbl_last_name: "Last Name",
        lbl_first_name: "First/Given Name",
        lbl_requested_by: "Requested By",
        lbl_title_dept: "Title/Dept",
        lbl_approved_by: "Approved By",
        lbl_signature: "Signature",
        lbl_on_file: "ON FILE",
        sec_operations: "Operational Details",
        lbl_pax_type: "Passenger Type",
        opt_select: "Select",
        opt_cargo: "Cargo Attendants",
        opt_company: "Company Business",
        opt_customers: "Customers",
        opt_offduty: "Employee off Duty",
        opt_dependant: "Employee's Dependant",
        opt_extracrew: "Extra Crew",
        opt_others: "Others",
        lbl_priority: "Priority",
        sec_route: "Route (Legs)",
        lbl_flight1: "First Leg (FLT 1) *",
        lbl_carrier: "Carrier",
        lbl_flight_num: "Flight",
        lbl_from: "From",
        lbl_to: "To",
        lbl_date: "Date",
        lbl_time: "Time",
        lbl_flight2: "Second Leg (FLT 2)",
        lbl_flight3: "Third Leg (FLT 3)",
        btn_generate: "Generate Trip Pass PDF",
        msg_generated: "Trip Pass generated!",
        btn_download: "Download PDF",
        live_preview: "Live Preview",
        history_title: "Generated Trip Pass",
        th_id: "Pass ID",
        th_date: "Date Created (CDMX)",
        th_by: "Generated By",
        th_flight: "Flight / Date",
        th_route: "Route",
        th_pax: "Passengers",
        th_action: "Action",
        msg_loading_hist: "Loading history...",
        users_create_title: "👤 Create New User",
        lbl_u_name: "Full Name",
        lbl_u_username: "Username",
        lbl_u_email: "Email Address",
        btn_create_user: "Create User",
        users_list_title: "Registered Users",
        th_name: "Name",
        th_user_email: "User / Email",
        th_role: "Role",
        th_actions: "Actions",
        msg_loading_users: "Loading users...",
        modal_confirm_payment: "Confirm customs payment has been made",
        modal_paid: "Paid",
        modal_no: "No",
        btn_cancel: "Cancel",
        btn_confirm: "Confirm",
        
        // New dashboard & UX translations
        kpi_total_tickets: "Total Trip Pass Issued",
        kpi_total_tickets_sub: "Accumulated history",
        kpi_today_tickets: "Today's Trip Pass",
        kpi_today_tickets_sub: "Issued in CDMX",
        kpi_active_users: "Active Operators",
        kpi_active_users_sub: "Users with issuance",
        kpi_system_status: "System Status",
        dash_recent_activity: "🔄 Recent Activity",
        badge_live: "Live",
        dash_analytics: "📊 Operational Analytics",
        chart_carrier_dist: "Carrier Distribution",
        chart_pax_dist: "Issuance by Passenger Type",
        msg_loading_act: "Loading activity...",
        filters_title: "🔍 Search Filters",
        lbl_filter_pax: "Passenger Name",
        lbl_filter_flight: "Flight Number",
        lbl_filter_carrier: "Carrier",
        lbl_filter_type: "Passenger Type",
        lbl_filter_date: "Flight Date",
        btn_clear_filters: "Clear Filters",
        opt_all: "All",
        lbl_quick_presets: "Quick Routes:",
        lbl_trip_type: "Legs",
        btn_add_tramo2: "+ Add Second Leg",
        btn_add_tramo3: "+ Add Third Leg",
        btn_load: "Load"
    }
};

let currentLang = 'es';

document.addEventListener('DOMContentLoaded', () => {
    const langBtn = document.getElementById('langToggle');
    if(langBtn) {
        // Initial state
        langBtn.innerHTML = 'EN / <strong>ES</strong>';
        
        langBtn.addEventListener('click', () => {
            currentLang = currentLang === 'es' ? 'en' : 'es';
            applyTranslations(currentLang);
            langBtn.innerHTML = currentLang === 'es' ? 'EN / <strong>ES</strong>' : '<strong>EN</strong> / ES';
        });
    }
});

function applyTranslations(lang) {
    const elements = document.querySelectorAll('[data-i18n]');
    elements.forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[lang] && translations[lang][key]) {
            if (el.tagName === 'INPUT' && el.type === 'button') {
                el.value = translations[lang][key];
            } else {
                if (el.children.length === 0 || el.tagName === 'OPTION') {
                    el.textContent = translations[lang][key];
                } else {
                    el.innerHTML = translations[lang][key]; 
                }
            }
        }
    });
}
