<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function jstr($s) {
    $s = (string)$s;
    $s = str_replace('\\', '\\\\', $s);
    $s = str_replace('"',  '\\"',  $s);
    $s = str_replace("\n", '\\n',  $s);
    $s = str_replace("\r", '\\r',  $s);
    $s = str_replace("\t", '\\t',  $s);
    return '"' . $s . '"';
}
function json_ok() {
    echo '{"ok":true}';
    exit;
}
function json_err($msg) {
    echo '{"ok":false,"error":' . jstr($msg) . '}';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_err('Method not allowed.');
}

$RECIPIENT = 'info@furutec.com.my';
$FROM_ADDR = 'noreply@furutec.com.my';
$SITE_NAME = 'Furutec Website';

function clean_line($v) {
    $v = trim((string)$v);
    $v = str_replace(array("\r", "\n", "\t"), ' ', $v);
    $out = preg_replace('/\s+/', ' ', $v);
    return is_string($out) ? $out : '';
}
function clean_block($v) {
    $v = trim((string)$v);
    return str_replace(array("\r\n", "\r"), "\n", $v);
}
function pick($arr, $k, $max) {
    return isset($arr[$k]) && is_string($arr[$k]) ? substr($arr[$k], 0, $max) : '';
}

if (pick($_POST, 'website', 200) !== '') {
    json_ok();
}

$topic    = clean_line(pick($_POST, 'topic', 100));
$fullName = clean_line(pick($_POST, 'fullName', 150));
$email    = clean_line(pick($_POST, 'email', 200));
$phone    = clean_line(pick($_POST, 'phone', 60));
$country  = clean_line(pick($_POST, 'country', 100));
$message  = clean_block(pick($_POST, 'message', 5000));

$errors = array();
if ($topic === '')    { $errors[] = 'Topic is required.'; }
if ($fullName === '') { $errors[] = 'Full name is required.'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'A valid corporate email is required.'; }
if ($phone === '')    { $errors[] = 'Phone number is required.'; }
if ($country === '')  { $errors[] = 'Country is required.'; }
if ($message === '')  { $errors[] = 'Project description is required.'; }

if (!empty($errors)) {
    json_err(implode(' ', $errors));
}

$subject = clean_line('[Furutec Enquiry] ' . $topic . ' - ' . $fullName);

$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 250) : 'unknown';

$bodyLines = array(
    'New enquiry received via furutec.com.my',
    'Submitted: ' . date('Y-m-d H:i:s') . ' (' . date_default_timezone_get() . ')',
    '-----------------------------------------',
    'Topic:        ' . $topic,
    'Full name:    ' . $fullName,
    'Email:        ' . $email,
    'Phone:        ' . $phone,
    'Country:      ' . $country,
    '-----------------------------------------',
    'Project description:',
    '',
    $message,
    '',
    '-----------------------------------------',
    'IP: ' . $ip,
    'User-agent: ' . $ua,
);
$body = implode("\r\n", $bodyLines);

$headers = array(
    'From: ' . $SITE_NAME . ' <' . $FROM_ADDR . '>',
    'Reply-To: ' . $fullName . ' <' . $email . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: Furutec-Website/1.0',
);

$sent = @mail($RECIPIENT, $subject, $body, implode("\r\n", $headers), '-f ' . $FROM_ADDR);

if (!$sent) {
    json_err('Mail server rejected the message. Please email info@furutec.com.my directly.');
}

json_ok();
