<?php
// Furutec Editor — authentication configuration.
//
// *** THIS IS THE DEMO / TEST CONFIGURATION (the GitHub copy). ***
// The GitHub repository is the test product: the editor signs in with the
// demo account below and the login form pre-fills it, so a reviewer can
// just click "Sign in".  The LIVE site keeps its own config/config.php on
// the server with a real password and demo_mode off — that file is never
// committed and must never be overwritten by this one.

return array(
    'admin_user'            => 'demo',
    // bcrypt of the demo password below.
    'admin_pass_hash'       => '$2y$10$ibCHlgylBgeyJ.cb//eq3OKwGBtyPqeVzdHs..MnGDoCovUo3HmqO',
    'must_change_password'  => false,
    'session_lifetime'      => 3600,   // 1 hour of inactivity
    'session_name'          => 'FURUTEC_EDITOR',
    'csrf_key'              => '7be203ed94f892d6c447298f7bd4f877',
    'max_login_attempts'    => 5,
    'lockout_seconds'       => 300,    // 5 min after too many failures

    // When false, Publish is disabled — the UI button is greyed out and
    // publish.php refuses even direct API calls.
    'publishing_enabled'    => true,

    // Demo mode: the login form is pre-filled with the credentials below and
    // shows a "test site" notice.  Leave these out (or false) on the live site.
    'demo_mode'             => true,
    'demo_password'         => 'furutec-demo',
);
