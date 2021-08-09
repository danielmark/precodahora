FROM php:7.3-apache

RUN docker-php-ext-install mysqli

RUN apt-get update -y && apt-get install -y sendmail libpng-dev libzip-dev

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev

RUN docker-php-ext-install mbstring

RUN docker-php-ext-install zip

RUN docker-php-ext-install gd

#install xdebug
#RUN pecl install xdebug

#composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer