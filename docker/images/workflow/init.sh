#!/usr/bin/env bash

#nginx
service nginx start

#php
service php7.4-fpm start

#cron
service cron start
