#!/usr/bin/env bash
# FEBMart - entrypoint untuk Railway
# 1) Membuat config/database.local.php otomatis dari environment Railway MySQL.
# 2) Menjalankan bootstrap aplikasi (auto-migrasi tabel) sekali.
# 3) Menjamin akun default (admin & kasir) selalu ada.
set -euo pipefail

CONFIG_FILE="/var/www/html/config/database.local.php"

if [[ -n "${MYSQLHOST:-}" && -n "${MYSQLDATABASE:-}" ]]; then
    cat > "$CONFIG_FILE" <<PHP
<?php
// Dibuat otomatis oleh docker-entrypoint.sh (Railway).
return [
    'host'     => '${MYSQLHOST}',
    'username' => '${MYSQLUSER:-root}',
    'password' => '${MYSQLPASSWORD:-}',
    'dbname'   => '${MYSQLDATABASE}',
];
PHP
    chown www-data:www-data "$CONFIG_FILE"

    # Bootstrap aplikasi: buat database/tabel + seed (jika belum ada).
    php -r 'require "/var/www/html/config/database.php";' >/dev/null 2>&1 || true

    # Penjamin: sisipkan akun default bila belum ada.
    php -r '
        $c = @new mysqli(getenv("MYSQLHOST"), getenv("MYSQLUSER"), getenv("MYSQLPASSWORD"), getenv("MYSQLDATABASE"));
        if ($c->connect_errno) { exit(0); }
        $c->set_charset("utf8mb4");
        $users = [
            ["Admin FEBMart", "adminfebmart", "admin@febmart.com", "adminfebmart", "admin"],
            ["kasir percobaan", "kasir01", "kasir1febmart@email.com", "kasir123", "kasir"],
        ];
        foreach ($users as $u) {
            $s = $c->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $s->bind_param("s", $u[1]); $s->execute(); $s->bind_result($n); $s->fetch(); $s->close();
            if ((int)$n === 0) {
                $h = password_hash($u[3], PASSWORD_DEFAULT);
                $i = $c->prepare("INSERT INTO users (name, username, email, password, role, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $i->bind_param("sssss", $u[0], $u[1], $u[2], $h, $u[4]); $i->execute(); $i->close();
            }
        }
        $c->close();
    '
fi

exec "$@"
