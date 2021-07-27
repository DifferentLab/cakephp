FROM php:7.4-cli

# clean up
RUN apt clean && apt-get update
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure mysqli --with-mysqli=mysqlnd \
    && docker-php-ext-install pdo_mysql

#RUN apt-get install -y php7.4-intl
#RUN docker-php-ext-configure intl && docker-php-ext-install
RUN apt-get install -y locales
RUN locale-gen de_DE \
	&& locale-gen es_ES

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY memory.ini $PHP_INI_DIR/conf.d/memory.ini