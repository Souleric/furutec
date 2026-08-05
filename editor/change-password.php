<?php
require_once __DIR__ . '/lib/auth.php';
fx_require_login(true);  // allow this specific page even if must_change_password

$err = '';
$ok  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!fx_csrf_check(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $err = 'Session expired. Refresh the page and try again.';
    } else {
        $new  = isset($_POST['new'])  ? (string)$_POST['new']  : '';
        $conf = isset($_POST['conf']) ? (string)$_POST['conf'] : '';
        if (strlen($new) < 10) {
            $err = 'Password must be at least 10 characters.';
        } elseif ($new !== $conf) {
            $err = 'Passwords do not match.';
        } elseif ($new === 'changeme123') {
            $err = 'Please pick something other than the default password.';
        } elseif (!fx_change_password($new)) {
            $err = 'Could not save the new password. Check that /config is writable.';
        } else {
            $ok = true;
        }
    }
}
$csrf = fx_csrf_token();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Furutec Editor · Change Password</title>
    <link rel="stylesheet" href="assets/editor.css?v=1"/>
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body class="fx-login-body">
    <form class="fx-login-card" method="post" autocomplete="off" novalidate>
        <div class="fx-login-brand">Set a new password</div>
        <div class="fx-login-sub">First-time login — please replace the default password.</div>

        <?php if ($err !== ''): ?>
        <div class="fx-alert fx-alert-error"><?= fx_escape($err) ?></div>
        <?php endif; ?>
        <?php if ($ok): ?>
        <div class="fx-alert fx-alert-ok">Password updated. <a href="index.php">Open the editor →</a></div>
        <?php else: ?>

        <label class="fx-field">
            <span class="fx-field-label">New password (10+ characters)</span>
            <input class="fx-input" type="password" name="new" required minlength="10" autofocus/>
        </label>
        <label class="fx-field">
            <span class="fx-field-label">Confirm new password</span>
            <input class="fx-input" type="password" name="conf" required minlength="10"/>
        </label>
        <input type="hidden" name="csrf" value="<?= fx_escape($csrf) ?>"/>
        <button type="submit" class="fx-btn fx-btn-primary fx-btn-block">Save new password</button>
        <?php endif; ?>

        <div class="fx-login-foot">
            <a href="logout.php">Sign out</a>
        </div>
    </form>
</body>
</html>
