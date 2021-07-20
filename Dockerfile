FROM php:7.4-cli

# clean up
RUN apt clean && apt-get update
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure mysqli --with-mysqli=mysqlnd \
    && docker-php-ext-install pdo_mysql

COPY memory.ini $PHP_INI_DIR/conf.d/memory.ini