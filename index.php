<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = h(getSetting('page_title') ?: 'Venta de Garage');
$pageDesc = h(getSetting('page_description'));
$phoneWA = h(getSetting('phone_whatsapp'));
$phoneSignal = h(getSetting('phone_signal'));
$items = getItems();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title><?= $pageTitle ?></title>
<link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<div class="container">
    <header class="header">
        <h1><?= $pageTitle ?></h1>
        <?php if ($pageDesc): ?><p class="desc"><?= $pageDesc ?></p><?php endif; ?>
        <div class="contact-btns">
            <?php if ($phoneWA): ?>
                <a href="<?= h(whatsappLink($phoneWA)) ?>" target="_blank" class="btn btn-wa">WhatsApp</a>
            <?php endif; ?>
            <?php if ($phoneSignal): ?>
                <a href="<?= h(signalLink($phoneSignal)) ?>" target="_blank" class="btn btn-signal">Signal</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="delivery-note">
        <p>📍 Solo entrega local en la ciudad. No enviamos por paquetería.</p>
        <p><a href="entrega.php">Ver puntos de encuentro</a></p>
    </section>

    <main class="catalog">
        <?php if (empty($items)): ?>
            <p class="empty">Próximamente publicaremos artículos. ¡Vuelve pronto!</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <article class="item-card">
                    <?php if ($item['image']): ?>
                        <div class="item-img">
                            <img src="assets/uploads/<?= h($item['image']) ?>" alt="<?= h($item['name']) ?>" loading="lazy">
                        </div>
                    <?php endif; ?>
                    <div class="item-body">
                        <h2><?= h($item['name']) ?></h2>
                        <?php if ($item['description']): ?>
                            <p class="item-desc"><?= nl2br(h($item['description'])) ?></p>
                        <?php endif; ?>
                        <div class="prices">
                            <div class="price normal">Normal: <strong><?= formatPrice($item['normal_price']) ?></strong></div>
                            <?php if ($item['rebaja1_price']): ?>
                                <div class="price rebaja">🔥 Rebaja 1: <strong><?= formatPrice($item['rebaja1_price']) ?></strong></div>
                            <?php endif; ?>
                            <?php if ($item['rebaja2_price']): ?>
                                <div class="price rebaja">🔥 Rebaja 2: <strong><?= formatPrice($item['rebaja2_price']) ?></strong></div>
                            <?php endif; ?>
                        </div>

                        <?php
                        $offers = getOffers($item['id']);
                        if ($offers): ?>
                            <div class="offers">
                                <h3>Ofertas recibidas</h3>
                                <ul>
                                <?php foreach ($offers as $offer): ?>
                                    <li>
                                        <strong><?= h($offer['buyer_name']) ?></strong>:
                                        <?= formatPrice($offer['amount']) ?>
                                        <?php if ($offer['message']): ?>
                                            <br><span class="offer-msg">"<?= h($offer['message']) ?>"</span>
                                        <?php endif; ?>
                                        <?php if ($phoneWA): ?>
                                            <a href="<?= h(whatsappLink($phoneWA, 'Hola, vi tu oferta de ' . formatPrice($offer['amount']) . ' por ' . $item['name'])) ?>" class="btn-small btn-wa" target="_blank">Contactar</a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="offer-form">
                            <h3>Haz una oferta</h3>
                            <form action="oferta.php" method="post">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <label>Tu nombre *<input type="text" name="buyer_name" required></label>
                                <label>Tu WhatsApp *<input type="tel" name="contact_whatsapp" required placeholder="Ej: 521234567890"></label>
                                <label>Tu oferta *<input type="number" name="amount" step="0.01" min="1" required></label>
                                <label>Mensaje (opcional)<textarea name="message" rows="2"></textarea></label>
                                <button type="submit" class="btn btn-offer">Enviar oferta</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>📱 <a href="entrega.php">Puntos de entrega</a></p>
    </footer>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
