#!/usr/bin/env bash
short_name="${1}"
server="eighty20results.com"
sed="$(which sed)"
wordpress_version=$(wget -q -O - http://api.wordpress.org/core/stable-check/1.0/  | grep latest | awk '{ print $1 }' | sed -e 's/"//g')
version=$(grep -E "^Version:" ./"${short_name}.php" | sed 's/[[:alpha:]|(|[:space:]|\:]//g' | awk -F- '{printf "%s", $1}')
today=$(date +%Y-%m-%d)
url_info="https:\/\/${server}\/protected-content\/${short_name}\/${short_name}"
url_with_version="${url_info}-${version}\.zip"
metadata_template=$(cat <<- __EOF__
{
  "name": "Eighty/20 Results - Single Use Trial Memberships for Paid Memberships Pro",
  "slug": "e20r-single-use-trial",
  "download_url": "https://${server}/protected-content/${short_name}/${short_name}-${version}.zip",
  "version": "1.0",
  "tested": "1.0",
  "requires": "5.0",
  "author": "Thomas Sjolshagen <thomas@eighty20results.com>",
  "author_homepage": "https://eighty20results.com/thomas-sjolshagen",
  "last_updated": "2021-02-14 12:45:00 CET",
  "homepage": "https://eighty20results.com/wordpress-plugins/paid-memberships-pro/${short_name}/",
  "sections": {
    "description": "Only allow a Paid Memberships Pro member to sign up for a trial membership level once.",
    "changelog": "See the linked <a href=\"CHANGELOG.md\" target=\"_blank\">Change Log</a> for details",
    "faq": "<h3>I found a bug in the plugin.</h3><p>Please report your issue to us by using the <a href='https://github.com/eighty20results/e20r-single-use-trial/issues' target='_blank'>Github Issues page</a>, and we'll try to respond within 1 business day.</p>"
    }
}
__EOF__
)

if [[ ! -f ./metadata.json ]]; then
	echo "${metadata_template}" > ./metadata.json
fi

###########
#
# Update plugin and wordpress version info in metadata.json
#
if [[ -f ./metadata.json ]]; then
	echo "Updating the metadata.json file"
	"${sed}" -r -e "s/\"version\": \"([0-9]+\.[0-9].*)\"\,/\"version\": \"${version}\"\,/" \
					 -e "s/\"tested\"\:\ \"([0-9]+\.[0-9].*)\"\,/\"tested\"\:\ \"${wordpress_version}\"\,/" \
					 -e "s/\"last_updated\": \"(.*)\",/\"last_updated\": \"${today} $(date +%H:%M:00) CET\",/g" \
					 -e "s/\"download_url\": \"${url_info}-([0-9]+\.[0-9].*)\.zip\",/\"download_url\": \"${url_with_version}\",/g" \
					 ./metadata.json > ./new_metadata.json
		mv ./new_metadata.json ./metadata.json
fi

# git commit -m "BUG FIX: Updated metdata.json for v${version} and WP ${wordpress_version}" ./metadata.json

