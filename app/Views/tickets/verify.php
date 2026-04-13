<?php
    $passengers = isset($ticket) ? json_decode($ticket['passengers'], true) : [];
    $passengersHtml = is_array($passengers) ? implode('<br>', array_map('htmlspecialchars', $passengers)) : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Pase - Mas Cargo</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f4f2; padding: 20px; display: flex; justify-content: center; }
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); max-width: 400px; width: 100%; text-align: center; }
        .valid { color: #005c42; font-size: 24px; font-weight: 800; margin: 20px 0; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .detail { text-align: left; margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 8px;}
        .detail strong { display: block; color: #666; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 3px;}
        .detail span { font-weight: 600; font-size: 1.1rem; color: #222;}
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <img src="/assets/img/logo.png" style="max-width:120px; margin-bottom: 10px;">
        
        <?php if (isset($error)): ?>
            <h1 class="error"><?php echo htmlspecialchars($error); ?></h1>
        <?php else: ?>
            <div class="valid">✅ PASE VÁLIDO</div>
            <div class="detail"><strong>ID de Autorización</strong><span><?php echo htmlspecialchars($ticket['ticket_id'] ?? ''); ?></span></div>
            <div class="detail"><strong>Pasajeros</strong><span><?php echo $passengersHtml; ?></span></div>
            <div class="detail"><strong>Vuelo</strong><span><?php echo htmlspecialchars($ticket['flight_num'] ?? ''); ?></span></div>
            <div class="detail"><strong>Ruta</strong><span><?php echo htmlspecialchars($ticket['orig_code'] ?? ''); ?> ✈ <?php echo htmlspecialchars($ticket['dest_code'] ?? ''); ?></span></div>
            <div class="detail"><strong>Fecha de Vuelo</strong><span><?php echo htmlspecialchars($ticket['dep_day'] ?? ''); ?></span></div>
        <?php endif; ?>
    </div>
</body>
</html>
