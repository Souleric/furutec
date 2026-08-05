<?php
// Furutec Editor — authentication configuration.
// This file lives outside the public-servable path via .htaccess deny.
// If you rotate the password, update the hash below (bcrypt).

return array(
    'admin_user'            => 'admin',
    // Default password: changeme123
    // On first login the editor forces a password change and rewrites this file.
    'admin_pass_hash'       => '$2y$10$XNt7hj5/POtnjkfsA9ENWeCCAzGKaQ.DZad8I0k77zBr.ybH/6Xau',
    'must_change_password'  => true,
    'session_lifetime'      => 3600,   // 1 hour of inactivity
    'session_name'          => 'FURUTEC_EDITOR',
    'csrf_key'              => 'change-this-any-random-string-once-in-production-9k2fZ',
    'max_login_attempts'    => 5,
    'lockout_seconds'       => 300,    // 5 min after too many failures
);
