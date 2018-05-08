FROM alpine:latest
MAINTAINER Marc Richter <mail@marc-richter.info>

ENV GID=991 UID=991 VERSION=3.2 DBHOST=dbhost DBUSER=postfix DBNAME=postfix DBS=mysqli

RUN apk update ; apk upgrade apk-tools
RUN echo "@commuedge http://nl.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories \
 && apk -U add \
    git \
    nginx \
    php7-fpm \
    php7-imap \
    php7-mbstring \
    php7-mysqli \
    php7-pgsql \
    php7-phar \
    php7-session \
    dovecot \
    supervisor \
    tini@commuedge \
  && rm -f /var/cache/apk/*

RUN mkdir -p /etc/supervisor.d /postfixadmin ; git clone https://github.com/postfixadmin/postfixadmin.git /postfixadmin \
 ; cd /postfixadmin ; git checkout tags/postfixadmin-${VERSION} ; rm -rf /postfixadmin/.git ; cd ${OLDPWD} \
 ; mkdir -p /postfixadmin/templates_c

COPY config.local.php /postfixadmin/config.local.php
COPY nginx.conf /etc/nginx/nginx.conf
COPY php-fpm.conf /etc/php7/php-fpm.conf
COPY postfixadmin.ini /etc/supervisor.d/postfixadmin.ini
COPY startup /usr/local/bin/startup

RUN chmod +x /usr/local/bin/startup ; \
     chown ${UID}:${GID} -R /etc/supervisor.d /postfixadmin

EXPOSE 80

CMD ["tini","--","startup"]
