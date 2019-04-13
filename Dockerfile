FROM php:7 as build
ARG TWITTER_API_KEY=12345
ARG TWITTER_API_SECRET=12345
ARG TWITTER_ACCESS_TOKEN=12345
ARG TWITTER_ACCESS_TOKEN_SECRET=12345
ARG NEWS_API_KEY=12345
ARG GEOCODIO_API_KEY=12345
ARG DB_USERNAME=chwd
ARG DB_PASSWORD=letmein
ARG DB_HOST=172.17.0.3
ARG ELECTION_NEWS_IMPORT_COUNT_PER_DAY=250
ARG ELECTION_NEWS_DRY_RUN=false
ARG BALLOTPEDIA_IMPORT_DIR=/imports
ARG BALLOTPEDIA_IMPORT_LIMIT=6000

ENV TWITTER_API_KEY=$TWITTER_API_KEY \
  TWITTER_API_SECRET=$TWITTER_API_SECRET \
  TWITTER_ACCESS_TOKEN=$TWITTER_ACCESS_TOKEN \
  TWITTER_ACCESS_TOKEN_SECRET=$TWITTER_ACCESS_TOKEN_SECRET \
  NEWS_API_KEY=$NEWS_API_KEY \
  GEOCODIO_API_KEY=$GEOCODIO_API_KEY \
  DB_USERNAME=$DB_USERNAME \
  DB_PASSWORD=$DB_PASSWORD \
  DB_HOST=$DB_HOST \
  ELECTION_NEWS_IMPORT_COUNT_PER_DAY=$ELECTION_NEWS_IMPORT_COUNT_PER_DAY \
  ELECTION_NEWS_DRY_RUN=$ELECTION_NEWS_DRY_RUN \
  BALLOTPEDIA_IMPORT_DIR=$BALLOTPEDIA_IMPORT_DIR \
  BALLOTPEDIA_IMPORT_LIMIT=$BALLOTPEDIA_IMPORT_LIMIT

RUN apt-get update -y && apt-get install -y libmcrypt-dev openssl git unzip libzip-dev
RUN pecl install mcrypt-1.0.2
RUN docker-php-ext-enable mcrypt && docker-php-ext-install pdo_mysql zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY ./ .
COPY ./scripts/import_ballotpedia .
RUN composer install

FROM php:7.2.16-apache as serve
WORKDIR /var/www/html
RUN sed -i 's/var\/www\/html/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite
COPY --from=build --chown=www-data:www-data /app /var/www/html/
RUN php artisan config:clear
EXPOSE 80
