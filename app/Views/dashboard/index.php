<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mas Cargo | Generador de Boletos</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body style="min-height: 100vh; overflow-y: auto;">

    <div class="dashboard-header" style="text-align: center; margin: 2rem 0;">
        <img src="/assets/img/logo.png" alt="Mas Cargo Logo" style="max-width: 100px;">
        <h1 style="color: var(--primary-color);">Generador de Boletos Oficiales</h1>
        <div class="user-badge" id="userBadge" style="margin-top: 10px;">Cargando...</div>
        <div style="margin-top: 10px;"><a id="logoutBtn" class="logout-link">Cerrar Sesión</a></div>
    </div>

    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" data-target="tab-generate">Generar Boleto</button>
            <button class="tab-btn" data-target="tab-history" id="loadHistoryBtn">Historial de Boletos</button>
        </div>

        <!-- GENERATE TAB -->
        <div class="tab-content active" id="tab-generate">
            <div class="split-layout">
                <!-- FORM COLUMN -->
                <div class="form-column">
                    <div class="card form-card" style="margin: 0; max-width: 100%;">
                        <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">Detalles del Vuelo</h2>
                        <form id="ticketForm">

                            <!-- Fechas y Pasajeros -->
                            <div class="form-row">
                                <div class="form-group"><label>Fecha Salida</label><input type="text" id="f-date1"
                                        value="" required></div>
                                <div class="form-group"><label>Fecha Llegada</label><input type="text" id="f-date2"
                                        value="" required></div>
                            </div>
                            <div class="form-group"><label>Aprobado por</label><input type="text" id="f-approved-by"
                                    value="" required></div>

                            <div class="form-group" style="margin-bottom: 1rem;"><label>Nombres de Pasajeros (Separados
                                    por renglón)</label><textarea id="f-passengers" rows="3" required></textarea></div>

                            <div class="form-row">
                                <div class="form-group"><label>Código de guía</label><input type="text" id="f-guide"
                                        value="" required></div>
                                <div class="form-group"><label>Área o departamento</label><input type="text" id="f-area"
                                        value="" required></div>
                            </div>

                            <!-- Detalles del Vuelo -->
                            <h3
                                style="margin: 1.5rem 0 1rem; font-size: 1rem; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
                                Detalles Operativos</h3>
                            <div class="form-row">
                                <div class="form-group"><label>Transportadora</label><input type="text" id="f-transport"
                                        value="MAA" required></div>
                                <div class="form-group">
                                    <label>Prioridad</label>
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
                            </div>
                            <div class="form-row">
                                <div class="form-group" style="width: 100%;"><label>Estado</label><input type="text"
                                        id="f-status" value="" required></div>
                            </div>

                            <!-- Origen y Destino -->
                            <h3
                                style="margin: 1.5rem 0 1rem; font-size: 1rem; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
                                Ruta</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Origen (Código)</label><input type="text" id="f-orig-code" value="" required>
                                    <label>Origen (Ciudad)</label><input type="text" id="f-orig-city" value="" required>
                                </div>
                                <div class="form-group">
                                    <label>Destino (Código)</label><input type="text" id="f-dest-code" value=""
                                        required>
                                    <label>Destino (Ciudad)</label><input type="text" id="f-dest-city" value=""
                                        required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tiempo</label><input type="text" id="f-time" value="" required>
                                </div>
                                <div class="form-group">
                                    <label>Vuelo</label><input type="text" id="f-flight" value="" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group"><label>Avión</label><input type="text" id="f-aircraft" value=""
                                        required></div>
                                <div class="form-group"><label>Millaje</label><input type="text" id="f-miles" value=""
                                        required></div>
                            </div>

                            <button type="submit" class="btn" style="margin-top: 1rem;">Generar PDF Formal</button>
                            <div id="ticketResult" class="ticket-result" style="display: none;">
                                <p>¡Boleto generado!</p>
                                <a id="downloadLink" href="#" target="_blank" class="btn btn-secondary">Descargar
                                    PDF</a>
                            </div>
                            <div id="generateError" class="error-msg"></div>
                        </form>
                    </div>
                </div>

                <!-- PREVIEW COLUMN -->
                <div class="preview-column">
                    <h2 style="font-size: 1.2rem; margin-bottom: 1rem; color: #333;">Previsualización en Vivo</h2>
                    <div class="ticket-canvas">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div class="ticket-dates">
                                <span id="prev-date-out"></span> &#9654; <span id="prev-date-return"></span> APROBADO
                                POR <span id="prev-approved"></span>
                            </div>
                        </div>
                        <hr class="ticket-hr">

                        <div class="ticket-passengers-logo">
                            <div class="ticket-passengers">
                                <div class="ticket-label">PREPARADO PARA</div>
                                <div id="prev-passengers" class="ticket-pax-names">
                                </div>
                            </div>
                            <div class="ticket-logo">
                                <img src="/assets/img/logo.png" alt="Mas Logo" style="height: 45px;">
                            </div>
                        </div>

                        <div class="ticket-res-code">
                            <span class="ticket-label">CÓDIGO DE GUÍA</span> <span id="prev-guide"
                                class="ticket-res-val"></span>
                        </div>
                        <hr class="ticket-hr">

                        <div class="ticket-departure-header">
                            <span class="plane-icon">&#9992;</span> ÁREA O DEPTO: <strong id="prev-area"></strong>
                            <span class="dep-notice">Por favor verifique el horario de vuelo antes de la salida</span>
                        </div>

                        <div class="ticket-details-box">
                            <!-- Left gray box -->
                            <div class="tbox-gray">
                                <div class="tbox-header">MAS CARGO</div>

                                <div class="tlabel">Prioridad:</div>
                                <div class="tval" id="prev-priority"></div>

                                <div class="tlabel">Estado:</div>
                                <div class="tval" id="prev-status"></div>
                            </div>
                            <!-- Middle white box -->
                            <div class="tbox-white">
                                <div class="tbox-route">
                                    <!-- Origin -->
                                    <div class="route-point">
                                        <div class="route-code" id="prev-orig-code"></div>
                                        <div class="route-city" id="prev-orig-city"></div>

                                        <div class="tlabel mt-10">Tiempo:</div>
                                        <div class="route-time" id="prev-time"></div>
                                    </div>

                                    <div class="route-arrow">&#9654;</div>

                                    <!-- Destination -->
                                    <div class="route-point">
                                        <div class="route-code" id="prev-dest-code"></div>
                                        <div class="route-city" id="prev-dest-city"></div>

                                        <div class="tlabel mt-10">Vuelo:</div>
                                        <div class="route-time" id="prev-flight"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Right white box -->
                            <div class="tbox-right">
                                <div class="tlabel">Transportadora:</div>
                                <div class="tval mb-15" id="prev-transport">MAA</div>

                                <div class="tlabel">Avión:</div>
                                <div class="tval mb-15" id="prev-aircraft"></div>

                                <div class="tlabel">Millaje: <span id="prev-miles"
                                        style="color: black; font-size: 0.9rem;"></span></div>
                            </div>
                        </div>

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
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 30px;">
                            <div style="font-size: 0.75rem; color: #666; font-family: monospace;">
                                ID de Pase <strong id="prev-ticket-id" style="color:#000;">[PENDIENTE]</strong><br>
                                Documento oficial Mas Cargo
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HISTORY TAB -->
        <div class="tab-content" id="tab-history">
            <div class="history-card">
                <h2 style="margin-bottom: 1rem; color: var(--primary-color);">Boletos Generados</h2>
                <div style="overflow-x: auto;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>ID Pase</th>
                                <th>Fecha Creado</th>
                                <th>Vuelo / Fecha</th>
                                <th>Ruta</th>
                                <th>Pasajeros</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr>
                                <td colspan="6" style="text-align:center;">Cargando historial...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
                document.getElementById('userBadge').textContent = 'Bienvenido: ' + data.name;
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
            { id: 'f-miles', prev: 'prev-miles' }
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
        document.getElementById('ticketForm').addEventListener('submit', async (e) => {
            e.preventDefault();
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

                    // Show ID on canvas
                    document.getElementById('prev-ticket-id').textContent = result.ticketId;

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