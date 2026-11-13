# FEBMart - Dockerfile untuk Railway
# Basis: PHP 8.2 + Apache (sesuai syarat README: PHP >= 8.2)
FROM php:8.2-apache

# Ekstensi database yang dipakai FEBMart (mysqli + pdo_mysql)
RUN docker-php-ext-install mysqli pdo_mysql

# Salin seluruh kode aplikasi ke DocumentRoot Apache
COPY . /var/www/html/

# Pastikan direktori config & assets bisa ditulis proses web server
# (dipakai untuk config/database.local.php dan upload gambar produk/user)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R u+rwX,go+rX /var/www/html

# Entrypoint: mengisi config DB dari environment Railway sebelum Apache jalan
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
