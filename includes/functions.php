<?php
require_once __DIR__ . '/db.php';

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function getSetting(string $key): string {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['setting_value'] ?? '';
}

function updateSetting(string $key, string $value): void {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();
}

function getItems(string $status = 'disponible'): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM items WHERE status = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getItem(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM items WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

function getOffers(int $itemId, string $status = 'aprobada'): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM offers WHERE item_id = ? AND status = ? ORDER BY amount DESC");
    $stmt->bind_param('is', $itemId, $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllOffers(?string $status = null): array {
    $db = getDB();
    if ($status) {
        $stmt = $db->prepare("SELECT o.*, i.name AS item_name FROM offers o JOIN items i ON o.item_id = i.id WHERE o.status = ? ORDER BY o.created_at DESC");
        $stmt->bind_param('s', $status);
    } else {
        $stmt = $db->prepare("SELECT o.*, i.name AS item_name FROM offers o JOIN items i ON o.item_id = i.id ORDER BY o.created_at DESC");
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function createItem(string $name, string $description, float $normalPrice, ?float $rebaja1, ?float $rebaja2, ?string $image): int {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO items (name, description, normal_price, rebaja1_price, rebaja2_price, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssddds', $name, $description, $normalPrice, $rebaja1, $rebaja2, $image);
    $stmt->execute();
    return $db->insert_id;
}

function updateItem(int $id, string $name, string $description, float $normalPrice, ?float $rebaja1, ?float $rebaja2, ?string $image): void {
    $db = getDB();
    if ($image !== null) {
        $stmt = $db->prepare("UPDATE items SET name=?, description=?, normal_price=?, rebaja1_price=?, rebaja2_price=?, image=? WHERE id=?");
        $stmt->bind_param('ssdddsi', $name, $description, $normalPrice, $rebaja1, $rebaja2, $image, $id);
    } else {
        $stmt = $db->prepare("UPDATE items SET name=?, description=?, normal_price=?, rebaja1_price=?, rebaja2_price=? WHERE id=?");
        $stmt->bind_param('ssdddi', $name, $description, $normalPrice, $rebaja1, $rebaja2, $id);
    }
    $stmt->execute();
}

function deleteItem(int $id): void {
    $db = getDB();
    $item = getItem($id);
    if ($item && $item['image']) {
        $path = UPLOAD_DIR . $item['image'];
        if (file_exists($path)) unlink($path);
    }
    $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

function addOffer(int $itemId, string $buyerName, float $amount, string $message, string $contactWhatsapp): void {
    $db = getDB();
    $autoApprove = getSetting('auto_approve_offers');
    $status = ($autoApprove === '1') ? 'aprobada' : 'pendiente';
    $stmt = $db->prepare("INSERT INTO offers (item_id, buyer_name, amount, message, contact_whatsapp, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isdsss', $itemId, $buyerName, $amount, $message, $contactWhatsapp, $status);
    $stmt->execute();
}

function approveOffer(int $id): void {
    $db = getDB();
    $stmt = $db->prepare("UPDATE offers SET status = 'aprobada' WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

function rejectOffer(int $id): void {
    $db = getDB();
    $stmt = $db->prepare("UPDATE offers SET status = 'rechazada' WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

function getMeetingPoints(): array {
    $db = getDB();
    $result = $db->query("SELECT * FROM meeting_points WHERE is_active = 1 ORDER BY id");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateMeetingPoint(int $id, string $name, string $address, string $notes, float $lat, float $lng): void {
    $db = getDB();
    $stmt = $db->prepare("UPDATE meeting_points SET name=?, address=?, notes=?, lat=?, lng=? WHERE id=?");
    $stmt->bind_param('sssddi', $name, $address, $notes, $lat, $lng, $id);
    $stmt->execute();
}

function uploadImage(array $file): string {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        throw new Exception('Tipo de imagen no permitido. Usa JPG, PNG o WebP.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('La imagen no debe superar 5MB.');
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . $ext;
    $dest = UPLOAD_DIR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new Exception('Error al subir la imagen.');
    }
    return $filename;
}

function formatPrice(float $price): string {
    return '$' . number_format($price, 2);
}

function whatsappLink(string $phone, string $message = ''): string {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $url = "https://wa.me/{$phone}";
    if ($message) {
        $url .= '?text=' . urlencode($message);
    }
    return $url;
}

function signalLink(string $phone): string {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return "https://signal.me/#p/+{$phone}";
}
