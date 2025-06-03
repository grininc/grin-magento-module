#!/bin/bash

# Exit on any error
set -e

# Magento version
MAGENTO_VERSION="2.4.8"

# Get the root directory (parent of scripts directory)
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Function to handle errors
handle_error() {
    echo "Error: Installation failed at step: $1"
    echo "Please check the error message above and try again."
    exit 1
}

# Check if we're in a Magento root directory
if [ -f "$ROOT_DIR/bin/magento" ]; then
    echo "Error: You appear to be in a Magento root directory"
    echo "Please run this script from a parent directory."
    exit 1
fi

# Check for magento-auth.env file
AUTH_FILE="$ROOT_DIR/scripts/magento-auth.env"
if [ ! -f "$AUTH_FILE" ]; then
    echo "Error: magento-auth.env file not found"
    echo "Please copy scripts/magento-auth.template to scripts/magento-auth.env and add your Magento Marketplace credentials"
    exit 1
fi

# Source the auth file
source "$AUTH_FILE"

# Validate credentials
if [ -z "$MAGENTO_MARKETPLACE_PUBLIC_KEY" ] || [ -z "$MAGENTO_MARKETPLACE_PRIVATE_KEY" ] || [ -z "$GITHUB_TOKEN" ]; then
    echo "Error: Magento Marketplace credentials not found in magento-auth.env"
    echo "Please add your public and private keys to the file"
    exit 1
fi

# Create a directory for the Magento installation
MAGENTO_DIR="$ROOT_DIR/.docker-magento"
if [ -d "$MAGENTO_DIR" ]; then
    echo "Error: Directory $MAGENTO_DIR already exists"
    echo "Please remove it before running this script."
    exit 1
fi

mkdir -p $MAGENTO_DIR
cd $MAGENTO_DIR

# Initialize git and download the templates
git init -qqq
git remote add origin https://github.com/markshust/docker-magento
git fetch origin -qqq
git checkout 51020e8ea366e1bcccc4126c07a13caad5182c15 -- compose # points to origin/master v52.0.1

# Check if the compose directory exists
if [ ! -d "compose" ]; then
    echo "Error: Failed to download Docker templates"
    exit 1
fi

