<?php
require_once __DIR__ . '/includes/functions.php';
$points = getMeetingPoints();
$city = h(getSetting('city_name'));
$maxSize = h(getSetting('max_delivery_size'));
$pageTitle = 'Puntos de Entrega - ' . h(getSetting('page_title') ?: 'Venta de Garage');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title><?= $pageTitle ?></title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin>
<link rel="stylesheet" href="assets/css/estilo.css">
<style>
#map { height: 400px; width: 100%; border-radius: 8px; margin-top: 1rem; }
.point-card { background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #25D366; }
.point-card h3 { margin: 0 0 0.3rem; }
.point-card .addr { color: #555; }
.point-card .notes { font-style: italic; color: #777; margin-top: 0.3rem; }
</style>
</head>
<body>
<div class="container">
    <header class="header">
        <h1>📍 Puntos de Entrega</h1>
        <?php if ($city): ?><p>Ciudad: <strong><?= $city ?></strong></p><?php endif; ?>
        <?php if ($maxSize): ?><p class="delivery-note"><?= $maxSize ?></p><?php endif; ?>
        <p><a href="index.php">← Volver al catálogo</a></p>
    </header>

    <main>
        <?php if (empty($points)): ?>
            <p>No hay puntos de entrega configurados aún.</p>
        <?php else: ?>
            <div class="points-list">
                <?php foreach ($points as $p): ?>
                    <div class="point-card" data-lat="<?= h($p['lat']) ?>" data-lng="<?= h($p['lng']) ?>" data-name="<?= h($p['name']) ?>">
                        <h3><?= h($p['name']) ?></h3>
                        <p class="addr"><?= nl2br(h($p['address'])) ?></p>
                        <?php if ($p['notes']): ?>
                            <p class="notes"><?= nl2br(h($p['notes'])) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="map"></div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p><a href="index.php">← Volver al catálogo</a></p>
    </footer>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin></script>
<script src="assets/js/app.js"></script>
<script>
(function() {
    var points = document.querySelectorAll('.point-card');
    if (points.length === 0) return;

    var map = L.map('map').setView([0, 0], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var bounds = [];
    points.forEach(function(card) {
        var lat = parseFloat(card.dataset.lat);
        var lng = parseFloat(card.dataset.lng);
        if (!lat && !lng) return;
        var name = card.dataset.name;
        var marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup('<strong>' + name + '</strong>');
        bounds.push([lat, lng]);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
})();
</script>
</body>
</html>
