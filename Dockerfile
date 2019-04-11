ARG IMAGE=chwd_back_base
FROM $IMAGE as base

FROM php:7.2.16-apache as serve
WORKDIR /var/www/html
RUN sed -i 's/var\/www\/html/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite
COPY --from=base --chown=www-data:www-data /app /var/www/html/
RUN php artisan config:clear
EXPOSE 80
