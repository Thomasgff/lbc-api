FROM php:8.1.19-fpm-alpine

RUN apk add --no-cache composer php-ctype php-tokenizer php-xml php-session php-dom php-xmlwriter
RUN docker-php-ext-install pdo_mysql mysqli

COPY composer*.json ./
RUN composer install --no-dev --optimize-autoloader

WORKDIR /app
COPY . .

EXPOSE 4000

CMD ["php", "-S", "0.0.0.0:4000", "-t", "public"]
