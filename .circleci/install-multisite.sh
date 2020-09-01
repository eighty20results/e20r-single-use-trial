#!/usr/bin/env bash

if [[ -z "$CIRCLECI" ]]; then
    echo "This script can only be run by CircleCI. Aborting." 1>&2
    exit 1
fi

if [[ -z "$WP_CORE_DIR" ]]; then
    echo "WordPress core directory isn't set. Aborting." 1>&2
    exit 1
fi

if [[ -z "$WP_HOST" ]]; then
    echo "WordPress host isn't set. Aborting." 1>&2
    exit 1
fi

if [[ -z "$WP_ORG_PLUGIN_NAME" ]]; then
    echo "WordPress.org plugin name not set. Aborting." 1>&2
    exit 1
fi

# Install WordPress
inc/bin/wp config create --path="${WP_CORE_DIR}" --dbhost="${DB_HOST}" --dbname="${DB_NAME}" --dbuser="${DB_USER} --dbpass=${DB_PASS}"
inc/bin/wp db import --path="${WP_CORE_DIR}" --dbuser="${DB_USER}" --dbpass="${DB_PASS}" --dbhost="${DB_HOST}" --dbname="${DB_NAME}" tests/database/default_db.sql
inc/bin/wp core multisite-install --path="${WP_CORE_DIR}" --url="http://${WP_HOST}" --title="Plugin Tests" --admin_user="admin" --admin_password="admin" --admin_email="thomas@eighty20results.com"
inc/bin/wp rewrite structure --path="${WP_CORE_DIR}" '/%postname%/'

# Install and activate Paid Memberships Pro
inc/bin/wp plugin install --path="${WP_CORE_DIR}" ${PMPRO_PLUGIN} --activate

# Copy our plugin to WordPress directory
cp -r ./ ${WP_CORE_DIR}/wp-content/plugins/${WP_ORG_PLUGIN_NAME}

# Activate our plugin
inc/bin/wp plugin activate --network --path="${WP_CORE_DIR}" ${WP_ORG_PLUGIN_NAME}
