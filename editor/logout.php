<?php
require_once __DIR__ . '/lib/auth.php';
fx_do_logout();
header('Location: login.php');
