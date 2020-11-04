FROM ubuntu:18.04

ENV TZ=UTC

#timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get -y upgrade \
    && apt-get -y install curl sudo git apt-transport-https ca-certificates nano mc wget

#php
RUN apt-get update \
    && apt-get -y install software-properties-common \
    && add-apt-repository ppa:ondrej/php

RUN apt-get update && apt-get -y install php7.4-fpm \
    php7.4-cli \
    php7.4-pgsql \
    php7.4-mbstring \
    php7.4-redis \
    php7.4-dom \
    php7.4-memcached \
    php7.4-zip \
    php7.4-gd \
    php7.4-soap \
    php7.4-bcmath \
    php7.4-curl

# composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Supervisor pid 1
RUN apt-get update && apt-get install -y supervisor \
    && mkdir -p /var/log/supervisor
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
CMD ["/usr/bin/supervisord"]

COPY init.sh /init.sh

#####################################
#             Crontab               #
#####################################
RUN apt-get update \
    && apt-get install -y cron \
    && mkfifo --mode 0666 /var/log/cron.log

ADD config/schedule /etc/cron.d/schedule

RUN chmod 0644 /etc/cron.d/schedule \
    && touch /var/log/cron.log  \
    && crontab /etc/cron.d/schedule

