FROM wordpress:latest

# Install any additional PHP extensions or tools if needed
RUN apt-get update && apt-get install -y \
  less \
  mariadb-client \
  && rm -rf /var/lib/apt/lists/*

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
  && chmod +x wp-cli.phar \
  && mv wp-cli.phar /usr/local/bin/wp

# Copy over setup script
COPY setup.sh /usr/local/bin/setup.sh
RUN chmod +x /usr/local/bin/setup.sh

# Set the entrypoint to our setup script
CMD ["/usr/local/bin/setup.sh"]
