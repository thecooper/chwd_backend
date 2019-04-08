FROM chwd_base as build
WORKDIR /app
COPY ./ /app
RUN composer install

FROM php:7 as test
COPY --from=build /app /app
WORKDIR /app
RUN php vendor/phpunit/phpunit/phpunit tests/Unit

FROM php:7.2.16-apache as serve
WORKDIR /var/www/html
COPY --from=build /app /var/www/html/
RUN sed -i 's/var\/www\/html/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN chown -R www-data:www-data .
RUN php artisan config:clear
RUN a2enmod rewrite
EXPOSE 80
