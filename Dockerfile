FROM php:7.2.8-fpm-alpine
COPY . /s3-guard
WORKDIR /s3-guard
ENV DB_CONNECTION sqlite
ENV DB_DATABASE /s3-guard/database.sqlite
RUN cp ./docker/php/database.sqlite /s3-guard/database.sqlite \
    && chmod +x ./docker/php/install-composer.sh \
    && sync \
    && ./docker/php/install-composer.sh \
    && composer install --no-dev
