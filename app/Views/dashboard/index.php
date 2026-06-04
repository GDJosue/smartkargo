<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mas Cargo | Generador de Boletos</title>
    <meta name="description" content="Sistema de generación de boletos oficiales y pases de abordar — Mas Cargo Airlines">
    <link rel="icon" href="/assets/img/cropped-site_logo-32x32.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body>

    <!-- ── Dashboard Header ── -->
    <header class="dashboard-header">
        <img src="/assets/img/logo.png" alt="Mas Cargo Logo">
        <h1>Generador de Boletos Oficiales</h1>
        <div class="dashboard-header-actions">
            <div class="user-badge" id="userBadge">Cargando...</div>
            <a id="logoutBtn" class="logout-link">Cerrar Sesión</a>
        </div>
    </header>

    <!-- ── Tab Navigation ── -->
    <div class="tabs-container">
        <nav class="tabs-nav">
            <button class="tab-btn active" data-target="tab-generate">Generar Boleto</button>
            <button class="tab-btn" data-target="tab-history" id="loadHistoryBtn">Historial de Boletos</button>
            <button class="tab-btn admin-only" data-target="tab-users" id="loadUsersBtn" style="display: none;">Gestión de Usuarios</button>
        </nav>

        <!-- ══════════ GENERATE TAB ══════════ -->
        <div class="tab-content active" id="tab-generate">
            <div class="split-layout">

                <!-- ── FORM COLUMN ── -->
                <div class="form-column">
                    <div class="card form-card" style="margin: 0; max-width: 100%;">
                        <h2>✈️ Detalles del Vuelo</h2>
                        <form id="ticketForm">

                            <!-- Section: Dates & Approval -->
                            <fieldset class="form-section">
                                <div class="form-section-title">
                                    <span class="section-icon">📅</span>
                                    <span>Fechas y Aprobación</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-date1">Fecha Salida</label>
                                        <input type="text" id="f-date1" value="" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-date2">Fecha Llegada</label>
                                        <input type="text" id="f-date2" value="" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="f-approved-by">Aprobado por</label>
                                    <input type="text" id="f-approved-by" value="" required>
                                </div>
                            </fieldset>

                            <!-- Section: Passengers -->
                            <fieldset class="form-section">
                                <div class="form-section-title">
                                    <span class="section-icon">👥</span>
                                    <span>Pasajeros</span>
                                </div>
                                <div class="form-group">
                                    <label for="f-passengers">Nombres de Pasajeros (Separados por renglón)</label>
                                    <textarea id="f-passengers" rows="3" required></textarea>
                                </div>
                            </fieldset>

                            <!-- Section: Operations -->
                            <fieldset class="form-section">
                                <div class="form-section-title">
                                    <span class="section-icon">⚙️</span>
                                    <span>Detalles Operativos</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-guide">Código de guía</label>
                                        <input type="text" id="f-guide" value="" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-area">Área o departamento</label>
                                        <input type="text" id="f-area" value="" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-transport">Transportadora</label>
                                        <input type="text" id="f-transport" value="MAA" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-passenger-type">Tipo de pasajero</label>
                                        <select id="f-passenger-type" required>
                                            <option value="" disabled selected>Seleccionar</option>
                                            <option value="Asistentes de carga">Asistentes de carga</option>
                                            <option value="Asuntos de la empresa">Asuntos de la empresa</option>
                                            <option value="Clientes">Clientes</option>
                                            <option value="Empleado fuera de servicio">Empleado fuera de servicio</option>
                                            <option value="Dependiente del empleado">Dependiente del empleado</option>
                                            <option value="Tripulación adicional">Tripulación adicional</option>
                                            <option value="Otros">Otros</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-priority">Prioridad</label>
                                        <select id="f-priority" required>
                                            <option value="" disabled selected>Seleccionar</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-status">Estado</label>
                                        <input type="text" id="f-status" value="" required>
                                    </div>
                                </div>
                            </fieldset>

                            <!-- Section: Route -->
                            <fieldset class="form-section">
                                <div class="form-section-title">
                                    <span class="section-icon">🗺️</span>
                                    <span>Ruta</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-orig-code">Origen (Código)</label>
                                        <input type="text" id="f-orig-code" value="" required>
                                        <label for="f-orig-city" class="mt-1">Origen (Ciudad)</label>
                                        <input type="text" id="f-orig-city" value="" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-dest-code">Destino (Código)</label>
                                        <input type="text" id="f-dest-code" value="" required>
                                        <label for="f-dest-city" class="mt-1">Destino (Ciudad)</label>
                                        <input type="text" id="f-dest-city" value="" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-time">Tiempo</label>
                                        <input type="text" id="f-time" value="" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-flight">Vuelo</label>
                                        <input type="text" id="f-flight" value="" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-aircraft">Avión</label>
                                        <input type="text" id="f-aircraft" value="" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-miles">Millaje</label>
                                        <input type="text" id="f-miles" value="" required>
                                    </div>
                                </div>
                            </fieldset>

                            <button type="submit" class="btn mt-2">Generar PDF Formal</button>
                            <div id="ticketResult" class="ticket-result" style="display: none;">
                                <p>¡Boleto generado!</p>
                                <a id="downloadLink" href="#" target="_blank" class="btn btn-secondary">Descargar PDF</a>
                            </div>
                            <div id="generateError" class="error-msg"></div>
                        </form>
                    </div>
                </div>

                <!-- ── PREVIEW COLUMN ── -->
                <div class="preview-column">
                    <div class="preview-header">
                        <h2>Previsualización en Vivo</h2>
                        <span class="preview-badge">LIVE</span>
                    </div>

                    <div class="ticket-canvas">
                        <!-- Boarding Pass Header -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div class="ticket-dates">
                                <span id="prev-date-out"></span> &#9654; <span id="prev-date-return"></span> APROBADO
                                POR <span id="prev-approved"></span>
                            </div>
                        </div>
                        <hr class="ticket-hr-bold">

                        <!-- Passenger Info + Logo -->
                        <div class="ticket-passengers-logo">
                            <div class="ticket-passengers">
                                <div class="ticket-label">PREPARADO PARA</div>
                                <div id="prev-passengers" class="ticket-pax-names"></div>
                            </div>
                            <div class="ticket-logo">
                                <img src="/assets/img/logo.png" alt="Mas Logo">
                            </div>
                        </div>

                        <!-- Guide Code -->
                        <div class="ticket-res-code">
                            <span class="ticket-label">CÓDIGO DE GUÍA</span>
                            <span id="prev-guide" class="ticket-res-val"></span>
                        </div>
                        <hr class="ticket-hr">

                        <!-- Departure Header -->
                        <div class="ticket-departure-header">
                            <span class="plane-icon">&#9992;</span> ÁREA O DEPTO: <strong id="prev-area"></strong>
                            <span class="dep-notice">Por favor verifique el horario de vuelo antes de la salida</span>
                        </div>

                        <!-- Details Grid -->
                        <div class="ticket-details-box">
                            <!-- Left: Metadata -->
                            <div class="tbox-gray">
                                <div class="tbox-header">MAS CARGO</div>

                                <div class="tlabel">Tipo de pasajero:</div>
                                <div class="tval" id="prev-passenger-type" style="margin-bottom: 8px;"></div>

                                <div class="tlabel">Prioridad:</div>
                                <div class="tval" id="prev-priority"></div>

                                <div class="tlabel">Estado:</div>
                                <div class="tval" id="prev-status"></div>
                            </div>

                            <!-- Middle: Route -->
                            <div class="tbox-white">
                                <div class="tbox-route">
                                    <!-- Origin -->
                                    <div class="route-point">
                                        <div class="route-code" id="prev-orig-code"></div>
                                        <div class="route-city" id="prev-orig-city"></div>
                                        <div class="tlabel mt-10">Tiempo:</div>
                                        <div class="route-time" id="prev-time"></div>
                                    </div>

                                    <!-- Arrow with Plane -->
                                    <div class="route-arrow">
                                        <div class="route-arrow-plane">✈</div>
                                        <div class="route-arrow-line"></div>
                                    </div>

                                    <!-- Destination -->
                                    <div class="route-point">
                                        <div class="route-code" id="prev-dest-code"></div>
                                        <div class="route-city" id="prev-dest-city"></div>
                                        <div class="tlabel mt-10">Vuelo:</div>
                                        <div class="route-time" id="prev-flight"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Transport -->
                            <div class="tbox-right">
                                <div class="tlabel">Transportadora:</div>
                                <div class="tval mb-15" id="prev-transport">MAA</div>

                                <div class="tlabel">Avión:</div>
                                <div class="tval mb-15" id="prev-aircraft"></div>

                                <div class="tlabel">Millaje: <span id="prev-miles"
                                        style="color: black; font-size: 0.9rem;"></span></div>
                            </div>
                        </div>

                        <!-- Passenger Table -->
                        <table class="ticket-table">
                            <thead>
                                <tr>
                                    <td>Nombre del pasajero:</td>
                                    <td>Asientos:</td>
                                    <td>Recibo(s) de billete(s) electrónico(s):</td>
                                </tr>
                            </thead>
                            <tbody id="prev-pax-table">
                                <!-- generated via JS -->
                            </tbody>
                        </table>

                        <!-- Ticket Footer -->
                        <div class="ticket-footer">
                            <div class="ticket-audit">
                                ID de Pase <strong id="prev-ticket-id">[PENDIENTE]</strong><br>
                                Generado por: <strong id="prev-created-by">[PENDIENTE]</strong><br>
                                Fecha/Hora (CDMX): <strong id="prev-created-at">[PENDIENTE]</strong><br>
                                <span class="ticket-official-stamp">Documento oficial Mas Cargo</span>
                            </div>
                            <div class="ticket-qr-space" id="ticket-qr">
                                <span class="qr-placeholder-text">QR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════ HISTORY TAB ══════════ -->
        <div class="tab-content" id="tab-history">
            <div class="history-card">
                <h2>Boletos Generados</h2>
                <div style="overflow-x: auto;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>ID Pase</th>
                                <th>Fecha Creado (CDMX)</th>
                                <th>Generado Por</th>
                                <th>Vuelo / Fecha</th>
                                <th>Ruta</th>
                                <th>Pasajeros</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr>
                                <td colspan="7" style="text-align:center;">Cargando historial...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════════ USERS TAB (ADMIN ONLY) ══════════ -->
        <div class="tab-content" id="tab-users">
            <div class="split-layout">
                <!-- FORM COLUMN -->
                <div class="form-column">
                    <div class="card form-card" style="margin: 0; max-width: 100%;">
                        <h2>👤 Crear Nuevo Usuario</h2>
                        <form id="createUserForm">
                            <div class="form-group">
                                <label for="u-name">Nombre Completo</label>
                                <input type="text" id="u-name" required>
                            </div>
                            <div class="form-group">
                                <label for="u-username">Nombre de Usuario</label>
                                <input type="text" id="u-username" required>
                            </div>
                            <div class="form-group">
                                <label for="u-email">Correo Electrónico</label>
                                <input type="email" id="u-email" required>
                            </div>
                            <button type="submit" class="btn mt-2">Crear Usuario</button>
                            <div id="createUserMsg" class="mt-2"></div>
                        </form>
                    </div>
                </div>

                <!-- TABLE COLUMN -->
                <div class="preview-column">
                    <div class="history-card" style="margin: 0;">
                        <h2>Usuarios Registrados</h2>
                        <div style="overflow-x: auto;">
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Usuario / Correo</th>
                                        <th>Rol</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <tr>
                                        <td colspan="5" style="text-align:center;">Cargando usuarios...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── PAYMENT MODAL ── -->
    <div id="paymentModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <h3>Confirma que el pago aduanal se ha realizado</h3>
            <div class="modal-radio-group">
                <label>
                    <input type="radio" name="paymentStatus" value="pagado" checked> Pagado
                </label>
                <label>
                    <input type="radio" name="paymentStatus" value="no"> No
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" id="btnCancelPayment" class="btn btn-cancel">Cancelar</button>
                <button type="button" id="btnConfirmPayment" class="btn">Confirmar</button>
            </div>
        </div>
    </div>

    <script src="/assets/js/main.js"></script>
    <script>
        // Check for session immediately
        document.addEventListener('DOMContentLoaded', async () => {
            const res = await fetch('/api/me');
            if (!res.ok) {
                window.location.href = '/login';
            } else {
                const data = await res.json();
                document.getElementById('userBadge').textContent = 'Bienvenido: ' + data.name + (data.isAdmin ? ' (Admin)' : '');
                if (data.isAdmin) {
                    document.getElementById('loadUsersBtn').style.display = 'inline-block';
                }
            }
        });

        document.getElementById('logoutBtn').addEventListener('click', async () => {
            await fetch('/api/logout', { method: 'POST' });
            window.location.href = '/login';
        });

        // LIVE PREVIEW LOGIC
        const inputs = [
            { id: 'f-date1', prev: 'prev-date-out' },
            { id: 'f-date2', prev: 'prev-date-return' },
            { id: 'f-approved-by', prev: 'prev-approved' },
            { id: 'f-guide', prev: 'prev-guide' },
            { id: 'f-area', prev: 'prev-area' },
            { id: 'f-transport', prev: 'prev-transport' },
            { id: 'f-priority', prev: 'prev-priority' },
            { id: 'f-status', prev: 'prev-status' },
            { id: 'f-orig-code', prev: 'prev-orig-code' },
            { id: 'f-orig-city', prev: 'prev-orig-city' },
            { id: 'f-dest-code', prev: 'prev-dest-code' },
            { id: 'f-dest-city', prev: 'prev-dest-city' },
            { id: 'f-time', prev: 'prev-time' },
            { id: 'f-flight', prev: 'prev-flight' },
            { id: 'f-aircraft', prev: 'prev-aircraft' },
            { id: 'f-miles', prev: 'prev-miles' },
            { id: 'f-passenger-type', prev: 'prev-passenger-type' }
        ];

        inputs.forEach(mapping => {
            const el = document.getElementById(mapping.id);
            const prev = document.getElementById(mapping.prev);
            if (el && prev) {
                el.addEventListener('input', () => { prev.textContent = el.value.toUpperCase(); });
            }
        });

        const paxInput = document.getElementById('f-passengers');
        const paxNames = document.getElementById('prev-passengers');
        const paxTable = document.getElementById('prev-pax-table');

        function updatePassengers() {
            const names = paxInput.value.split('\n').filter(n => n.trim() !== '');
            paxNames.innerHTML = names.join('<br>');

            paxTable.innerHTML = '';
            let baseTicket = 1392163966934;
            names.forEach((name, i) => {
                paxTable.innerHTML += `
                <tr>
                    <td style="border-bottom: 1px solid #ccc; padding: 6px 0;">&raquo; ${name.toUpperCase()}</td>
                    <td style="border-bottom: 1px solid #ccc; padding: 6px 0;">Sin asignar</td>
                    <td style="border-bottom: 1px solid #ccc; padding: 6px 0; color: #555;">${baseTicket + i}</td>
                </tr>`;
            });
        }

        paxInput.addEventListener('input', updatePassengers);
        updatePassengers(); // init

        // SUBMIT FORM LOGIC
        document.getElementById('ticketForm').addEventListener('submit', (e) => {
            e.preventDefault();
            // Mostrar modal
            document.getElementById('paymentModal').style.display = 'flex';
        });

        // Cancelar Modal
        document.getElementById('btnCancelPayment').addEventListener('click', () => {
            document.getElementById('paymentModal').style.display = 'none';
        });

        // Confirmar Modal
        document.getElementById('btnConfirmPayment').addEventListener('click', async () => {
            const status = document.querySelector('input[name="paymentStatus"]:checked').value;
            
            // Ocultar modal
            document.getElementById('paymentModal').style.display = 'none';
            
            if (status === 'no') {
                return; // Detener flujo, no generar boleto
            }

            const generateBtn = document.querySelector('button[type="submit"]');
            const resultDiv = document.getElementById('ticketResult');
            const errorDiv = document.getElementById('generateError');
            const downloadLink = document.getElementById('downloadLink');

            generateBtn.disabled = true;
            generateBtn.textContent = 'Generando...';
            resultDiv.style.display = 'none';
            errorDiv.style.display = 'none';

            const data = {
                dateOut: document.getElementById('f-date1').value,
                dateReturn: document.getElementById('f-date2').value,
                approvedBy: document.getElementById('f-approved-by').value,
                passengers: document.getElementById('f-passengers').value.split('\n').filter(n => n.trim() !== ''),
                guideCode: document.getElementById('f-guide').value,
                area: document.getElementById('f-area').value,
                transportadora: document.getElementById('f-transport').value,
                passengerType: document.getElementById('f-passenger-type').value,
                priority: document.getElementById('f-priority').value,
                status: document.getElementById('f-status').value,
                origCode: document.getElementById('f-orig-code').value,
                origCity: document.getElementById('f-orig-city').value,
                destCode: document.getElementById('f-dest-code').value,
                destCity: document.getElementById('f-dest-city').value,
                time: document.getElementById('f-time').value,
                flight: document.getElementById('f-flight').value,
                aircraft: document.getElementById('f-aircraft').value,
                miles: document.getElementById('f-miles').value
            };

            try {
                const response = await fetch('/api/save-ticket', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {

                    // Show ID and Audit fields on canvas
                    document.getElementById('prev-ticket-id').textContent = result.ticketId;
                    document.getElementById('prev-created-by').textContent = result.created_by_name;
                    document.getElementById('prev-created-at').textContent = result.created_at_cdmx;

                    // Generate QR Code
                    const qrContainer = document.getElementById('ticket-qr');
                    qrContainer.innerHTML = '';
                    new QRCode(qrContainer, {
                        text: window.location.origin + '/verify/' + result.ticketId,
                        width: 80,
                        height: 80,
                        colorDark: '#005c42',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });

                    // Allow more time for layout to settle
                    setTimeout(() => {
                        const element = document.querySelector('.ticket-canvas');
                        const opt = {
                            margin: 10,
                            filename: 'Boleto_' + result.ticketId + '.pdf',
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

                        html2pdf().set(opt)
                            .from(element)
                            .save()
                            .then(() => {
                                // Update UI after success
                                resultDiv.style.display = 'block';
                                downloadLink.style.display = 'none';
                                generateBtn.disabled = false;
                                generateBtn.textContent = 'Generar Nuevo PDF';
                            });
                    }, 1200);

                } else {
                    errorDiv.textContent = result.message || 'Error al generar boleto';
                    errorDiv.style.display = 'block';
                    generateBtn.disabled = false;
                    generateBtn.textContent = 'Generar PDF Formal';
                }
            } catch (err) {
                console.error(err);
                errorDiv.textContent = 'Error de conexión';
                errorDiv.style.display = 'block';
                generateBtn.disabled = false;
                generateBtn.textContent = 'Generar PDF Formal';
            }
        });
    </script>

</body>

</html>