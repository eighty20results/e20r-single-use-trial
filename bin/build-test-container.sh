#!/usr/bin/env bash
DEV_ENVIRONMENT=$(ipconfig getifaddr en0)
export DEV_ENVIRONMENT
export PROJECT_NAME="${PROJECT_NAME:-e20r-single-use-trial}"
PLUGIN_LIST="paid-memberships-pro pmpro-payfast ${PROJECT_NAME}"
PLUGIN_DIR="${PLUGIN_DIR:-docker-env}"
IMAGE_VERSION="${IMAGE_VERSION:-1.0}"
DOCKER_CMD="$(which docker)"
DB_NAME="${DB_NAME:-wordpress}"
DB_HOST="${DB_HOST:-mariadb}"
DB_USERNAME="${DB_USERNAME:-wordpress}"
DB_PASSWD="${DB_PASSWD:-wordpress}"
DB_FILE="${DB_FILE:-tests/databases/default_db.sql}"
TARGET_REGISTRY="${TARGET_REGISTRY:-docker.local:5000}"
TAG_NAME="${TARGET_REGISTRY}/${PROJECT_NAME}"
WORKING_DIR="${WORKING_DIR:-.}"

DOCKER_BUILD_ARGS="--build-arg PROJECT_NAME=${PROJECT_NAME} --build-arg DB_NAME=${DB_NAME} --build-arg DB_HOST=${DB_HOST} --build-arg DB_USERNAME=${DB_USERNAME} --build-arg DB_PASSWD=${DB_PASSWD}"

if [[ -z "${DOCKER_CMD}" ]]; then
  echo "Error: Docker not installed on this host!!"
  exit 1
fi

build_cmd="${DOCKER_CMD} build -t ${TAG_NAME}:${IMAGE_VERSION} --rm ${DOCKER_BUILD_ARGS} -f ${PLUGIN_DIR}/${PROJECT_NAME}.Dockerfile ${WORKING_DIR}"
tag_cmd="${DOCKER_CMD} tag ${TAG_NAME}:${IMAGE_VERSION} ${TAG_NAME}:latest"
docker_push_cmd="${DOCKER_CMD} push ${TAG_NAME}:${IMAGE_VERSION}"

echo "Running: ${build_cmd}"
status="$(${build_cmd})"

if [[ "${status}" -ne 0 ]]; then
  echo "Error building container for ${PROJECT_NAME}"
  echo "${status}"
  exit 1
fi

echo "Running: ${tag_cmd}"
status="$(${tag_cmd})"

if [[ "${status}" -ne 0 ]]; then
  echo "Error tagging ${TAG_NAME}:${IMAGE_VERSION} as ${TAG_NAME}:latest"
  exit 1
fi

echo "Running: ${docker_push_cmd}"
status="$(${docker_push_cmd})"

if [[ "${status}" -ne 0 ]]; then
  echo "Error pushing to target registry"
  exit 1
fi

echo "Built ${PROJECT_NAME} docker image and pushed to local repository: ${TAG_NAME}"
./bin/prepare-docker.sh
