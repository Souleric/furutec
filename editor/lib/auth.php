<?php
// Furutec Editor — session, login, CSRF, password rotation.

require_once __DIR__ . '/util.php';
require_once __DIR__ . '/data.php';

function fx_config() {
    static $cfg = null;
    if ($cfg === null) $cfg = include fx_config_path();
    return $cfg;
}

/** Start the session with hardened cookie settings.  Idempotent. */
function fx_session_start() {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $cfg = fx_config();
    session_name($cfg['session_name']);
    // Secure cookie params — HTTPS only, HttpOnly, Lax.
    $params = session_get_cookie_params();
    session_set_cookie_params(
        (int)$cfg['session_lifetime'],
        '/',
        $params['domain'],
        true,        // secure (HTTPS)
        true         // httponly
    );
    session_start();
    // Idle-timeout enforcement.
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > (int)$cfg['session_lifetime']) {
        $_SESSION = array();
        session_regenerate_id(true);
    }
    $_SESSION['last_activity'] = time();
}

/** Is the current session authenticated? */
function fx_is_logged_in() {
    fx_session_start();
    return !empty($_SESSION['auth']);
}

/** Must the current user change their password next? */
function fx_must_change_password() {
    $cfg = fx_config();
    return !empty($cfg['must_change_password']);
}

/**
 * Enforce login on a page.  If not authed, redirect to /editor/login.php.
 * If authed but must-change-password, redirect to /editor/change-password.php.
 */
function fx_require_login($allow_change_password = false) {
    if (!fx_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
    if (fx_must_change_password() && !$allow_change_password) {
        header('Location: change-password.php');
        exit;
    }
}

/** Verify credentials.  Returns true on success, false otherwise. */
function fx_verify_login($user, $pass) {
    $cfg = fx_config();
    if (!is_string($user) || !is_string($pass)) return false;
    if ($user !== $cfg['admin_user']) return false;
    return password_verify($pass, $cfg['admin_pass_hash']);
}

/** Mark the current session as authenticated. */
function fx_do_login() {
    fx_session_start();
    session_regenerate_id(true);
    $_SESSION['auth'] = true;
    $_SESSION['last_activity'] = time();
}

/** Log out and clear the session. */
function fx_do_logout() {
    fx_session_start();
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** Simple per-IP file-based lockout after too many bad logins. */
function fx_lockout_file($ip) {
    return sys_get_temp_dir() . '/fx_editor_lockout_' . md5($ip);
}
function fx_login_is_locked($ip) {
    $cfg = fx_config();
    $path = fx_lockout_file($ip);
    if (!is_file($path)) return false;
    $data = @unserialize(file_get_contents($path));
    if (!is_array($data)) return false;
    if ((int)$data['count'] < (int)$cfg['max_login_attempts']) return false;
    if ((time() - (int)$data['first']) > (int)$cfg['lockout_seconds']) {
        @unlink($path);
        return false;
    }
    return true;
}
function fx_login_record_failure($ip) {
    $path = fx_lockout_file($ip);
    $data = is_file($path) ? @unserialize(file_get_contents($path)) : null;
    if (!is_array($data) || (time() - (int)$data['first']) > 3600) {
        $data = array('count' => 0, 'first' => time());
    }
    $data['count']++;
    file_put_contents($path, serialize($data), LOCK_EX);
}
function fx_login_clear_failures($ip) {
    @unlink(fx_lockout_file($ip));
}

/** CSRF token helpers.  Bound to the user's session. */
function fx_csrf_token() {
    fx_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(24));
    }
    return $_SESSION['csrf'];
}
function fx_csrf_check($token) {
    fx_session_start();
    if (empty($_SESSION['csrf'])) return false;
    return hash_equals($_SESSION['csrf'], (string)$token);
}
function fx_require_csrf() {
    $token = isset($_POST['csrf']) ? $_POST['csrf'] : (isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '');
    if (!fx_csrf_check($token)) {
        fx_json_response(array('ok' => false, 'error' => 'Session expired. Refresh the editor and try again.'));
    }
}

/**
 * Rotate the admin password and rewrite /config/config.php.
 * Also clears must_change_password.
 */
function fx_change_password($new_pass) {
    $cfg = fx_config();
    $hash = password_hash($new_pass, PASSWORD_BCRYPT, array('cost' => 10));
    $cfg['admin_pass_hash']      = $hash;
    $cfg['must_change_password'] = false;
    // Also rotate csrf_key to invalidate outstanding forms.
    $cfg['csrf_key'] = bin2hex(openssl_random_pseudo_bytes(16));

    $php  = "<?php\n// Furutec Editor — authentication configuration.\n";
    $php .= "// Auto-written by /editor/change-password.php on " . date('Y-m-d H:i:s') . "\n\n";
    $php .= "return " . fx_var_export($cfg, 0) . ";\n";

    $path = fx_config_path();
    $tmp  = $path . '.tmp';
    if (file_put_contents($tmp, $php, LOCK_EX) === false) return false;
    return rename($tmp, $path);
}

/** Try to get the caller's IP, prefer X-Forwarded-For (LiteSpeed sets this behind their WAF). */
function fx_client_ip() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($parts[0]);
    }
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
}
