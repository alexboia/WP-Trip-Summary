# syntax=docker/dockerfile:1

ARG WORDPRESS_RUNTIME_IMAGE=wordpress:7.1.4-php8.3-apache

FROM composer:2 AS composer
FROM wordpress:cli-2.12.0-php8.3 AS cli

# Resolve the committed production dependencies on the actual target PHP.
# The host's vendor directory and development dependencies are never copied.
FROM ${WORDPRESS_RUNTIME_IMAGE} AS dependencies
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /opt/wpts-plugin
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress \
		--no-plugins --no-scripts --optimize-autoloader \
	&& composer check-platform-reqs --no-dev

FROM ${WORDPRESS_RUNTIME_IMAGE}
ARG WORDPRESS_VERSION=7.0
ARG WPTS_PHP_BRANCH=8.3
ARG WORDPRESS_RUNTIME_IMAGE
LABEL org.opencontainers.image.title="WP Trip Summary isolated compatibility site" \
	com.alexboia.wpts.wordpress-version="${WORDPRESS_VERSION}" \
	com.alexboia.wpts.php-branch="${WPTS_PHP_BRANCH}" \
	com.alexboia.wpts.wordpress-runtime-image="${WORDPRESS_RUNTIME_IMAGE}"
ENV WPTS_EXPECTED_WORDPRESS_VERSION="${WORDPRESS_VERSION}" \
	WPTS_EXPECTED_PHP_BRANCH="${WPTS_PHP_BRANCH}"
COPY --from=cli /usr/local/bin/wp /usr/local/bin/wp

# Keep the official Docker config template, but replace the whole bundled core.
# In particular, the minimum profile must run WordPress 7.0, not the newer core
# that happens to ship in the maintained PHP 8.3 runtime image.
RUN find /usr/src/wordpress -mindepth 1 -maxdepth 1 ! -name 'wp-config-docker.php' -exec rm -rf '{}' + \
	&& php -d memory_limit=512M /usr/local/bin/wp --allow-root core download --version="${WORDPRESS_VERSION}" \
		--path=/usr/src/wordpress --force \
	&& php -r 'exit(PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION === getenv("WPTS_EXPECTED_PHP_BRANCH") ? 0 : 1);'

COPY abp01-plugin-*.php index.php .htaccess /usr/src/wordpress/wp-content/plugins/wp-trip-summary/
COPY LICENSE.md /usr/src/wordpress/wp-content/plugins/wp-trip-summary/license.txt
COPY README.txt /usr/src/wordpress/wp-content/plugins/wp-trip-summary/readme.txt
COPY lib/ /usr/src/wordpress/wp-content/plugins/wp-trip-summary/lib/
COPY views/ /usr/src/wordpress/wp-content/plugins/wp-trip-summary/views/
COPY media/ /usr/src/wordpress/wp-content/plugins/wp-trip-summary/media/
COPY lang/ /usr/src/wordpress/wp-content/plugins/wp-trip-summary/lang/
COPY data/dev/setup/ /usr/src/wordpress/wp-content/plugins/wp-trip-summary/data/setup/
COPY --from=dependencies /opt/wpts-plugin/vendor/ /usr/src/wordpress/wp-content/plugins/wp-trip-summary/vendor/
RUN mkdir -p /usr/src/wordpress/wp-content/plugins/wp-trip-summary/data/cache \
		/usr/src/wordpress/wp-content/plugins/wp-trip-summary/data/storage \
	&& chown -R www-data:www-data /usr/src/wordpress

COPY docker-images/compatibility/uploads.ini /usr/local/etc/php/conf.d/zz-wpts-uploads.ini
COPY --chmod=0755 docker-images/compatibility/entrypoint.sh /usr/local/bin/wpts-compat-entrypoint
# Checkouts made on Windows may use CRLF even for executable shell scripts.
RUN sed -i 's/\r$//' /usr/local/bin/wpts-compat-entrypoint
ENTRYPOINT ["wpts-compat-entrypoint"]
CMD ["apache2-foreground"]
