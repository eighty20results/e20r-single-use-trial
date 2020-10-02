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

home_dir="${HOME}/${WP_ORG_PLUGIN_NAME}"

echo "---------------------------------------------------------------"
ls -la "${home_dir}/inc/bin/"
echo "---------------------------------------------------------------"

# Load WordPress and install into core directory
echo "Create WordPress install directory (${WP_CORE_DIR}), cd to it and download WordPress."
mkdir -p "${WP_CORE_DIR}"
cd "${WP_CORE_DIR}"

"${home_dir}/inc/bin/wp" core download

echo "Create WordPress config files in ${WP_CORE_DIR}"
echo "${home_dir}/inc/bin/wp config create \
      	--path=${WP_CORE_DIR} \
      	--dbhost=${DB_HOST} \
      	--dbname=${DB_NAME} \
      	--dbuser=${DB_USER} \
      	--dbpass=${DB_PASS}"

# Install WordPress
echo "Create WordPress config files in ${WP_CORE_DIR}"
"${home_dir}/inc/bin/wp" config create \
	--path="${WP_CORE_DIR}" \
	--dbhost="${DB_HOST}" \
	--dbname="${DB_NAME}" \
	--dbuser="${DB_USER}" \
	--dbpass="${DB_PASS}"

echo "Install/Create the WordPress test site"
"${home_dir}/inc/bin/wp" core install \
	--title="Testing ${WP_ORG_PLUGIN_NAME}" \
	--admin_user="admin" \
	--admin_password="admin" \
	--admin_email="thomas@eighty20results.com"

echo "Return to home directory"
cd "${home_dir}"

# Set permissions
chmod 644 "${WP_CORE_DIR}/wp-config.php"

echo "Import database for WordPress to installation in ${WP_CORE_DIR}"
"${home_dir}/inc/bin/wp" db import \
	--path="${WP_CORE_DIR}" \
	--dbuser="${DB_USER}" \
	--dbpass="${DB_PASS}" \
	--dbhost="${DB_HOST}" \
	--dbname="${DB_NAME}" \
	"./.circleci/database/default_db.sql"

echo "Setup rewrite rules for WordPress"
"${home_dir}/inc/bin/wp" rewrite structure \
	--path="${WP_CORE_DIR}" \
	'/%postname%/'

# Copy our plugin to WordPress directory
echo "Copying the plugin sources to the correct directory"
cp -r "${home_dir}/*" "${WP_CORE_DIR}/wp-content/plugins/${WP_ORG_PLUGIN_NAME}"

if [[ -n "${WP_DEPENDENCY_LIST}" ]]; then
	echo "Install and activate dependencies: ${WP_DEPENDENCY_LIST[*]}"
	"${home_dir}/inc/bin/wp" plugin install --path="${WP_CORE_DIR}" "${WP_DEPENDENCY_LIST[*]}" --activate
fi
# Activate our plugin
echo "Activating the plugin being tested"
"${home_dir}/inc/bin/wp" plugin activate --path="${WP_CORE_DIR}" "${WP_ORG_PLUGIN_NAME}"
