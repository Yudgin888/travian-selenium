FROM php:8.1.13-fpm-alpine3.15

ENV TERM="xterm" \
    LANG="C.UTF-8" \
    LC_ALL="C.UTF-8"
ENV DOCKER_CONF_HOME=/opt/docker/
ENV APPLICATION_USER=dev-user \
    APPLICATION_GROUP=application \
    APPLICATION_PATH=/home/application \
    APPLICATION_UID=1000 \
    APPLICATION_GID=1000

COPY conf/ /etc/

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

RUN apk add --no-cache gcc libc-dev make openssl-dev pcre-dev zlib-dev linux-headers curl gnupg1 libxslt-dev gd-dev geoip-dev perl-dev autoconf libtool automake git g++ cmake sudo patch file libuuid imagemagick-dev supervisor openssh ca-certificates openssl libwebp-tools sshpass go aom-dev imagemagick jpegoptim optipng pngquant git wget vim nano less tree bash-completion mariadb-client libzip libzip-dev gcompat && apk add --no-cache --virtual .gettext gettext

RUN addgroup -g $APPLICATION_GID $APPLICATION_GROUP \
    && echo "%$APPLICATION_GROUP ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/$APPLICATION_GROUP \
    && adduser -h /home/application -D -u $APPLICATION_UID -s /bin/bash -G $APPLICATION_GROUP $APPLICATION_USER

STOPSIGNAL SIGQUIT

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

USER dev-user
COPY user/* /home/application/
RUN echo "source ~/bashconfig.sh" >> ~/.bashrc

USER root
RUN chown -R dev-user:application /home/application/
COPY user/* /root/
RUN mkdir -p /opt/php-libs
COPY conf/* /opt/php-libs/files/

RUN install-php-extensions pcntl pdo_mysql pdo_pgsql sockets zip exif redis amqp gd imagick uuid intl xdebug geospatial

RUN mv /opt/php-libs/files/opcache-jit.ini "$PHP_INI_DIR/conf.d/docker-php-opcache-jit.ini"
RUN cp /opt/php-libs/files/xdebug.ini "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini"

COPY conf/php.ini /usr/local/etc/php/php.ini
COPY conf/pm.conf /usr/local/etc/php-fpm.d/pm.conf
EXPOSE 9000 9003
RUN chown -R dev-user:application /var/www

WORKDIR /var/www
