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
echo "Create WordPress config files"
inc/bin/wp config create \
	--path="${WP_CORE_DIR}" \
	--dbhost="${DB_HOST}" \
	--dbname="${DB_NAME}" \
	--dbuser="${DB_USER}" \
	--dbpass="${DB_PASS}"

echo "Import database for WordPress"
inc/bin/wp db import \
	--path="${WP_CORE_DIR}" \
	--dbuser="${DB_USER}" \
	--dbpass="${DB_PASS}" \
	--dbhost="${DB_HOST}" \
	--dbname="${DB_NAME}" \
	/.circleci/database/default_db.sql

echo "Install/Create the WordPress test multisite"
inc/bin/wp core multisite-install \
	--path="${WP_CORE_DIR}" \
	--url="http://${WP_HOST}" \
	--title="${WP_ORG_PLUGIN_NAME} Tests" \
	--admin_user="admin" \
	--admin_password="admin" \
	--admin_email="thomas@eighty20results.com"

echo "Setup rewrite rules for WordPress"
inc/bin/wp rewrite structure \
	--path="${WP_CORE_DIR}" \
	'/%postname%/'

echo "Copying the plugin sources to the correct directory"
# Copy our plugin to WordPress directory
cp -r ./ ${WP_CORE_DIR}/wp-content/plugins/${WP_ORG_PLUGIN_NAME}

if [[ -n "${WP_DEPENDENCY_LIST}" ]]; then
	echo "Install and activate dependencies"
	inc/bin/wp plugin install --network --path="${WP_CORE_DIR}" "${WP_DEPENDENCY_LIST}" --activate
fi
# Activate our plugin
echo "Activating the plugin being tested"
inc/bin/wp plugin activate --network --path="${WP_CORE_DIR}" ${WP_ORG_PLUGIN_NAME}
