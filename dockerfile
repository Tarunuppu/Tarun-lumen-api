FROM php:7.3-fpm-alpine

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html/

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

COPY . .

RUN composer install
RUN composer require tymon/jwt-auth
#RUN composer require illuminate/mail
#RUN composer require illuminate/notifications