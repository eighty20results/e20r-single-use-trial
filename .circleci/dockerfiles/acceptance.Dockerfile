FROM php:7.2-cli-stretch

MAINTAINER Thomas Sjolshagen thomas@eighty20results.com

ARG WP_PLUGIN_NAME="e20r-single-use-trial"

# Install required system packages
RUN apt-get update && \
    apt-get -y install \
            git \
            zlib1g-dev \
            libssl-dev \
            libfreetype6-dev \
            libjpeg62-turbo-dev \
            libpng-dev \
            mysql-client \
            sudo less \
            zip unzip \
        --no-install-recommends && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV WP_PLUGIN_NAME=${WP_PLUGIN_NAME}

# Install php extensions, Add mysql driver required for wp-browser, Configure php, Install composer, add wp-cli
RUN docker-php-ext-install \
    bcmath \
    gd \
    zip && \
	docker-php-ext-install -j$(nproc) iconv \
        && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
        && docker-php-ext-install -j$(nproc) gd && \
	docker-php-ext-install mysqli && \
	echo "date.timezone = UTC" >> /usr/local/etc/php/php.ini && \
	curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin && \
	composer global require --optimize-autoloader \
        "hirak/prestissimo" && \
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
	mv wp-cli.phar /usr/local/bin/wp

# Prepare application
WORKDIR /${WP_PLUGIN_NAME}

# Install vendor
COPY ./composer.json /${WP_PLUGIN_NAME}/composer.json
RUN composer install --prefer-dist --optimize-autoloader

# Add source-code
COPY . /${WP_PLUGIN_NAME}
WORKDIR /project

ADD bin/docker-entrypoint.sh /

RUN ["chmod", "+x", "/docker-entrypoint.sh"]
