FROM jitesoft/composer:latest AS build
COPY ./ /app
RUN composer install --no-progress --prefer-dist --no-dev --no-suggest -o

FROM jitesoft/php-fpm:latest
LABEL maintainer="Johannes Tegnér <johannes@jitesoft.com>"
ENV LOG_FILE="/dev/stdout" \
    SMTP_SERVER="mail.example.com" \
    SMTP_PORT="587" \
    SMTP_USER="user" \
    DOMAINS="/https:\/\/(.*\.)?(.*)(\.(*))/" \
    DEBUG="false" \
    APP_ENV="production" \
    EMAIL_CONSTRAINT="/(.*)@(*)/"

COPY --from=build /app /app
