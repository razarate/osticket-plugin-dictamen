FROM php:8.2-apache
RUN docker-php-ext-install mysqli
# Copia el archivo php.ini a la ubicación correcta
COPY php.ini /usr/local/etc/php/

# Asegúrate de que el directorio existe antes de cambiar la propiedad
RUN mkdir -p /var/www/html/osticket/ && \
    chown -R www-data:www-data /var/www/html/osticket/ 
    # ejecutar después de la instalación de osticket
    #&& \ chmod 644 /var/www/html/osticket/include/ost-config.php