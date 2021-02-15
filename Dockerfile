FROM ubuntu:20.04 as base
WORKDIR /
EXPOSE 443


FROM php:7.4-apache as build
WORKDIR /


ENV CONFPATH = ""

RUN apt-get update
RUN apt install default-mysql-client -y
RUN apt install nano

RUN apt-get update \
    && apt-get install -y git
RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/default.key -out /etc/ssl/certs/default.pem -subj "/C=IL/ST=TelAviv/L=Raanana/O=Security/OU=Development/CN=bodokhp@gmail.com"
RUN docker-php-ext-install mysqli

# RUN apt install -y ssl-cert

RUN a2enmod ssl
# RUN a2ensite default-ssl.conf


COPY / /var/www/
COPY ./conf/default.conf /etc/apache2/sites-available
COPY ./conf/default-ssl.conf /etc/apache2/sites-available

RUN a2ensite default-ssl

RUN chown -R www-data:www-data /var/www
RUN chmod 666 /var/www/machine/core/Configurations.php

CMD ["/usr/sbin/apachectl", "-D", "FOREGROUND"]
