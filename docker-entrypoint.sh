#!/usr/bin/env bash
# FEBMart - entrypoint untuk Railway
# Membuat config/database.local.php otomatis dari environment Railway MySQL.
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
fi

exec "$@"
