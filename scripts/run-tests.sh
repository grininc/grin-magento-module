#!/bin/bash

# Change to .docker-magento directory
cd "$HOME/.docker-magento"

if [ -z "$1" ]; then
    # No parameter: run all tests in the Test directory
    echo "Listing all tests:"
    ./bin/clinotty vendor/bin/phpunit --configuration /var/www/html/app/code/Grin/Module/phpunit.xml --list-tests "/var/www/html/app/code/Grin/Module/Test"
    echo -e "\nRunning tests:"
    ./bin/clinotty vendor/bin/phpunit --configuration /var/www/html/app/code/Grin/Module/phpunit.xml "/var/www/html/app/code/Grin/Module/Test"
else
    # Parameter provided: run the specific test file
    echo "Listing tests in file:"
    ./bin/clinotty vendor/bin/phpunit --configuration /var/www/html/app/code/Grin/Module/phpunit.xml --list-tests "/var/www/html/app/code/Grin/Module/$1"
    echo -e "\nRunning tests:"
    ./bin/clinotty vendor/bin/phpunit --configuration /var/www/html/app/code/Grin/Module/phpunit.xml "/var/www/html/app/code/Grin/Module/$1"
fi