# Move the Docker files to our installation directory
mv compose/* .
mv compose/.gitignore .
mv compose/.vscode .
rm -rf compose .git

# Go back to root directory
cd $ROOT_DIR

# Update ports in the compose file
COMPOSE_FILE="$MAGENTO_DIR/compose.yaml"

# Function to replace port mapping
replace_port() {
    local service=$1
    local old_port=$2
    local new_port=$3
    
    # Handle app service format (e.g., "80:8000")
    if [ "$service" = "app" ]; then
        # Use a more precise sed command to match the exact line
        sed -i '' "/ports:/,/volumes:/ s/\"$old_port:/\"$new_port:/g" "$COMPOSE_FILE"
        return
    fi
    
    # Handle standard format (e.g., "6379:6379")
    pattern="\"$old_port:$old_port\""
    replacement="\"$new_port:$old_port\""
    sed -i '' "s/$pattern/$replacement/g" "$COMPOSE_FILE"
}

# Function to update hosts file
update_hosts_file() {
    local hosts_entry="127.0.0.1 magento.grin.co.test"
    
    # Check if the entry already exists
    if ! grep -q "$hosts_entry" /etc/hosts; then
        echo "Adding magento.grin.co.test to hosts file..."
        echo "$hosts_entry" | sudo tee -a /etc/hosts
    fi
}

# Ports have been automatically updated to avoid conflicts with app.grin.co
replace_port "app" "80" "8081"
replace_port "app" "443" "8443"
replace_port "mysql" "3306" "3307"
replace_port "redis" "6379" "6380"
replace_port "opensearch" "9200" "9201"
replace_port "rabbitmq" "5672" "5673"
replace_port "mailcatcher" "1025" "1026"

# Update hosts file
update_hosts_file

# Create/overwrite magento.env file
MAGENTO_ENV_FILE="$MAGENTO_DIR/bin/../env/magento.env"
echo "Creating magento.env file at $MAGENTO_ENV_FILE..."
mkdir -p "$(dirname "$MAGENTO_ENV_FILE")"
cat > "$MAGENTO_ENV_FILE" << EOL
MAGENTO_ADMIN_EMAIL=admin@grin.co
MAGENTO_ADMIN_FIRST_NAME=Admin
MAGENTO_ADMIN_LAST_NAME=User
MAGENTO_ADMIN_USER=admin
MAGENTO_ADMIN_PASSWORD=admin123
MAGENTO_ADMIN_FRONTNAME=admin
MAGENTO_LOCALE=en_US
MAGENTO_CURRENCY=USD
MAGENTO_TIMEZONE=America/New_York
EOL

# Run the setup script
cd $MAGENTO_DIR

# Download Magento first
echo "Downloading Magento..."
echo "Note: This may take several minutes to complete..."

# Patch bin/download to add timeout and other flags to composer create-project
sed -i '' 's|composer create-project --repository-url=https://repo.mage-os.org/ mage-os/project-community-edition="${VERSION}" \.|composer create-project --no-progress --prefer-dist --repository-url=https://repo.mage-os.org/ mage-os/project-community-edition="${VERSION}" .|' "$MAGENTO_DIR/bin/download"
sed -i '' 's|composer create-project --repository=https://repo.magento.com/ magento/project-"${EDITION}"-edition="${VERSION}" \.|composer create-project --no-progress --prefer-dist --repository=https://repo.magento.com/ magento/project-"${EDITION}"-edition="${VERSION}" .|' "$MAGENTO_DIR/bin/download"

# Patch bin/setup to use absolute paths for magento.env
sed -i '' 's|if \[ -f "\.\.\/env\/magento\.env" \]; then|SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" \&\& pwd)"\nif [ -f "$SCRIPT_DIR/../env/magento.env" ]; then|' "$MAGENTO_DIR/bin/setup"
sed -i '' 's|source "\.\.\/env\/magento\.env"|source "$SCRIPT_DIR/../env/magento.env"|' "$MAGENTO_DIR/bin/setup"

# Patch bin/setup to show correct port in URLs
sed -i '' 's|https://${DOMAIN}/|https://${DOMAIN}:8443/|' "$MAGENTO_DIR/bin/setup"
sed -i '' 's|https://${DOMAIN}/admin/|https://${DOMAIN}:8443/admin/|' "$MAGENTO_DIR/bin/setup"

# Patch bin/setup-install to set correct base URLs with port
sed -i '' 's|--base-url=https://"$DOMAIN"/|--base-url=https://"$DOMAIN":8443/|' "$MAGENTO_DIR/bin/setup-install"
sed -i '' 's|--base-url-secure=https://"$DOMAIN"/|--base-url-secure=https://"$DOMAIN":8443/|' "$MAGENTO_DIR/bin/setup-install"

# Start the containers to inject the environment variables
bin/start --no-dev

# Wait for containers to be ready
echo "Waiting for containers to be ready..."
sleep 10

# Set up authentication in the correct order
echo "Setting up authentication..."

# Create a temporary Composer home directory in the container
bin/clinotty bash << EOF
set -e

# Create temporary and permanent Composer directories
mkdir -p /var/www/.composer-temp
mkdir -p /var/www/.composer

# Set up temporary Composer home
export COMPOSER_HOME=/var/www/.composer-temp

# Set up GitHub token
composer config --global github-oauth.github.com "${GITHUB_TOKEN}" || exit 1

# Set up Magento Marketplace credentials
composer config --global http-basic.repo.magento.com "${MAGENTO_MARKETPLACE_PUBLIC_KEY}" "${MAGENTO_MARKETPLACE_PRIVATE_KEY}" || exit 1

# Copy the auth.json to the correct location
cp /var/www/.composer-temp/auth.json /var/www/.composer/auth.json || exit 1
EOF
[ $? -eq 0 ] || handle_error "Setting up authentication"

# Run download inside the container
bin/download community "$MAGENTO_VERSION" || handle_error "Downloading Magento"

# Then run the setup with the domain parameter
bin/setup "magento.grin.co.test" || handle_error "Setting up Magento"

# Update web configuration to use correct port
bin/clinotty bin/magento config:set web/secure/base_url "https://magento.grin.co.test:8443/" || handle_error "Setting secure base URL"
bin/clinotty bin/magento config:set web/unsecure/base_url "https://magento.grin.co.test:8443/" || handle_error "Setting unsecure base URL"
bin/clinotty bin/magento config:set web/secure/use_in_frontend 1 || handle_error "Setting secure frontend"
bin/clinotty bin/magento config:set web/secure/use_in_adminhtml 1 || handle_error "Setting secure admin"
bin/clinotty bin/magento cache:flush || handle_error "Flushing cache"

# Add module volume mount to Docker Compose
echo "Adding module volume mount to Docker Compose..."
cat << EOF | sed -i '' '/volumes:/r /dev/stdin' "$MAGENTO_DIR/compose.yaml"
      - ${ROOT_DIR}:/var/www/html/app/code/Grin/Module:delegated,exclude=.docker-magento
EOF

# Restart containers to apply volume mount
bin/restart

# Wait for containers to be ready
echo "Waiting for containers to be ready..."
sleep 10

# Enable and setup our module
echo "Setting up GRIN module..."
bin/clinotty bin/magento module:enable Grin_Module --force || handle_error "Enabling module"
bin/clinotty bin/magento setup:upgrade || handle_error "Running setup:upgrade"
bin/clinotty bin/magento setup:di:compile || handle_error "Running setup:di:compile"
bin/clinotty bin/magento setup:static-content:deploy -f || handle_error "Deploying static content"
bin/clinotty bin/magento cache:flush || handle_error "Flushing cache"
bin/clinotty bin/magento cron:run || handle_error "Running cron"

# Install Magento coding standard
echo "Installing Magento coding standard..."
bin/clinotty composer require --dev magento/magento-coding-standard || handle_error "Installing coding standard"

# Verify module installation
echo "Verifying module installation..."
bin/clinotty bin/magento module:status Grin_Module || handle_error "Verifying module status"

# Disable Two-Factor Authentication
echo "Disabling Two-Factor Authentication..."
bin/clinotty bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth Magento_TwoFactorAuth || handle_error "Disabling 2FA"
bin/clinotty bin/magento setup:upgrade || handle_error "Running setup:upgrade after 2FA"
bin/clinotty bin/magento cache:flush || handle_error "Final cache flush"

echo ""
echo "Install complete."