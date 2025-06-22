#!/bin/bash

# Exit if any command fails
set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Running WP GraphQL Yoast SEO tests in Docker...${NC}"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
  echo -e "${RED}Error: Docker is not running. Please start Docker and try again.${NC}"
  exit 1
fi

# Check if the containers are running
if ! docker-compose ps | grep -q "wordpress.*Up"; then
  echo -e "${YELLOW}WordPress container is not running. Starting Docker services...${NC}"
  docker-compose up -d
  echo -e "${GREEN}Waiting for services to start...${NC}"
  sleep 10
fi

# Install necessary packages and Composer in the container
echo -e "${YELLOW}Installing necessary packages in container...${NC}"
docker-compose exec wordpress bash -c "apt-get update && apt-get install -y curl zip unzip git subversion"

# Check if Composer is installed in the container, if not install it
echo -e "${YELLOW}Checking for Composer in container...${NC}"
if ! docker-compose exec wordpress bash -c "command -v composer" > /dev/null 2>&1; then
  echo -e "${YELLOW}Installing Composer in container...${NC}"
  docker-compose exec wordpress bash -c "curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer"
fi

# Check PHP version in the container and install compatible PHPUnit version
echo -e "${YELLOW}Checking PHP version in container...${NC}"
PHP_VERSION=$(docker-compose exec wordpress bash -c "php -r 'echo PHP_VERSION;'")
echo -e "${GREEN}PHP version in container: ${PHP_VERSION}${NC}"

# Install PHPUnit in the container if it's not already installed
echo -e "${YELLOW}Installing test dependencies in container...${NC}"

# Use PHPUnit 9.x for PHP 7.3-8.0, PHPUnit 10.x for PHP 8.1+
if [[ "$PHP_VERSION" == 8.* ]]; then
  echo -e "${YELLOW}Using PHPUnit 10.x for PHP 8.x${NC}"
  docker-compose exec wordpress bash -c "cd /var/www/html/wp-content/plugins/wp-graphql-yoast-seo && composer require --dev phpunit/phpunit:'^10.0' --with-all-dependencies"
else
  echo -e "${YELLOW}Using PHPUnit 9.x for PHP 7.x${NC}"
  docker-compose exec wordpress bash -c "cd /var/www/html/wp-content/plugins/wp-graphql-yoast-seo && composer require --dev phpunit/phpunit:'^9.0' --with-all-dependencies"
fi

# Install WordPress test environment in the container
echo -e "${YELLOW}Setting up WordPress test environment in container...${NC}"

# Get database credentials from docker-compose.yml environment variables
DB_NAME=$(grep WORDPRESS_DB_NAME docker-compose.yml | awk '{print $2}')
DB_USER=$(grep WORDPRESS_DB_USER docker-compose.yml | awk '{print $2}')
DB_PASS=$(grep WORDPRESS_DB_PASSWORD docker-compose.yml | awk '{print $2}')
DB_HOST="db" # This should be the service name in docker-compose.yml

echo -e "${GREEN}Using database: $DB_NAME, user: $DB_USER, host: $DB_HOST${NC}"

# Fix permissions for the install-wp-tests.sh script
echo -e "${YELLOW}Fixing permissions for install-wp-tests.sh...${NC}"
docker-compose exec wordpress bash -c "chmod +x /var/www/html/wp-content/plugins/wp-graphql-yoast-seo/bin/install-wp-tests.sh"

# Run the install script
docker-compose exec wordpress bash -c "cd /var/www/html/wp-content/plugins/wp-graphql-yoast-seo && \
  if [ ! -d /tmp/wordpress-tests-lib ]; then \
    ./bin/install-wp-tests.sh $DB_NAME $DB_USER $DB_PASS $DB_HOST latest true; \
  fi"

# Run the tests
echo -e "${YELLOW}Running tests...${NC}"

if [ -n "$1" ]; then
  # Run specific test file
  docker-compose exec wordpress bash -c "cd /var/www/html/wp-content/plugins/wp-graphql-yoast-seo && \
    ./vendor/bin/phpunit -v \"$1\""
else
  # Run all tests
  docker-compose exec wordpress bash -c "cd /var/www/html/wp-content/plugins/wp-graphql-yoast-seo && \
    ./vendor/bin/phpunit -v"
fi

echo -e "${GREEN}Tests completed!${NC}"
