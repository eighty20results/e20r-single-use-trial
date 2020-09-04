# Using the main wodby WordPress (latest) docker image as the foundation for this plugin specific entity
FROM wodby/nginx:latest

ARG PROJECT_NAME="${PROJECT_NAME:-e20r-single-use-trial}"
ARG PLUGIN_LIST="paid-memberships-pro ${PROJECT_NAME}"
ARG DB_NAME="wordpress"
ARG DB_HOST="mariadb"
ARG DB_USERNAME="wordpress"
ARG DB_PASSWD="wordpress"
ARG DB_FILE="tests/databases/default_db.sql"
ARG WP_ROOT="/var/www/html"

# Install all required plugins and activate them
RUN wp --path=${WP_ROOT} core download && \
    wp --path=${WP_ROOT} config create --dbname=${DB_NAME} --dbuser=${DB_USERNAME} --dbpass=${DB_PASSWD} \
        --dbhost=${DB_HOST} --skip-check --force

# Copy the (new) version of the plugin we're testing into the image
RUN mkdir -p ${WP_ROOT}/wp-content/plugins/${PROJECT_NAME}/ && \
    mkdir -p ${WP_ROOT}/wp-content/plugins/${PROJECT_NAME}/inc && \
    mkdir -p ${WP_ROOT}/wp-content/plugins/${PROJECT_NAME}/languages

COPY inc/* ${WP_ROOT}/wp-content/plugins/${PROJECT_NAME}/inc/
COPY languages/* ${WP_ROOT}/wp-content/plugins/${PROJECT_NAME}/languages/
COPY metadata.json ${WP_ROOT}/wp-content/plugins/${PROJECT_NAME}/
COPY README.txt ${WP_ROOT}/wp-content/plugins/${PROJECT_NAME}/
COPY composer* ${WP_ROOT}/wp-content/plugins/${PROJECT_NAME}/
COPY tests/database/default_db.sql ${WP_ROOT}/
COPY *${PROJECT_NAME}.php ${WP_ROOT}/wp-content/plugins/${PROJECT_NAME}/
