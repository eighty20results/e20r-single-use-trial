#!/usr/bin/env bash

if [[ -z "$CIRCLECI" ]]; then
    echo "This script can only be run by CircleCI. Aborting." 1>&2
    exit 1
fi

if [[ -z "$CIRCLE_BRANCH" || "$CIRCLE_BRANCH" != "master" ]]; then
    echo "Build branch is required and must be 'master' branch. Stopping deployment." 1>&2
    exit 0
fi

git archive -9 --format zip --output "./artifacts/${CIRCLE_PROJECT_REPONAME}.zip"
git archive -9 --format tar.gz --output "./artifacts/${CIRCLE_PROJECT_REPONAME}.tar.gz"

ghr -t ${GITHUB_TOKEN} \
	-u ${CIRCLE_PROJECT_USERNAME} \
	-r ${CIRCLE_PROJECT_REPONAME} \
	-c ${CIRCLE_SHA1} \
	-delete ${VERSION} ./artifacts/
