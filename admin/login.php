<?php
require_once __DIR__ . '/../includes/auth.php';
$error = '';
$captcha = generateCaptcha();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $captchaAnswer = $_POST['captcha'] ?? '';

    if (!verifyCaptcha($captchaAnswer)) {
        $error = 'Captcha incorrecto.';
        $captcha = generateCaptcha();
    } elseif (tryLogin($password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Contraseña incorrecta.';
        $captcha = generateCaptcha();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Login</title>
<link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <div class="login-box">
        <h1>🔐 Admin</h1>
        <?php if ($error): ?><p class="error-msg"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="post">
            <label>Contraseña
                <input type="password" name="password" required autofocus>
            </label>
            <label><?= htmlspecialchars($captcha['question']) ?>
                <input type="number" name="captcha" required>
            </label>
            <button type="submit" class="btn">Entrar</button>
        </form>
        <p><a href="../index.php">← Volver al catálogo</a></p>
    </div>
</div>
</body>
</html>
