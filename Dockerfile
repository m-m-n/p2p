FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    cron \
    supervisor \
    unzip \
    p7zip \
    git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY ./config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir /app

COPY ./p2p-src /app
COPY ./config/crontab /app/crontab
WORKDIR /app
RUN composer install
RUN crontab /app/crontab

CMD [ "/usr/bin/supervisord" ]
