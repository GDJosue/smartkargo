<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mas Cargo | Generador de Trip Pass</title>
    <meta name="description" content="Sistema de generación de Trip Pass y pases de abordar — Mas Cargo Airlines">
    <link rel="icon" href="/assets/img/cropped-site_logo-32x32.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <!-- ── Dashboard Header ── -->
    <header class="dashboard-header">
        <img src="/assets/img/logo.png" alt="Mas Cargo Logo">
        <h1 data-i18n="title">Generador de Trip Pass</h1>
        <div class="dashboard-header-actions">
            <button id="langToggle" class="btn btn-sm btn-secondary" style="margin-right:15px;">EN / <strong>ES</strong></button>
            <div class="user-badge" id="userBadge">Cargando...</div>
            <a id="logoutBtn" class="logout-link" data-i18n="logout">Cerrar Sesión</a>
        </div>
    </header>

    <!-- ── Tab Navigation ── -->
    <div class="tabs-container">
        <nav class="tabs-nav">
            <button class="tab-btn active" data-target="tab-dashboard" id="tabDashboardBtn" data-i18n="tab_dashboard">Dashboard</button>
            <button class="tab-btn" data-target="tab-generate" data-i18n="tab_generate">Generar Trip Pass</button>
            <button class="tab-btn" data-target="tab-history" id="loadHistoryBtn" data-i18n="tab_history">Historial de Trip Pass</button>
            <button class="tab-btn admin-only" data-target="tab-users" id="loadUsersBtn" style="display: none;" data-i18n="tab_users">Gestión de Usuarios</button>
        </nav>

        <!-- ══════════ DASHBOARD TAB ══════════ -->
        <div class="tab-content active" id="tab-dashboard">
            <!-- KPI Cards Grid -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon">🎫</div>
                    <div class="kpi-content">
                        <div class="kpi-label" data-i18n="kpi_total_tickets">Total Trip Pass Emitidos</div>
                        <div class="kpi-value" id="kpi-total-tickets">0</div>
                        <div class="kpi-subtext" data-i18n="kpi_total_tickets_sub">Histórico acumulado</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon">📅</div>
                    <div class="kpi-content">
                        <div class="kpi-label" data-i18n="kpi_today_tickets">Trip Pass de Hoy</div>
                        <div class="kpi-value" id="kpi-today-tickets">0</div>
                        <div class="kpi-subtext" data-i18n="kpi_today_tickets_sub">Emitidos en CDMX</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon">👥</div>
                    <div class="kpi-content">
                        <div class="kpi-label" data-i18n="kpi_active_users">Operadores Activos</div>
                        <div class="kpi-value" id="kpi-active-users">0</div>
                        <div class="kpi-subtext" data-i18n="kpi_active_users_sub">Usuarios con emisión</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon">🖥️</div>
                    <div class="kpi-content">
                        <div class="kpi-label" data-i18n="kpi_system_status">Estado del Sistema</div>
                        <div class="kpi-value status-online">
                            <span class="status-indicator"></span> ONLINE
                        </div>
                        <div class="kpi-subtext" id="kpi-clock">CDMX --:--:--</div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Analytics Layout -->
            <div class="dashboard-layout">
                <!-- Left Column: Recent Activity -->
                <div class="dashboard-col dashboard-activity">
                    <div class="card" style="margin: 0; max-width: 100%; height: 100%;">
                        <div class="card-header-flex">
                            <h2 data-i18n="dash_recent_activity">🔄 Actividad Reciente</h2>
                            <span class="badge badge-live" data-i18n="badge_live">En Vivo</span>
                        </div>
                        <div class="activity-feed-container">
                            <table class="history-table recent-table">
                                <thead>
                                    <tr>
                                        <th data-i18n="th_id">ID Pase</th>
                                        <th data-i18n="th_pax">Pasajero</th>
                                        <th data-i18n="th_flight">Vuelo</th>
                                        <th data-i18n="th_route">Ruta</th>
                                        <th data-i18n="th_action">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="recentActivityBody">
                                    <tr>
                                        <td colspan="5" style="text-align: center;" data-i18n="msg_loading_act">Cargando actividad...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Charts -->
                <div class="dashboard-col dashboard-charts">
                    <div class="card" style="margin: 0; max-width: 100%; height: 100%;">
                        <h2 data-i18n="dash_analytics">📊 Analítica Operativa</h2>
                        <div class="charts-grid">
                            <div class="chart-container">
                                <h3 data-i18n="chart_carrier_dist" style="margin-bottom:10px;">Distribución por Aerolínea (Carrier)</h3>
                                <div class="chart-wrapper">
                                    <canvas id="chartCarrier"></canvas>
                                </div>
                            </div>
                            <div class="chart-container">
                                <h3 data-i18n="chart_pax_dist" style="margin-bottom:10px;">Emisiones por Tipo de Pasajero</h3>
                                <div class="chart-wrapper">
                                    <canvas id="chartPaxType"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════ GENERATE TAB ══════════ -->
        <div class="tab-content" id="tab-generate">
            <div class="split-layout">

                <!-- ── FORM COLUMN ── -->
                <div class="form-column">
                    <div class="card form-card" style="margin: 0; max-width: 100%;">
                        <h2 data-i18n="flight_details_title">✈️ Detalles del Trip Pass</h2>
                        <form id="ticketForm">

                            <fieldset class="form-section">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-last-name" data-i18n="lbl_last_name">Apellidos (Last Name)</label>
                                        <input type="text" id="f-last-name" value="" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-first-name" data-i18n="lbl_first_name">Nombre (First/Given Name)</label>
                                        <input type="text" id="f-first-name" value="" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-req-by" data-i18n="lbl_requested_by">Solicitado por (Requested By)</label>
                                        <input type="text" id="f-req-by" value="" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-area" data-i18n="lbl_title_dept">Título / Dept (Title/Dept)</label>
                                        <input type="text" id="f-area" value="" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="f-approved-by" data-i18n="lbl_approved_by">Aprobado por (Approved By)</label>
                                    <input type="text" id="f-approved-by" value="" required>
                                </div>
                                <div class="form-row" style="align-items: center;">
                                    <div class="form-group" style="flex: 1;">
                                        <label for="f-signature" data-i18n="lbl_signature">Firma (Signature)</label>
                                        <input type="text" id="f-signature" value="" placeholder="Dejar en blanco si es ON FILE">
                                    </div>
                                    <div class="form-group" style="flex: 0 0 auto; margin-top: 1.5rem;">
                                        <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                                            <input type="checkbox" id="f-on-file" style="width:auto;"> <span data-i18n="lbl_on_file">ON FILE</span>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>

                            <!-- Section: Operations -->
                            <fieldset class="form-section">
                                <div class="form-section-title">
                                    <span class="section-icon">⚙️</span>
                                    <span data-i18n="sec_operations">Detalles Operativos</span>
                                </div>
                                <div class="form-group">
                                    <label for="f-passenger-type" data-i18n="lbl_pax_type">Tipo de pasajero</label>
                                    <select id="f-passenger-type" required>
                                        <option value="" disabled selected data-i18n="opt_select">Seleccionar</option>
                                        <option value="cb-cargo" data-i18n="opt_cargo">Cargo Attendants</option>
                                        <option value="cb-company" data-i18n="opt_company">Company Business</option>
                                        <option value="cb-customers" data-i18n="opt_customers">Customers</option>
                                        <option value="cb-offduty" data-i18n="opt_offduty">Employee off Duty</option>
                                        <option value="cb-dependant" data-i18n="opt_dependant">Employee's Dependant</option>
                                        <option value="cb-extracrew" data-i18n="opt_extracrew">Extra Crew</option>
                                        <option value="cb-others" data-i18n="opt_others">Others</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="f-priority" data-i18n="lbl_priority">Prioridad</label>
                                    <input type="text" id="f-priority" value="" maxlength="2" style="text-align: center; font-size:1.2rem;" required>
                                </div>
                            </fieldset>

                            <!-- Section: Route -->
                            <fieldset class="form-section">
                                <div class="form-section-title">
                                    <span class="section-icon">🗺️</span>
                                    <span data-i18n="sec_route">Rutas (Tramos)</span>
                                </div>

                                <!-- Presets and Toggle -->
                                <div class="quick-routes-container">
                                    <span class="presets-lbl" data-i18n="lbl_quick_presets">Rutas Rápidas:</span>
                                    <div class="quick-routes-buttons">
                                        <button type="button" class="btn-preset" data-carrier="MAA" data-flight="101" data-from="MEX" data-to="LAX" data-time="08:30">MEX-LAX</button>
                                        <button type="button" class="btn-preset" data-carrier="MAA" data-flight="202" data-from="MEX" data-to="MIA" data-time="14:15">MEX-MIA</button>
                                        <button type="button" class="btn-preset" data-carrier="MAA" data-flight="303" data-from="MEX" data-to="CUN" data-time="20:00">MEX-CUN</button>
                                    </div>
                                </div>

                                <div class="form-group trip-type-group">
                                    <label data-i18n="lbl_trip_type">Tramos</label>
                                    <p style="font-size:0.8rem; color:var(--text-muted); margin:0 0 5px;">Primer tramo es obligatorio. Puedes agregar hasta 2 tramos adicionales.</p>
                                </div>
                                
                                <h3 style="margin-bottom:10px; font-size:0.85rem;" data-i18n="lbl_flight1">Primer Tramo (FLT 1) *</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-carrier1" data-i18n="lbl_carrier">Carrier</label>
                                        <input type="text" id="f-carrier1" value="MAA" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-flight1" data-i18n="lbl_flight_num">Vuelo</label>
                                        <input type="text" id="f-flight1" value="" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-from1" data-i18n="lbl_from">From</label>
                                        <input type="text" id="f-from1" value="" required>
                                    </div>
                                    <div class="form-group">
                                        <label flex-direction="column" for="f-to1" data-i18n="lbl_to">To</label>
                                        <input type="text" id="f-to1" value="" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="f-date1" data-i18n="lbl_date">Date</label>
                                        <input type="text" id="f-date1" value="" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="f-time1" data-i18n="lbl_time">Time</label>
                                        <input type="text" id="f-time1" value="" required>
                                    </div>
                                </div>

                                <div class="tramo-toggle-container" style="margin: 10px 0;">
                                    <button type="button" id="btnAddTramo2" class="btn btn-sm btn-secondary" data-i18n="btn_add_tramo2">+ Agregar Segundo Tramo</button>
                                    <button type="button" id="btnAddTramo3" class="btn btn-sm btn-secondary" style="display:none;" data-i18n="btn_add_tramo3">+ Agregar Tercer Tramo</button>
                                </div>

                                <div id="flt2-wrapper" class="collapsed-section" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out;">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <h3 style="margin:15px 0 10px; font-size:0.85rem;" data-i18n="lbl_flight2">Segundo Tramo (FLT 2)</h3>
                                        <button type="button" id="btnRemoveTramo2" class="btn btn-sm" style="background:#cc0000; font-size:0.7rem; padding:2px 8px;">✕ Quitar</button>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="f-carrier2" data-i18n="lbl_carrier">Carrier</label>
                                            <input type="text" id="f-carrier2" value="MAA">
                                        </div>
                                        <div class="form-group">
                                            <label for="f-flight2" data-i18n="lbl_flight_num">Vuelo</label>
                                            <input type="text" id="f-flight2" value="">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="f-from2" data-i18n="lbl_from">From</label>
                                            <input type="text" id="f-from2" value="">
                                        </div>
                                        <div class="form-group">
                                            <label for="f-to2" data-i18n="lbl_to">To</label>
                                            <input type="text" id="f-to2" value="">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="f-date2" data-i18n="lbl_date">Date</label>
                                            <input type="text" id="f-date2" value="">
                                        </div>
                                        <div class="form-group">
                                            <label for="f-time2" data-i18n="lbl_time">Time</label>
                                            <input type="text" id="f-time2" value="">
                                        </div>
                                    </div>
                                </div>

                                <div id="flt3-wrapper" class="collapsed-section" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out;">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <h3 style="margin:15px 0 10px; font-size:0.85rem;" data-i18n="lbl_flight3">Tercer Tramo (FLT 3)</h3>
                                        <button type="button" id="btnRemoveTramo3" class="btn btn-sm" style="background:#cc0000; font-size:0.7rem; padding:2px 8px;">✕ Quitar</button>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="f-carrier3" data-i18n="lbl_carrier">Carrier</label>
                                            <input type="text" id="f-carrier3" value="MAA">
                                        </div>
                                        <div class="form-group">
                                            <label for="f-flight3" data-i18n="lbl_flight_num">Vuelo</label>
                                            <input type="text" id="f-flight3" value="">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="f-from3" data-i18n="lbl_from">From</label>
                                            <input type="text" id="f-from3" value="">
                                        </div>
                                        <div class="form-group">
                                            <label for="f-to3" data-i18n="lbl_to">To</label>
                                            <input type="text" id="f-to3" value="">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="f-date3" data-i18n="lbl_date">Date</label>
                                            <input type="text" id="f-date3" value="">
                                        </div>
                                        <div class="form-group">
                                            <label for="f-time3" data-i18n="lbl_time">Time</label>
                                            <input type="text" id="f-time3" value="">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <button type="submit" class="btn mt-2" data-i18n="btn_generate">Generar Trip Pass PDF</button>
                            <div id="ticketResult" class="ticket-result" style="display: none;">
                                <p data-i18n="msg_generated">¡Trip Pass generado!</p>
                                <a id="downloadLink" href="#" target="_blank" class="btn btn-secondary" data-i18n="btn_download">Descargar PDF</a>
                            </div>
                            <div id="generateError" class="error-msg"></div>
                        </form>
                    </div>
                </div>

                <!-- ── PREVIEW COLUMN ── -->
                <div class="preview-column">
                    <div class="preview-header">
                        <h2 data-i18n="live_preview">Previsualización en Vivo</h2>
                        <span class="preview-badge">LIVE</span>
                    </div>

                    <div class="ticket-canvas" id="ticket-canvas-content">
                        <div class="physical-ticket">
                            <div class="pt-watermark">ISSUING FILE</div>
                            <div class="pt-sidebar">ORIGINAL: ISSUING STATION CONTROL FILE</div>
                            
                            <div class="pt-main">
                                <div class="pt-header">
                                    <div class="pt-logo"><img src="/assets/img/logo.png" alt="mas"></div>
                                    <div class="pt-title">TRIP AND BOARDING PASS</div>
                                    <div class="pt-number" id="prev-ticket-id">000 0000 0</div>
                                </div>

                                <div class="pt-body">
                                    <div class="pt-left">
                                        <div class="pt-row"><div class="pt-label">PASSENGER LAST NAME</div><div class="pt-val" id="prev-last-name"></div></div>
                                        <div class="pt-row"><div class="pt-label">PASSENGER FIRST/GIVEN NAME</div><div class="pt-val" id="prev-first-name"></div></div>
                                        <div class="pt-row"><div class="pt-label">REQUESTED BY</div><div class="pt-val" id="prev-req-by"></div></div>
                                        <div class="pt-row"><div class="pt-label">TITTLE / DEPT</div><div class="pt-val" id="prev-dept"></div></div>
                                        <div class="pt-row"><div class="pt-label">APPROVED BY</div><div class="pt-val" id="prev-approved"></div></div>
                                        <div class="pt-row pt-sig-row">
                                            <div class="pt-label">SIGNATURE</div>
                                            <div class="pt-val" id="prev-signature"></div>
                                            <div class="pt-on-file">
                                                <span>ON FILE</span>
                                                <div class="pt-checkbox" id="prev-on-file-cb"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-right">
                                        <div class="pt-pax-types">
                                            <div class="pt-pax-item"><div class="pt-checkbox cb-type" id="prev-cb-cargo"></div> <span class="pt-cb-lbl">Cargo Attendants</span></div>
                                            <div class="pt-pax-item"><div class="pt-checkbox cb-type" id="prev-cb-company"></div> <span class="pt-cb-lbl">Company Business</span></div>
                                            <div class="pt-pax-item"><div class="pt-checkbox cb-type" id="prev-cb-customers"></div> <span class="pt-cb-lbl">Customers</span></div>
                                            <div class="pt-pax-item"><div class="pt-checkbox cb-type" id="prev-cb-offduty"></div> <span class="pt-cb-lbl">Employee off Duty</span></div>
                                            <div class="pt-pax-item"><div class="pt-checkbox cb-type" id="prev-cb-dependant"></div> <span class="pt-cb-lbl">Employee's Dependant</span></div>
                                            <div class="pt-pax-item"><div class="pt-checkbox cb-type" id="prev-cb-others"></div> <span class="pt-cb-lbl">Others</span></div>
                                            <div class="pt-pax-item"><div class="pt-checkbox cb-type" id="prev-cb-extracrew"></div> <span class="pt-cb-lbl">Extra Crew</span></div>
                                        </div>
                                        <div class="pt-priority">
                                            <div class="pt-priority-lbl">PRIORITY</div>
                                            <div class="pt-priority-box" id="prev-priority-box"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-footer">
                                    <div class="pt-footer-left">
                                        <div class="pt-label-sm">IF "ON FILE" ATTACH E-MAIL TO "ISSUING FILE" COPY</div>
                                        <div class="pt-fee-box">FEE NO FARE</div>
                                        <div id="qr-code-container" style="display:flex; align-items:center; justify-content:center; padding:8px; min-height:80px;"></div>
                                    </div>
                                    <div class="pt-footer-right">
                                        <table class="pt-flt-table">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>CARRIER</th>
                                                    <th>FLIGHT</th>
                                                    <th>FROM</th>
                                                    <th>TO</th>
                                                    <th>DATE</th>
                                                    <th>TIME</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="pt-flt-label">FLT 1</td>
                                                    <td id="prev-carrier1">MAA</td>
                                                    <td id="prev-flight1"></td>
                                                    <td id="prev-from1"></td>
                                                    <td id="prev-to1"></td>
                                                    <td id="prev-date1"></td>
                                                    <td id="prev-time1"></td>
                                                </tr>
                                                <tr>
                                                    <td class="pt-flt-label">FLT 2</td>
                                                    <td id="prev-carrier2">MAA</td>
                                                    <td id="prev-flight2"></td>
                                                    <td id="prev-from2"></td>
                                                    <td id="prev-to2"></td>
                                                    <td id="prev-date2"></td>
                                                    <td id="prev-time2"></td>
                                                </tr>
                                                <tr>
                                                    <td class="pt-flt-label">FLT 3</td>
                                                    <td id="prev-carrier3">MAA</td>
                                                    <td id="prev-flight3"></td>
                                                    <td id="prev-from3"></td>
                                                    <td id="prev-to3"></td>
                                                    <td id="prev-date3"></td>
                                                    <td id="prev-time3"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════ HISTORY TAB ══════════ -->
        <div class="tab-content" id="tab-history">
            <!-- Advanced Filters Card -->
            <div class="filters-card">
                <h2 data-i18n="filters_title" class="filters-title">🔍 Filtros de Búsqueda</h2>
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="filter-pax" data-i18n="lbl_filter_pax">Nombre del Pasajero</label>
                        <input type="text" id="filter-pax" placeholder="Buscar por pasajero...">
                    </div>
                    <div class="form-group">
                        <label for="filter-flight" data-i18n="lbl_filter_flight">Número de Vuelo</label>
                        <input type="text" id="filter-flight" placeholder="Ej. 101, 202...">
                    </div>
                    <div class="form-group">
                        <label for="filter-carrier" data-i18n="lbl_filter_carrier">Aerolínea (Carrier)</label>
                        <select id="filter-carrier">
                            <option value="" data-i18n="opt_all">Todos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter-type" data-i18n="lbl_filter_type">Tipo de Pasajero</label>
                        <select id="filter-type">
                            <option value="" data-i18n="opt_all">Todos</option>
                            <option value="Cargo Attendants" data-i18n="opt_cargo">Cargo Attendants</option>
                            <option value="Company Business" data-i18n="opt_company">Company Business</option>
                            <option value="Customers" data-i18n="opt_customers">Customers</option>
                            <option value="Employee off Duty" data-i18n="opt_offduty">Employee off Duty</option>
                            <option value="Employee's Dependant" data-i18n="opt_dependant">Employee's Dependant</option>
                            <option value="Extra Crew" data-i18n="opt_extracrew">Extra Crew</option>
                            <option value="Others" data-i18n="opt_others">Others</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter-date" data-i18n="lbl_filter_date">Fecha de Vuelo</label>
                        <input type="text" id="filter-date" placeholder="DD-MM-YYYY">
                    </div>
                    <div class="form-group btn-filter-clear-group">
                        <button type="button" id="btnClearFilters" class="btn btn-secondary btn-sm" data-i18n="btn_clear_filters">Limpiar Filtros</button>
                    </div>
                </div>
                <div class="filter-results-info">
                    <span id="filter-results-count" data-i18n="msg_showing_all">Mostrando 0 trip pass</span>
                </div>
            </div>

            <div class="history-card">
                <h2 data-i18n="history_title">Trip Pass Generados</h2>
                <div style="overflow-x: auto;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th data-i18n="th_id">ID Pase</th>
                                <th data-i18n="th_date">Fecha Creado (CDMX)</th>
                                <th data-i18n="th_by">Generado Por</th>
                                <th data-i18n="th_flight">Vuelo / Fecha</th>
                                <th data-i18n="th_route">Ruta</th>
                                <th data-i18n="th_pax">Pasajeros</th>
                                <th data-i18n="th_action">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr>
                                <td colspan="7" style="text-align:center;" data-i18n="msg_loading_hist">Cargando historial...</td>
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
                        <h2 data-i18n="users_create_title">👤 Crear Nuevo Usuario</h2>
                        <form id="createUserForm">
                            <div class="form-group">
                                <label for="u-name" data-i18n="lbl_u_name">Nombre Completo</label>
                                <input type="text" id="u-name" required>
                            </div>
                            <div class="form-group">
                                <label for="u-username" data-i18n="lbl_u_username">Nombre de Usuario</label>
                                <input type="text" id="u-username" required>
                            </div>
                            <div class="form-group">
                                <label for="u-email" data-i18n="lbl_u_email">Correo Electrónico</label>
                                <input type="email" id="u-email" required>
                            </div>
                            <button type="submit" class="btn mt-2" data-i18n="btn_create_user">Crear Usuario</button>
                            <div id="createUserMsg" class="mt-2"></div>
                        </form>
                    </div>
                </div>

                <!-- TABLE COLUMN -->
                <div class="preview-column">
                    <div class="history-card" style="margin: 0;">
                        <h2 data-i18n="users_list_title">Usuarios Registrados</h2>
                        <div style="overflow-x: auto;">
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th data-i18n="th_name">Nombre</th>
                                        <th data-i18n="th_user_email">Usuario / Correo</th>
                                        <th data-i18n="th_role">Rol</th>
                                        <th data-i18n="th_actions">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <tr>
                                        <td colspan="5" style="text-align:center;" data-i18n="msg_loading_users">Cargando usuarios...</td>
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
            <h3 data-i18n="modal_confirm_payment">Confirma que el pago aduanal se ha realizado</h3>
            <div class="modal-radio-group">
                <label>
                    <input type="radio" name="paymentStatus" value="pagado" checked> <span data-i18n="modal_paid">Pagado</span>
                </label>
                <label>
                    <input type="radio" name="paymentStatus" value="no"> <span data-i18n="modal_no">No</span>
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" id="btnCancelPayment" class="btn btn-cancel" data-i18n="btn_cancel">Cancelar</button>
                <button type="button" id="btnConfirmPayment" class="btn" data-i18n="btn_confirm">Confirmar</button>
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
            { id: 'f-last-name', prev: 'prev-last-name' },
            { id: 'f-first-name', prev: 'prev-first-name' },
            { id: 'f-req-by', prev: 'prev-req-by' },
            { id: 'f-area', prev: 'prev-dept' },
            { id: 'f-approved-by', prev: 'prev-approved' },
            { id: 'f-signature', prev: 'prev-signature' },
            { id: 'f-priority', prev: 'prev-priority-box' },
            { id: 'f-carrier1', prev: 'prev-carrier1' },
            { id: 'f-flight1', prev: 'prev-flight1' },
            { id: 'f-from1', prev: 'prev-from1' },
            { id: 'f-to1', prev: 'prev-to1' },
            { id: 'f-date1', prev: 'prev-date1' },
            { id: 'f-time1', prev: 'prev-time1' },
            { id: 'f-carrier2', prev: 'prev-carrier2' },
            { id: 'f-flight2', prev: 'prev-flight2' },
            { id: 'f-from2', prev: 'prev-from2' },
            { id: 'f-to2', prev: 'prev-to2' },
            { id: 'f-date2', prev: 'prev-date2' },
            { id: 'f-time2', prev: 'prev-time2' },
            { id: 'f-carrier3', prev: 'prev-carrier3' },
            { id: 'f-flight3', prev: 'prev-flight3' },
            { id: 'f-from3', prev: 'prev-from3' },
            { id: 'f-to3', prev: 'prev-to3' },
            { id: 'f-date3', prev: 'prev-date3' },
            { id: 'f-time3', prev: 'prev-time3' }
        ];

        inputs.forEach(mapping => {
            const el = document.getElementById(mapping.id);
            const prev = document.getElementById(mapping.prev);
            if (el && prev) {
                el.addEventListener('input', () => { prev.textContent = el.value.toUpperCase(); });
            }
        });

        // Checkboxes mapping
        document.getElementById('f-on-file').addEventListener('change', (e) => {
            document.getElementById('prev-on-file-cb').innerHTML = e.target.checked ? 'X' : '';
        });

        document.getElementById('f-passenger-type').addEventListener('change', (e) => {
            // clear all
            document.querySelectorAll('.cb-type').forEach(el => el.innerHTML = '');
            const selected = e.target.value;
            if (selected) {
                document.getElementById('prev-' + selected).innerHTML = 'X';
            }
        });

        // SUBMIT FORM LOGIC
        document.getElementById('ticketForm').addEventListener('submit', (e) => {
            e.preventDefault();
            document.getElementById('paymentModal').style.display = 'flex';
        });

        document.getElementById('btnCancelPayment').addEventListener('click', () => {
            document.getElementById('paymentModal').style.display = 'none';
        });

        document.getElementById('btnConfirmPayment').addEventListener('click', async () => {
            const status = document.querySelector('input[name="paymentStatus"]:checked').value;
            
            document.getElementById('paymentModal').style.display = 'none';
            
            if (status === 'no') return;

            const generateBtn = document.querySelector('button[type="submit"]');
            const resultDiv = document.getElementById('ticketResult');
            const errorDiv = document.getElementById('generateError');
            const downloadLink = document.getElementById('downloadLink');

            generateBtn.disabled = true;
            resultDiv.style.display = 'none';
            errorDiv.style.display = 'none';

            // Adapt data for backend keeping compatibility where possible
            const paxFullName = document.getElementById('f-first-name').value + ' ' + document.getElementById('f-last-name').value;
            
            // Tramos logic: check if tramo 2 and 3 have data
            const hasTramo2 = document.getElementById('f-flight2').value.trim() !== '' || document.getElementById('f-from2').value.trim() !== '';
            const hasTramo3 = document.getElementById('f-flight3').value.trim() !== '' || document.getElementById('f-from3').value.trim() !== '';

            const data = {
                dateOut: document.getElementById('f-date1').value,
                dateReturn: hasTramo2 ? document.getElementById('f-date2').value : '',
                approvedBy: document.getElementById('f-approved-by').value,
                passengers: [paxFullName],
                guideCode: 'N/A', // Deprecated in physical design
                area: document.getElementById('f-area').value,
                transportadora: document.getElementById('f-carrier1').value,
                passengerType: document.getElementById('f-passenger-type').options[document.getElementById('f-passenger-type').selectedIndex].text,
                priority: document.getElementById('f-priority').value,
                status: 'OK', // Default
                origCode: document.getElementById('f-from1').value,
                origCity: '',
                destCode: document.getElementById('f-to1').value,
                destCity: '',
                time: document.getElementById('f-time1').value,
                flight: document.getElementById('f-flight1').value,
                aircraft: '', // Deprecated
                miles: '', // Deprecated
                carrier2: hasTramo2 ? document.getElementById('f-carrier2').value : '',
                flight2: hasTramo2 ? document.getElementById('f-flight2').value : '',
                from2: hasTramo2 ? document.getElementById('f-from2').value : '',
                to2: hasTramo2 ? document.getElementById('f-to2').value : '',
                time2: hasTramo2 ? document.getElementById('f-time2').value : '',
                carrier3: hasTramo3 ? document.getElementById('f-carrier3').value : '',
                flight3: hasTramo3 ? document.getElementById('f-flight3').value : '',
                from3: hasTramo3 ? document.getElementById('f-from3').value : '',
                to3: hasTramo3 ? document.getElementById('f-to3').value : '',
                date3: hasTramo3 ? document.getElementById('f-date3').value : '',
                time3: hasTramo3 ? document.getElementById('f-time3').value : '',
                requestedBy: document.getElementById('f-req-by').value,
                signature: document.getElementById('f-signature').value,
                onFile: document.getElementById('f-on-file').checked ? 1 : 0
            };

            try {
                const response = await fetch('/api/save-ticket', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    // Autosave inputs to localStorage
                    localStorage.setItem('masair_last_req_by', document.getElementById('f-req-by').value);
                    localStorage.setItem('masair_last_area', document.getElementById('f-area').value);
                    localStorage.setItem('masair_last_approved_by', document.getElementById('f-approved-by').value);

                    // Refresh dashboard after a slight delay
                    if (typeof loadDashboard === 'function') {
                        setTimeout(loadDashboard, 1000);
                    }

                    // Format Ticket ID to match physical example (e.g. 865 6389 0)
                    // Let's pad and format the DB ID
                    let formattedId = String(result.ticketId).padStart(8, '0');
                    formattedId = formattedId.substring(0,3) + ' ' + formattedId.substring(3,7) + ' ' + formattedId.substring(7);
                    
                    document.getElementById('prev-ticket-id').textContent = formattedId;

                    // Generate QR code with verify URL
                    const qrContainer = document.getElementById('qr-code-container');
                    qrContainer.innerHTML = '';
                    new QRCode(qrContainer, {
                        text: result.verifyUrl,
                        width: 70,
                        height: 70,
                        colorDark: '#1a5142',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });

                    setTimeout(() => {
                        const element = document.querySelector('.ticket-canvas');
                        const opt = {
                            margin: 5,
                            filename: 'TripPass_' + result.ticketId + '.pdf',
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
                                resultDiv.style.display = 'block';
                                downloadLink.style.display = 'none';
                                generateBtn.disabled = false;
                            });
                    }, 500);

                } else {
                    errorDiv.textContent = result.message || 'Error al generar trip pass';
                    errorDiv.style.display = 'block';
                    generateBtn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                errorDiv.textContent = 'Error de conexión';
                errorDiv.style.display = 'block';
                generateBtn.disabled = false;
            }
        });
    </script>

</body>

</html>