#!/bin/bash

# Wait for the database to be ready
until wp db check --path=/var/www/html --allow-root; do
  echo "Waiting for database..."
  sleep 5
done

# Install WordPress
wp core install --url="http://localhost:8000" --title="WPGraphQL Yoast SEO Test" --admin_user="admin" --admin_password="password" --admin_email="admin@example.com" --skip-email --allow-root

# Install and activate plugins
wp plugin install wp-graphql --activate --allow-root
wp plugin install wordpress-seo --activate --allow-root
wp plugin activate wp-graphql-yoast-seo --allow-root

# Load dummy data
wp post create --post_type=post --post_title="Test Post" --post_status=publish --allow-root
wp post create --post_type=page --post_title="Test Page" --post_status=publish --allow-root
