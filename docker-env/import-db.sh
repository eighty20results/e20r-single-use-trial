#!/usr/bin/env bash
PROJECT_NAME='single-use-trial'
echo "Importing database for ${PROJECT_NAME}"
# sleep 30;
echo $(pwd)
make wp db import ./mariadb-init/${PROJECT_NAME}.sql
