<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$itemId = (int)($_POST['item_id'] ?? 0);
$buyerName = trim($_POST['buyer_name'] ?? '');
$amount = (float)($_POST['amount'] ?? 0);
$message = trim($_POST['message'] ?? '');
$contactWhatsapp = trim($_POST['contact_whatsapp'] ?? '');

$errors = [];

if (!$itemId || !getItem($itemId)) {
    $errors[] = 'Artículo no válido.';
}
if ($buyerName === '') {
    $errors[] = 'El nombre es obligatorio.';
}
if ($amount <= 0) {
    $errors[] = 'Ingresa un monto válido.';
}
if ($contactWhatsapp === '') {
    $errors[] = 'El WhatsApp es obligatorio.';
}

if (empty($errors)) {
    addOffer($itemId, $buyerName, $amount, $message, $contactWhatsapp);
    $success = true;
} else {
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Oferta enviada - Venta de Garage</title>
<link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<div class="container">
    <div class="result-box">
        <?php if ($success): ?>
            <h2>✅ ¡Oferta enviada!</h2>
            <p>Gracias, <?= h($buyerName) ?>. Tu oferta de <?= formatPrice($amount) ?> ha sido registrada.</p>
            <?php if (getSetting('auto_approve_offers') !== '1'): ?>
                <p>El vendedor revisará tu oferta y si es aceptada aparecerá publicada.</p>
            <?php endif; ?>
        <?php else: ?>
            <h2>❌ Error</h2>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="index.php" class="btn">Volver al catálogo</a>
    </div>
</div>
</body>
</html>
