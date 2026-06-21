<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $phoneWA = trim($_POST['phone_whatsapp'] ?? '');
    $phoneSignal = trim($_POST['phone_signal'] ?? '');
    $autoApprove = isset($_POST['auto_approve_offers']) ? '1' : '0';
    $pageTitle = trim($_POST['page_title'] ?? '');
    $pageDesc = trim($_POST['page_description'] ?? '');
    $cityName = trim($_POST['city_name'] ?? '');
    $maxSize = trim($_POST['max_delivery_size'] ?? '');

    if ($password !== '') {
        updateSetting('admin_password', password_hash($password, PASSWORD_BCRYPT));
    }
    updateSetting('phone_whatsapp', $phoneWA);
    updateSetting('phone_signal', $phoneSignal);
    updateSetting('auto_approve_offers', $autoApprove);
    updateSetting('page_title', $pageTitle);
    updateSetting('page_description', $pageDesc);
    updateSetting('city_name', $cityName);
    updateSetting('max_delivery_size', $maxSize);

    for ($i = 1; $i <= 3; $i++) {
        $name = trim($_POST["point_name_$i"] ?? '');
        $addr = trim($_POST["point_address_$i"] ?? '');
        $notes = trim($_POST["point_notes_$i"] ?? '');
        $lat = (float)($_POST["point_lat_$i"] ?? 0);
        $lng = (float)($_POST["point_lng_$i"] ?? 0);
        if ($name) {
            updateMeetingPoint($i, $name, $addr, $notes, $lat, $lng);
        }
    }

    $message = 'Configuración guardada.';
}

$phoneWA = getSetting('phone_whatsapp');
$phoneSignal = getSetting('phone_signal');
$autoApprove = getSetting('auto_approve_offers');
$pageTitle = getSetting('page_title');
$pageDesc = getSetting('page_description');
$cityName = getSetting('city_name');
$maxSize = getSetting('max_delivery_size');
$points = getMeetingPoints();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuración - Admin</title>
<link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <header class="header">
        <h1>⚙️ Configuración</h1>
        <nav class="admin-nav">
            <a href="dashboard.php" class="btn btn-sm">Dashboard</a>
            <a href="items.php" class="btn btn-sm">Artículos</a>
            <a href="offers.php" class="btn btn-sm">Ofertas</a>
            <a href="settings.php" class="btn btn-sm active">Configuración</a>
            <a href="logout.php" class="btn btn-sm btn-outline">Salir</a>
        </nav>
    </header>

    <main>
        <?php if ($message): ?><p class="success-msg"><?= htmlspecialchars($message) ?></p><?php endif; ?>

        <form method="post" class="settings-form">
            <fieldset>
                <legend>🔐 Cambiar contraseña de admin</legend>
                <label>Nueva contraseña (dejar vacío para no cambiar)
                    <input type="password" name="password" placeholder="••••••••">
                </label>
            </fieldset>

            <fieldset>
                <legend>📞 Contacto</legend>
                <label>WhatsApp (solo números, ej: 521234567890)
                    <input type="text" name="phone_whatsapp" value="<?= htmlspecialchars($phoneWA) ?>">
                </label>
                <label>Signal (solo números, ej: 521234567890)
                    <input type="text" name="phone_signal" value="<?= htmlspecialchars($phoneSignal) ?>">
                </label>
            </fieldset>

            <fieldset>
                <legend>📄 Página</legend>
                <label>Título
                    <input type="text" name="page_title" value="<?= htmlspecialchars($pageTitle) ?>">
                </label>
                <label>Descripción
                    <textarea name="page_description" rows="2"><?= htmlspecialchars($pageDesc) ?></textarea>
                </label>
                <label>Ciudad
                    <input type="text" name="city_name" value="<?= htmlspecialchars($cityName) ?>">
                </label>
                <label>Tamaño máximo de entrega
                    <input type="text" name="max_delivery_size" value="<?= htmlspecialchars($maxSize) ?>">
                </label>
            </fieldset>

            <fieldset>
                <legend>💬 Ofertas</legend>
                <label class="checkbox-label">
                    <input type="checkbox" name="auto_approve_offers" value="1" <?= $autoApprove === '1' ? 'checked' : '' ?>>
                    Auto-aprobar ofertas (aparecen al instante sin revisión)
                </label>
            </fieldset>

            <fieldset>
                <legend>📍 Puntos de encuentro</legend>
                <p class="hint">Usa <a href="https://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> para encontrar las coordenadas de cada punto.</p>
                <?php foreach ($points as $i => $p): ?>
                    <div class="point-form">
                        <h4><?= htmlspecialchars($p['name']) ?></h4>
                        <label>Nombre
                            <input type="text" name="point_name_<?= $p['id'] ?>" value="<?= htmlspecialchars($p['name']) ?>">
                        </label>
                        <label>Dirección
                            <textarea name="point_address_<?= $p['id'] ?>" rows="2"><?= htmlspecialchars($p['address']) ?></textarea>
                        </label>
                        <label>Notas (horarios, referencias, etc.)
                            <textarea name="point_notes_<?= $p['id'] ?>" rows="2"><?= htmlspecialchars($p['notes']) ?></textarea>
                        </label>
                        <label>Latitud
                            <input type="text" name="point_lat_<?= $p['id'] ?>" value="<?= htmlspecialchars($p['lat']) ?>" placeholder="Ej: 19.4326">
                        </label>
                        <label>Longitud
                            <input type="text" name="point_lng_<?= $p['id'] ?>" value="<?= htmlspecialchars($p['lng']) ?>" placeholder="Ej: -99.1332">
                        </label>
                    </div>
                <?php endforeach; ?>
            </fieldset>

            <button type="submit" class="btn">Guardar configuración</button>
        </form>
    </main>
</div>
</body>
</html>
