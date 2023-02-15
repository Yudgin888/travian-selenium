WORKING_DIR = /var/www
DOCKER_EXEC = docker exec travian-php

.PHONY: cache
cache:
	${DOCKER_EXEC} composer dump-autoload --classmap-authoritative --working-dir=${WORKING_DIR}
	${DOCKER_EXEC} php bin/console cache:clear --no-interaction --no-ansi
	${DOCKER_EXEC} php bin/console cache:warmup --no-interaction --no-ansi

.PHONY: perm
perm:
	${DOCKER_EXEC} chmod -R 777 ${WORKING_DIR}/var
