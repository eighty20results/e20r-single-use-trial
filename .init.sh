wp db import ~/public_html/wp-content/plugins/${REPO_NAME}/tests/database/default_db.sql
wp plugin delete hello
wp plugin delete akismet
wp plugin install paid-memberships-pro --activate
wp plugin activate ${REPO_NAME}
