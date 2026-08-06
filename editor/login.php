<?php
require_once __DIR__ . '/lib/auth.php';

if (fx_is_logged_in()) {
    if (fx_must_change_password()) { header('Location: change-password.php'); exit; }
    header('Location: index.php'); exit;
}

$err = '';
$ip  = fx_client_ip();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (fx_login_is_locked($ip)) {
        $err = 'Too many failed attempts. Try again in a few minutes.';
    } else {
        $user = isset($_POST['user']) ? trim((string)$_POST['user']) : '';
        $pass = isset($_POST['pass']) ? (string)$_POST['pass'] : '';
        if (fx_verify_login($user, $pass)) {
            fx_login_clear_failures($ip);
            fx_do_login();
            if (fx_must_change_password()) { header('Location: change-password.php'); exit; }
            header('Location: index.php'); exit;
        }
        fx_login_record_failure($ip);
        $err = 'Invalid username or password.';
    }
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Furutec Editor · Login</title>
    <link rel="stylesheet" href="assets/editor.css?v=3"/>
    <link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='18' fill='%232E3192'/><text x='50' y='72' text-anchor='middle' font-family='-apple-system,Arial,sans-serif' font-weight='800' font-size='62' fill='white'>F</text></svg>"/>
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body class="fx-login-body">
    <form class="fx-login-card" method="post" autocomplete="off" novalidate>
        <div class="fx-login-brand">Furutec Editor</div>
        <div class="fx-login-sub">Sign in to manage the website content.</div>

        <?php if ($err !== ''): ?>
        <div class="fx-alert fx-alert-error"><?= fx_escape($err) ?></div>
        <?php endif; ?>

        <label class="fx-field">
            <span class="fx-field-label">Username</span>
            <input class="fx-input" type="text" name="user" required autofocus value="<?= fx_escape(isset($_POST['user']) ? $_POST['user'] : '') ?>"/>
        </label>
        <label class="fx-field">
            <span class="fx-field-label">Password</span>
            <input class="fx-input" type="password" name="pass" required/>
        </label>
        <button type="submit" class="fx-btn fx-btn-primary fx-btn-block">Sign in</button>
        <div class="fx-login-foot">
            Trouble signing in? Contact your website administrator.
        </div>
    </form>
</body>
</html>
