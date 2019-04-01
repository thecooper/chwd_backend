FROM php:7 as build
RUN apt-get update -y && apt-get install -y libmcrypt-dev openssl git
RUN pecl install mcrypt-1.0.2
RUN docker-php-ext-enable mcrypt
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo_mysql
WORKDIR /app
COPY ./ /app
RUN composer install

FROM php:7.2.16-apache as serve
WORKDIR /var/www/html
COPY --from=build /app /var/www/html/
RUN sed -i 's/var\/www\/html/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN chown -R www-data:www-data .
RUN php artisan config:clear && php artisan cache:clear
RUN a2enmod rewrite
EXPOSE 80
