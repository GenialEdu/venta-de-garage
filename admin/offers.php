<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offerId = (int)($_POST['offer_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($offerId && $action === 'approve') {
        approveOffer($offerId);
    } elseif ($offerId && $action === 'reject') {
        rejectOffer($offerId);
    }
    header('Location: offers.php');
    exit;
}

$pendingOffers = getAllOffers('pendiente');
$approvedOffers = getAllOffers('aprobada');
$rejectedOffers = getAllOffers('rechazada');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ofertas - Admin</title>
<link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <header class="header">
        <h1>📩 Ofertas</h1>
        <nav class="admin-nav">
            <a href="dashboard.php" class="btn btn-sm">Dashboard</a>
            <a href="items.php" class="btn btn-sm">Artículos</a>
            <a href="offers.php" class="btn btn-sm active">Ofertas</a>
            <a href="settings.php" class="btn btn-sm">Configuración</a>
            <a href="logout.php" class="btn btn-sm btn-outline">Salir</a>
        </nav>
    </header>

    <main>
        <h2>Pendientes (<?= count($pendingOffers) ?>)</h2>
        <?php if (empty($pendingOffers)): ?>
            <p>No hay ofertas pendientes.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>Artículo</th><th>Comprador</th><th>Monto</th><th>WhatsApp</th><th>Mensaje</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOffers as $offer): ?>
                        <tr>
                            <td><?= htmlspecialchars($offer['item_name']) ?></td>
                            <td><?= htmlspecialchars($offer['buyer_name']) ?></td>
                            <td><strong><?= formatPrice($offer['amount']) ?></strong></td>
                            <td><a href="<?= htmlspecialchars(whatsappLink($offer['contact_whatsapp'])) ?>" target="_blank"><?= htmlspecialchars($offer['contact_whatsapp']) ?></a></td>
                            <td><?= htmlspecialchars($offer['message'] ?: '-') ?></td>
                            <td class="actions">
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="offer_id" value="<?= $offer['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn-small">✅ Aprobar</button>
                                    <button type="submit" name="action" value="reject" class="btn-small btn-danger">❌ Rechazar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($approvedOffers): ?>
            <h2>Aprobadas (<?= count($approvedOffers) ?>)</h2>
            <table class="table">
                <thead>
                    <tr><th>Artículo</th><th>Comprador</th><th>Monto</th><th>WhatsApp</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($approvedOffers as $offer): ?>
                        <tr>
                            <td><?= htmlspecialchars($offer['item_name']) ?></td>
                            <td><?= htmlspecialchars($offer['buyer_name']) ?></td>
                            <td><?= formatPrice($offer['amount']) ?></td>
                            <td><a href="<?= htmlspecialchars(whatsappLink($offer['contact_whatsapp'])) ?>" target="_blank"><?= htmlspecialchars($offer['contact_whatsapp']) ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
