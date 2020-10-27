FROM php:7.3-cli-stretch

MAINTAINER Thomas Sjolshagen thomas@eighty20results.com

# Docker container build time arguments with defaults
# when the --build-arg isn't supplied for that variable/argument
ARG WP_PLUGIN_NAME="e20r-single-use-trial"
ARG WP_CORE_DIR="./${WP_PLUGN_NAME}"
ARG WP_HOST="${WP_PLUGIN_NAME}.local"
ARG WP_DEPENDENCY_LIST="paid-memberships-pro pmpro-payfast"
ARG WP_ORG_USERNAME="eighty20results"
ARG DB_USER="wordpress"
ARG DB_HOST="wpdb.local"
ARG DB_NAME="wordpress"
ARG DB_PASS="wordpress"
ARG COMPOSER_MEMORY_LIMIT=-1

# For dependencies, etc
ENV WP_PLUGIN_NAME ${WP_PLUGIN_NAME}
ENV WP_CORE_DIR ${WP_CORE_DIR}
ENV WP_DEPENDENCY_LIST ${WP_DEPENDENCY_LIST}
ENV WP_ORG_USERNAME ${WP_ORG_USERNAME}

# Settings for the DB and WP (docker) stack
ENV WP_HOST ${WP_HOST}
ENV DB_USER ${DB_USER}
ENV DB_HOST ${DB_HOST}
ENV DB_NAME ${DB_NAME}
ENV DB_PASS ${DB_PASS}
ENV COMPOSER_MEMORY_LIMIT ${COMPOSER_MEMORY_LIMIT}

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
            libzip-dev \
            zip unzip \
        --no-install-recommends && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV COMPOSER_ALLOW_SUPERUSER=1

# Prepare application
WORKDIR /${WP_PLUGIN_NAME}

# Install php extensions, Add mysql driver required for wp-browser, Configure php, Install composer, add wp-cli
RUN docker-php-ext-configure zip --with-libzip=/usr/include \
		&& docker-php-ext-install zip \
		&& docker-php-ext-install bcmath gd \
		&& docker-php-ext-install -j$(nproc) iconv \
        && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
        && docker-php-ext-install -j$(nproc) gd \
		&& docker-php-ext-install mysqli && \
	echo "date.timezone = UTC" >> /usr/local/etc/php/php.ini && \
	curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin && \
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
	mv wp-cli.phar /usr/local/bin/wp

# Install vendor
COPY ./composer.json /${WP_PLUGIN_NAME}/composer.json
RUN composer update --prefer-dist --optimize-autoloader

# Add source-code
COPY . /${WP_PLUGIN_NAME}
WORKDIR /project

ADD .circleci/bin/codeception-container-entrypoint.sh /

RUN ["chmod", "+x", "/codeception-container-entrypoint.sh"]
