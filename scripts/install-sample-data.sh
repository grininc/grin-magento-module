#!/bin/bash

# Exit on error
set -e

# Change to the Magento root directory
cd .docker-magento

echo "Setting developer mode..."
bin/magento deploy:mode:set developer

echo "Installing sample data..."
bin/magento sampledata:deploy

echo "Running setup upgrade..."
bin/magento setup:upgrade

echo "Cleaning cache..."
bin/magento cache:clean

echo "Flushing cache..."
bin/magento cache:flush

echo "Sample data installation completed!" 
