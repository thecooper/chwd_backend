FROM chwdback-test-base as test

COPY . /home

WORKDIR /home

RUN composer install

RUN php artisan migrate

CMD ["sh"]

# RUN npm run test