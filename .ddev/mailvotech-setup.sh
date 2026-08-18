#!/bin/bash

setup_mailvotech() {
    [ -z "${MAILVOTECH_URL}" ] && MAILVOTECH_URL="https://${DDEV_HOSTNAME}"
    [ -z "${PHPMYADMIN_URL}" ] && PHPMYADMIN_URL="https://${DDEV_HOSTNAME}:8037"
    [ -z "${MAILHOG_URL}" ] && MAILHOG_URL="https://${DDEV_HOSTNAME}:8026"

    printf "Installing MailVotech Composer dependencies...\n"
    composer install

    cp ./.ddev/local.config.php.dist ./config/local.php
    cp ./.ddev/.env.test.local ./.env.test.local
    if [[ "${CODESPACES:-}" != "true" ]]; then
        cp ./.ddev/.env.local.dist ./.env.local
    fi

    printf "Installing MailVotech...\n"
    php bin/console mailvotech:install "${MAILVOTECH_URL}" --force --no-interaction
    php bin/console cache:warmup --no-interaction --env=dev

    printf "Enabling plugins...\n"
    php bin/console mailvotech:plugins:reload

    printf "Building GrapesJs builder assets...\n"
    ( cd plugins/GrapesJsBuilderBundle && npm ci && npm run build-dev )

    tput setaf 2
    printf "All done! Here's some useful information:\n"
    printf "🔒 The default login is admin / Maut1cR0cks!\n"
    printf "🌐 To open the MailVotech instance, go to ${MAILVOTECH_URL} in your browser.\n"
    printf "🌐 To open PHPMyAdmin for managing the database, go to ${PHPMYADMIN_URL} in your browser.\n"
    printf "🌐 To open MailHog for seeing all emails that MailVotech sent, go to ${MAILHOG_URL} in your browser.\n"
    printf "🚀 Run \"ddev exec composer test\" to run PHPUnit tests.\n"
    printf "🚀 Run \"ddev exec composer e2e-test\" to run End-to-End Test Suite.\n"
    printf "🚀 Run \"ddev exec bin/console COMMAND\" (like mailvotech:segments:update) to use the MailVotech CLI. For an overview of all available CLI commands, go to https://mau.tc/cli\n"
    printf "🔴 If you want to stop the instance, simply run \"ddev stop\".\n"
    tput sgr0
}

# Check if the user has indicated their preference for the MailVotech installation
# already (DDEV-managed or self-managed)
if ! test -f ./.ddev/mailvotech-preference
then
    tput setab 3
    tput setaf 0
    printf "Do you want us to set up the MailVotech instance for you with the recommended settings for DDEV?\n"
    printf "If you answer \"no\", you will have to set up the MailVotech instance yourself."
    tput sgr0
    printf "\nAnswer [yes/no]: "
    read MAILVOTECH_PREF

    if [[ "$MAILVOTECH_PREF" =~ ^([Yy]|[Yy][Ee][Ss])$ ]];
    then
        printf "Okay, setting up your MailVotech instance... 🚀\n"
        echo "ddev-managed" > ./.ddev/mailvotech-preference
        setup_mailvotech
    else
        printf "Okay, you'll have to set up the MailVotech instance yourself. That's what pros do, right? Good luck! 🚀\n"
        echo "unmanaged" > ./.ddev/mailvotech-preference
    fi
fi
