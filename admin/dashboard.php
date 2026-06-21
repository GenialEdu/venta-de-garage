<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/functions.php';

$totalItems = count(getItems());
$totalSold = count(getItems('vendido'));
$pendingOffers = count(getAllOffers('pendiente'));
$approvedOffers = count(getAllOffers('aprobada'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Admin</title>
<link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <header class="header">
        <h1>📊 Panel de Administración</h1>
        <nav class="admin-nav">
            <a href="dashboard.php" class="btn btn-sm active">Dashboard</a>
            <a href="items.php" class="btn btn-sm">Artículos</a>
            <a href="offers.php" class="btn btn-sm">Ofertas <?= $pendingOffers ? "($pendingOffers)" : '' ?></a>
            <a href="settings.php" class="btn btn-sm">Configuración</a>
            <a href="logout.php" class="btn btn-sm btn-outline">Salir</a>
        </nav>
    </header>

    <main class="dashboard">
        <div class="stats">
            <div class="stat-card">
                <span class="stat-num"><?= $totalItems ?></span>
                <span class="stat-label">Artículos disponibles</span>
            </div>
            <div class="stat-card">
                <span class="stat-num"><?= $totalSold ?></span>
                <span class="stat-label">Vendidos</span>
            </div>
            <div class="stat-card">
                <span class="stat-num"><?= $pendingOffers ?></span>
                <span class="stat-label">Ofertas pendientes</span>
            </div>
            <div class="stat-card">
                <span class="stat-num"><?= $approvedOffers ?></span>
                <span class="stat-label">Ofertas aprobadas</span>
            </div>
        </div>

        <div class="quick-links">
            <h2>Acciones rápidas</h2>
            <a href="items.php?action=add" class="btn">+ Agregar artículo</a>
            <?php if ($pendingOffers > 0): ?>
                <a href="offers.php" class="btn btn-warning">📩 Revisar ofertas pendientes</a>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
