#!/usr/bin/env bash

set -Eeuo pipefail

ROLE="${1:-web}"

if [ "$#" -gt 0 ]; then
    shift
fi

MAILVOTECH_ROOT="/var/www/html"
CONFIG_FILE="${MAILVOTECH_ROOT}/config/local.php"

DB_HOST="${MAILVOTECH_DB_HOST:-db}"
DB_PORT="${MAILVOTECH_DB_PORT:-3306}"

mkdir -p \
    "${MAILVOTECH_ROOT}/config" \
    "${MAILVOTECH_ROOT}/var/cache" \
    "${MAILVOTECH_ROOT}/var/logs" \
    "${MAILVOTECH_ROOT}/var/tmp" \
    "${MAILVOTECH_ROOT}/media/files" \
    "${MAILVOTECH_ROOT}/media/images"

chown -R www-data:www-data \
    "${MAILVOTECH_ROOT}/config" \
    "${MAILVOTECH_ROOT}/var" \
    "${MAILVOTECH_ROOT}/media/files" \
    "${MAILVOTECH_ROOT}/media/images"


wait_for_database() {
    echo "[MailVotech] Waiting for database ${DB_HOST}:${DB_PORT} ..."

    for attempt in $(seq 1 90); do
        if php -r '
            $host = getenv("MAILVOTECH_DB_HOST") ?: "db";
            $port = (int) (getenv("MAILVOTECH_DB_PORT") ?: 3306);

            $socket = @fsockopen($host, $port, $errno, $errstr, 2);

            if ($socket) {
                fclose($socket);
                exit(0);
            }

            exit(1);
        '; then
            echo "[MailVotech] Database is reachable."
            return 0
        fi

        sleep 2
    done

    echo "[MailVotech] ERROR: database did not become reachable."
    return 1
}


wait_for_installation() {
    echo "[MailVotech] Waiting for initial installation..."

    while [ ! -s "${CONFIG_FILE}" ]; do
        sleep 5
    done

    echo "[MailVotech] Installation configuration detected."
}


wait_for_database

case "${ROLE}" in

    web)
        echo "[MailVotech] Starting Apache web service."
        exec apache2-foreground
        ;;

    worker)
        wait_for_installation
        echo "[MailVotech] Starting Messenger workers."
        exec supervisord \
            -c /etc/supervisor/conf.d/mailvotech.conf
        ;;

    cron)
        wait_for_installation
        echo "[MailVotech] Starting cron scheduler."
        exec cron -f
        ;;

    *)
        echo "[MailVotech] Executing custom command: ${ROLE} $*"
        exec "${ROLE}" "$@"
        ;;

esac
