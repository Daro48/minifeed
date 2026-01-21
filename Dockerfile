FROM php:8.2-apache

# PHP Extensions
RUN docker-php-ext-install pdo pdo_mysql

# Apache Modules
RUN a2enmod rewrite
RUN a2enmod headers

RUN apt-get update && apt-get install -y ffmpeg

# PHP Upload Limits
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Apache config
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Apache Zugriff korrekt setzen
RUN sed -i 's#/var/www/#/var/www/html/#g' /etc/apache2/apache2.conf

# Upload-Verzeichnisse (RICHTIGER PFAD)
RUN mkdir -p /var/www/html/public/uploads/images
RUN mkdir -p /var/www/html/public/uploads/videos
RUN mkdir -p /var/www/html/public/uploads/original

# Rechte
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
