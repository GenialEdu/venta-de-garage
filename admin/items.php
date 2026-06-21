<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $normalPrice = (float)($_POST['normal_price'] ?? 0);
    $rebaja1 = $_POST['rebaja1_price'] !== '' ? (float)$_POST['rebaja1_price'] : null;
    $rebaja2 = $_POST['rebaja2_price'] !== '' ? (float)$_POST['rebaja2_price'] : null;
    $imageFile = $_FILES['image'] ?? null;

    if (!$name || $normalPrice <= 0) {
        $error = 'Nombre y precio normal son obligatorios.';
    } else {
        try {
            $imageName = null;
            if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
                $imageName = uploadImage($imageFile);
            }

            if ($action === 'add') {
                createItem($name, $description, $normalPrice, $rebaja1, $rebaja2, $imageName);
                $message = 'Artículo creado.';
                $action = 'list';
            } elseif ($action === 'edit' && $editId) {
                $existing = getItem($editId);
                if ($imageName === null) {
                    $imageName = $existing['image'] ?? null;
                } else {
                    if ($existing['image']) {
                        $oldPath = UPLOAD_DIR . $existing['image'];
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                }
                updateItem($editId, $name, $description, $normalPrice, $rebaja1, $rebaja2, $imageName);
                $message = 'Artículo actualizado.';
                $action = 'list';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

if ($action === 'delete' && $editId) {
    deleteItem($editId);
    header('Location: items.php');
    exit;
}

if ($action === 'toggle' && $editId) {
    $item = getItem($editId);
    if ($item) {
        $newStatus = $item['status'] === 'disponible' ? 'vendido' : 'disponible';
        $db = getDB();
        $stmt = $db->prepare("UPDATE items SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $newStatus, $editId);
        $stmt->execute();
    }
    header('Location: items.php');
    exit;
}

$editItem = ($action === 'edit' && $editId) ? getItem($editId) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Artículos - Admin</title>
<link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <header class="header">
        <h1>📦 Artículos</h1>
        <nav class="admin-nav">
            <a href="dashboard.php" class="btn btn-sm">Dashboard</a>
            <a href="items.php" class="btn btn-sm active">Artículos</a>
            <a href="offers.php" class="btn btn-sm">Ofertas</a>
            <a href="settings.php" class="btn btn-sm">Configuración</a>
            <a href="logout.php" class="btn btn-sm btn-outline">Salir</a>
        </nav>
    </header>

    <main>
        <?php if ($message): ?><p class="success-msg"><?= htmlspecialchars($message) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error-msg"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <?php if ($action === 'add' || $editItem): ?>
            <h2><?= $editItem ? 'Editar' : 'Agregar' ?> artículo</h2>
            <form method="post" enctype="multipart/form-data" class="item-form">
                <label>Nombre *
                    <input type="text" name="name" required value="<?= htmlspecialchars($editItem['name'] ?? '') ?>">
                </label>
                <label>Descripción
                    <textarea name="description" rows="4"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                </label>
                <label>Precio Normal *
                    <input type="number" name="normal_price" step="0.01" min="1" required value="<?= htmlspecialchars($editItem['normal_price'] ?? '') ?>">
                </label>
                <label>Rebaja 1 (opcional)
                    <input type="number" name="rebaja1_price" step="0.01" min="0" value="<?= htmlspecialchars($editItem['rebaja1_price'] ?? '') ?>">
                </label>
                <label>Rebaja 2 (opcional)
                    <input type="number" name="rebaja2_price" step="0.01" min="0" value="<?= htmlspecialchars($editItem['rebaja2_price'] ?? '') ?>">
                </label>
                <label>Imagen
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                    <?php if ($editItem && $editItem['image']): ?>
                        <br><img src="../assets/uploads/<?= htmlspecialchars($editItem['image']) ?>" alt="" class="thumb">
                    <?php endif; ?>
                </label>
                <div class="form-actions">
                    <button type="submit" class="btn"><?= $editItem ? 'Actualizar' : 'Agregar' ?></button>
                    <a href="items.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        <?php else: ?>
            <div class="toolbar">
                <a href="items.php?action=add" class="btn">+ Nuevo artículo</a>
            </div>
            <?php
            $allItems = getItems();
            $soldItems = getItems('vendido');
            ?>
            <h2>Disponibles (<?= count($allItems) ?>)</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Rebaja 1</th>
                        <th>Rebaja 2</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allItems)): ?>
                        <tr><td colspan="6">No hay artículos.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allItems as $item): ?>
                            <tr>
                                <td><?php if ($item['image']): ?><img src="../assets/uploads/<?= htmlspecialchars($item['image']) ?>" alt="" class="thumb"><?php endif; ?></td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= formatPrice($item['normal_price']) ?></td>
                                <td><?= $item['rebaja1_price'] ? formatPrice($item['rebaja1_price']) : '-' ?></td>
                                <td><?= $item['rebaja2_price'] ? formatPrice($item['rebaja2_price']) : '-' ?></td>
                                <td class="actions">
                                    <a href="items.php?action=edit&id=<?= $item['id'] ?>" class="btn-small">Editar</a>
                                    <a href="items.php?action=toggle&id=<?= $item['id'] ?>" class="btn-small btn-warning" onclick="return confirm('¿Marcar como vendido?')">Vendido</a>
                                    <a href="items.php?action=delete&id=<?= $item['id'] ?>" class="btn-small btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($soldItems): ?>
                <h2>Vendidos (<?= count($soldItems) ?>)</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($soldItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= formatPrice($item['normal_price']) ?></td>
                                <td>
                                    <a href="items.php?action=toggle&id=<?= $item['id'] ?>">Reactivar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
