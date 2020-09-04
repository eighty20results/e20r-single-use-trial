#!/usr/bin/env bash
DEV_ENVIRONMENT="$(ipconfig getifaddr en0)"
export PROJECT_NAME="${PROJECT_NAME:-e20r-single-use-trial}"
PLUGIN_DIR=docker-env
PLUGIN_LIST="paid-memberships-pro pmpro-payfast"
CURRENT_DIR=$(pwd)

if [[ "${DEV_ENVIRONMENT}" == "10.0.0.76" || "${DEV_ENVIRONMENT}" == "10.0.0.175" ]];
then
    echo "At home so using the docker env on docker.local"
    # ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; mkdir -p ./mariadb-init" # mkdir -p ./traefik
    cp  "${PLUGIN_DIR}/hosts.home" ~/PhpStormProjects/docker-images/docker4wordpress/hosts.docker
    scp "${PLUGIN_DIR}/docker-compose.yml" docker.local:./www/docker/docker4wordpress/docker-compose.yml
    scp "${PLUGIN_DIR}/docker-compose.override.yml-home" docker.local:./www/docker/docker4wordpress/docker-compose.override.yml
    scp "${PLUGIN_DIR}/env" docker.local:./www/docker/docker4wordpress/.env
    ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; make down && make up"
    ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; make wp 'core install --url=development.local --title=\"${PROJECT_NAME} test site\" --admin_user=admin --admin_password=admin --admin_email=info@example.com'"
    ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; make wp 'db import /var/www/html/default_db.sql'"
    ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; make wp \"rewrite structure '/%postname%'\""
    ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; make wp 'rewrite flush'"
    ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; make wp 'plugin install ${PLUGIN_LIST}'"
    ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; make wp 'plugin activate ${PLUGIN_LIST} ${PROJECT_NAME}'"
#    scp ${PLUGIN_DIR}/${PROJECT_NAME}.sql docker.local:./www/docker/docker4wordpress/mariadb-init/
#    scp ${PLUGIN_DIR}/import-db.sh docker.local:./www/docker/docker4wordpress/import-db.sh
#    scp -r ${PLUGIN_DIR}/traefik docker.local:./www/docker/docker4wordpress/traefik
#    ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; chmod +x ./import-db.sh ; nohup ./import-db.sh"
#    ssh docker.local "cd ./www/docker-images/docker4wordpress/ ; make wp plugin activate ${PLUGIN_LIST}"
else
    echo "Not at home (using the local laptop docker env)"
    mkdir -p /Volumes/Development/www/docker-images/docker4wordpress/mariadb-init
    cp "${PLUGIN_DIR}/hosts.local" ~/PhpStormProjects/docker-images/docker4wordpress/hosts.docker
    cp "${PLUGIN_DIR}/docker-compose.yml" ~/PhpStormProjects/docker-images/docker4wordpress/docker-compose.yml
    cp "${PLUGIN_DIR}/docker-compose.override.yml-local" ~/PhpStormProjects/docker-images/docker4wordpress/docker-compose.override.yml
    cp "${PLUGIN_DIR}/${PROJECT_NAME}.sql" ~/PhpStormProjects/docker-images/docker4wordpress/mariadb-init/
    cp "${PLUGIN_DIR}/import-db.sh" ~/PhpStormProjects/docker-images/docker4wordpress/import-db.sh
    cp "${PLUGIN_DIR}/env" ~/PhpStormProjects/docker-images/docker4wordpress/.env
    cd /Users/sjolshag/PhpStormProjects/docker-images/docker4wordpress/ || exit 1
    make down
    make up
    chmod +x ./import-db.sh
    nohup ./import-db.sh
    # ./import-db.sh
    make wp plugin activate ${PLUGIN_LIST}
    cd "${CURRENT_DIR}" || exit 1
fi
