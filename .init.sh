ln -s "/workspace/${REPO_NAME}/public_html/wp-content/plugins/${REPO_NAME}" "/workspace/${REPO_NAME}"
wp db import "/workspace/${REPO_NAME}/${REPO_NAME}/tests/database/default_db.sql" db import "/workspace/${REPO_NAME}/${REPO_NAME}/tests/database/default_db.sql"
wp plugin delete hello
wp plugin delete akismet
wp plugin install paid-memberships-pro --activate
wp plugin activate ${REPO_NAME}

