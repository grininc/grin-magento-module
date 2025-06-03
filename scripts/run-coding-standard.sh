#!/bin/bash

# Get the root directory (parent of scripts directory)
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Change to .docker-magento directory
cd "$ROOT_DIR/.docker-magento"

# Get the file path if provided
FILE_PATH="$1"

# Define the plugin directory
PLUGIN_DIR="/var/www/html/app/code/Grin/Module"

# Define patterns to ignore
IGNORE_PATTERNS=".docker-magento/*"

if [ -z "$FILE_PATH" ]; then
    # No parameter: run on entire plugin
    echo "Running coding standard check on plugin code..."
    ./bin/clinotty vendor/bin/phpcs --standard=Magento2 --colors --no-colors --report=full --report-file=/tmp/phpcs-report.txt --ignore="$IGNORE_PATTERNS" "$PLUGIN_DIR"
else
    # Parameter provided: run on specific file
    echo "Running coding standard check on file: $FILE_PATH"
    ./bin/clinotty vendor/bin/phpcs --standard=Magento2 --colors --no-colors --report=full --report-file=/tmp/phpcs-report.txt --ignore="$IGNORE_PATTERNS" "$PLUGIN_DIR/$FILE_PATH"
fi

# Display the report
echo -e "\nCoding Standard Report:"
./bin/clinotty cat /tmp/phpcs-report.txt 