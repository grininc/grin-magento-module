#!/bin/bash

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "Error: Docker is not running"
    exit 1
fi

# Check if .docker-magento directory exists
if [ ! -d ".docker-magento" ]; then
    echo "Error: .docker-magento directory not found"
    echo "Please run setup-first-time.sh first"
    exit 1
fi

# Check if we're in the right directory
if [ ! -f ".docker-magento/compose.yaml" ]; then
    echo "Error: Please run this script from the root directory of the module"
    exit 1
fi

# Stop any existing containers
cd .docker-magento
echo "Stopping any existing containers..."
docker-compose down -v

# Start the Docker containers
echo "Starting containers..."
docker-compose up -d

# Wait for services to be ready
echo "Waiting for services to be ready..."
sleep 10

# Check if containers are running
if ! docker-compose ps | grep -q "Up"; then
    echo "Error: Some containers failed to start. Please check the logs with:"
    echo "cd .docker-magento && docker-compose logs"
    exit 1
fi

echo "Magento environment is starting up!"
echo "You can access Magento at https://magento.grin.co.test:8443"
echo "Admin URL: https://magento.grin.co.test:8443/admin"
echo "Admin credentials: admin/admin123"
echo ""
