# FEBMart - Dockerfile untuk Railway v3
# Strategi: PHP 8.2 CLI + built-in server (php -S).
# Lebih ringan & stabil daripada Apache (menghindari masalah MPM di image baru).
FROM php:8.2-cli

# Ekstensi database yang dipakai FEBMart (mysqli + pdo_mysql)
RUN docker-php-ext-install mysqli pdo_mysql

WORKDIR /var/www/html
COPY . /var/www/html/

# Entrypoint: mengisi config DB dari environment Railway sebelum server jalan
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
