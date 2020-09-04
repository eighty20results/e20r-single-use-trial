#!/usr/bin/env bash
DEV_ENVIRONMENT=$(ipconfig getifaddr en0)
export DEV_ENVIRONMENT
export PROJECT_NAME="${PROJECT_NAME:-e20r-single-use-trial}"
export PLUGIN_LIST="paid-memberships-pro pmpro-payfast"
export PLUGIN_DIR="${PLUGIN_DIR:-docker-env}"
export DB_NAME="${DB_NAME:-wordpress}"
export DB_HOST="${DB_HOST:-mariadb}"
export DB_USERNAME="${DB_USERNAME:-wordpress}"
export DB_PASSWD="${DB_PASSWD:-wordpress}"
export DB_FILE="${DB_FILE:-tests/databases/default_db.sql}"
WORKING_DIR="${WORKING_DIR:-.}"
ANSIBLE_DIR="~/ansible"
MY_INVENTORY="inventory/sjolshagen-net.yml"
MY_PLAYBOOK="playbook/build-test-vm.yml"
CURRENT_DIR=$(pwd)

cd "${ANSIBLE_DIR}" || exit 1
if ! ansible-playbook -i "${MY_INVENTORY}" "${MY_PLAYBOOK}"; then
  echo "Error: Unable to build Test VM with the ${PROJECT_NAME} plugin installed!"
  exit 1
fi

