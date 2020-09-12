wp db import ~/public_html/wp-content/plugins/${REPO_NAME}/tests/database/default_db.sql
wp plugin delete hello
wp plugin delete akismet
<<<<<<< HEAD
wp plugin install paid-memberships-pro
wp plugin activate paid-memberships-pro ${REPO_NAME}
=======
wp plugin install paid-memberships-pro --activate
wp plugin activate ${REPO_NAME}
>>>>>>> fd68401b5bdc41daff9c98dcf00afabb57614df7
