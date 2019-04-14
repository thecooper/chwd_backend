FROM php:7.2.16-apache

ARG APP_ENV=local
ARG APP_URL=http://localhost
ARG APP_DEBUG=true
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

ENV APP_NAME="Change How We Engage" \
  APP_ENV=$APP_ENV \
  APP_URL=$APP_URL \
  APP_DEBUG=$APP_DEBUG \
  TWITTER_API_KEY=$TWITTER_API_KEY \
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

WORKDIR /var/www/html
RUN apt-get install awscli
COPY --chown=www-data:www-data ./ .
RUN sed -i 's/var\/www\/html/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite
RUN php artisan config:clear
EXPOSE 80
