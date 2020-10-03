#!/usr/bin/env bash

# Allows WP CLI to run with the right permissions.
wp-su() {
    sudo -E -u www-data wp "$@"
}

# Clean up from previous tests
rm -rf "/wp-core/wp-content/uploads/${WP_PLUGIN_NAME}"


# Make sure permissions are correct.
mkdir -p /wp-core || die "Error: Cannot create /wp-core"
cd /wp-core || die "Error: Cannot access /wp-core"
chown -R www-data:www-data wp-content
chmod 755 wp-content

export WP_CLI_CACHE_DIR=/wp-core/.wp-cli/cache

# Make sure the database is up and running.
while ! mysqladmin ping -h${DB_SERVER} --silent; do

    echo 'Waiting for the database'
    sleep 1

done

echo 'The database server is ready'

# Make sure WordPress is installed.
if ! $(wp-su core is-installed); then

    echo "Installing WordPress"
	./.circleci/setup-${WP_TYPE}.sh
fi

rm -rf "/wp-core/wp-content/plugins/${WP_PLUGIN_NAME}"


# Checkout the branch being requested
if [[ -n "${WP_PLUGIN_BRANCH}" && "master" != "${WP_PLUGIN_BRANCH}" ]]; then
	echo "Grabbing the latest ${WP_PLUGIN_BRANCH} branch of ${WP_PLUGIN_NAME}"
	git clone -b master --single-branch "https://${GITHUB_TOKEN}@github.com/eighty20results/${WP_PLUGIN_NAME}.git" "/wp-core/wp-content/plugins/${WP_PLUGIN_NAME}"
	cd /wp-core/wp-content/plugins/${WP_PLUGIN_NAME}
	git checkout ${WP_PLUGIN_BRANCH}
else
	echo "Grabbing the latest development master of ${WP_PLUGIN_NAME}"
	git clone -b ${WP_PLUGIN_BRANCH} --single-branch "https://${GITHUB_TOKEN}@github.com/eighty20results/${WP_PLUGIN_NAME}.git" "/wp-core/wp-content/plugins/${WP_PLUGIN_NAME}"
fi

cd /project

# Run the Codeception tests
exec "/${WP_PLUGIN_NAME}/inc/bin/codecept" "$@"
