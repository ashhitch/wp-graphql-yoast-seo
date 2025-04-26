#!/bin/bash

# Execute the original Docker entrypoint script to ensure WordPress is properly configured
docker-php-entrypoint apache2-foreground &

# Wait for WordPress to start
sleep 10

# Wait for the database to be ready
until wp db check --path=/var/www/html --allow-root; do
  echo "Waiting for database..."
  sleep 5
done

# Check if WordPress is installed
if ! $(wp core is-installed --path=/var/www/html --allow-root); then
  # Install WordPress
  wp core install --url="http://localhost:8000" --title="WPGraphQL Yoast SEO Test" --admin_user="admin" --admin_password="password" --admin_email="admin@example.com" --skip-email --allow-root
  echo "WordPress installed successfully"
fi

# Check and install WPGraphQL plugin
if ! $(wp plugin is-installed wp-graphql --allow-root); then
  wp plugin install wp-graphql --activate --allow-root
  echo "WPGraphQL plugin installed and activated"
else
  wp plugin activate wp-graphql --allow-root
  echo "WPGraphQL plugin activated"
fi

# Check and install Yoast SEO plugin
if ! $(wp plugin is-installed wordpress-seo --allow-root); then
  wp plugin install wordpress-seo --activate --allow-root
  echo "Yoast SEO plugin installed and activated"
else
  wp plugin activate wordpress-seo --allow-root
  echo "Yoast SEO plugin activated"
fi

# Activate WP GraphQL Yoast SEO plugin
if ! $(wp plugin is-active wp-graphql-yoast-seo --allow-root); then
  wp plugin activate wp-graphql-yoast-seo --allow-root
  echo "WP GraphQL Yoast SEO plugin activated"
fi

# Load dummy data if it doesn't exist
if [ $(wp post list --post_type=post --format=count --allow-root) -eq 0 ]; then
  wp post create --post_type=post --post_title="Test Post" --post_status=publish --allow-root
  echo "Test post created"
fi

if [ $(wp post list --post_type=page --format=count --allow-root) -eq 0 ]; then
  wp post create --post_type=page --post_title="Test Page" --post_status=publish --allow-root
  echo "Test page created"
fi

# Keep container running
tail -f /dev/null
