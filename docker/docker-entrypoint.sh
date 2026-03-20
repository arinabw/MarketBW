#!/bin/sh
set -e
# Тома с хоста часто root:root — PHP-FPM (www-data) не может писать в SQLite.
for d in /var/www/data /var/www/images; do
  if [ -d "$d" ]; then
    chown -R www-data:www-data "$d" 2>/dev/null || true
    chmod -R u+rwx "$d" 2>/dev/null || true
  fi
done
exec /usr/bin/supervisord -c /etc/supervisord.conf
