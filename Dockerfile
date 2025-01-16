FROM wordpress:latest

# Install any additional PHP extensions or tools if needed
RUN docker-php-ext-install mysqli

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
  && chmod +x wp-cli.phar \
  && mv wp-cli.phar /usr/local/bin/wp
