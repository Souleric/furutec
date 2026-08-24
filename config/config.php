<?php
// Furutec Editor — authentication configuration.
// This file lives outside the public-servable path via .htaccess deny.
// If you rotate the password, update the hash below (bcrypt).

return array(
    'admin_user'            => 'admin',
    // Password rotated on 24 Aug 2026 — bcrypt cost 12.
    // (Plaintext is not stored in this file; ask Eric if you need it reset.)
    'admin_pass_hash'       => '$2y$12$baMxnN/gCSWV6kNV2wrhFO5PquiNN31.NaslwIx3GgtjDYpgZndom',
    'must_change_password'  => false,
    'session_lifetime'      => 3600,   // 1 hour of inactivity
    'session_name'          => 'FURUTEC_EDITOR',
    'csrf_key'              => 'change-this-any-random-string-once-in-production-9k2fZ',
    'max_login_attempts'    => 5,
    'lockout_seconds'       => 300,    // 5 min after too many failures
);
