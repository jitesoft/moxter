FROM php:7.2-apache

ENV APACHE_DOCUMENT_ROOT="/var/www/html/public" \
    COMPOSER_ALLOW_SUPERUSER="1" \
    PATH="/composer/vendor/bin:$PATH" \
    COMPOSER_NO_INTERACTION="1" \
    SMTP_SERVER="mail.example.com" \
    SMTP_PORT="587" \
    SMTP_USER="" \
    DOMAINS="/https:\/\/(.*\.)?(.*)(\.(*))/" \
    DEBUG="false" \
    APP_ENV="production" \
    EMAIL_CONSTRAINT="/(.*)@(*)/"

WORKDIR /var/www/html

RUN mkdir /composer \
    && apt-get update \
    && apt-get install -y git subversion curl --no-install-recommends && rm -r /var/lib/apt/lists/* \
    && php -r "copy('https://getcomposer.org/installer', '/tmp/setup.php'); if (hash('SHA384', file_get_contents('/tmp/setup.php')) !== trim(file_get_contents('https://composer.github.io/installer.sig'))) { echo 'Signature did not match.' . PHP_EOL; exit(1); }" \
    && php /tmp/setup.php --install-dir=/usr/bin --filename=composer \
    && rm -rf /tmp/setup.php \
    && php -v \
    && composer --version \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY ./ /var/www/html

RUN composer install --no-progress --prefer-dist --no-dev --no-suggest -o
