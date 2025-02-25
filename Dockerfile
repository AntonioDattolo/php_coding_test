# Usa un'immagine base di PHP con Apache
#sto usando php 8.2 e apache
FROM php:8.2-apache

# Installa le dipendenze di sistema necessarie

#aggiorna la lista dei pacchetti
RUN apt-get update && apt-get install -y \
#git per gestire codice sorgente 
    git \
#unzip per gestire file zip(in caso)
    unzip \
#librerie per estensioni php 
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-install \
#gestire sempre i file zip
    zip \
#essenziali per connettersi a MySQL
    pdo_mysql \
    mysqli \
#supportare internalizzazione
    intl \
    mbstring \
#ottimizzare le prestazioni di php
    opcache \
#metodo rewrite du Apache, necessario per il routing
    && a2enmod rewrite

# Installa Composer utilizzando un'immagine multi-stage
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Imposta la directory di lavoro
WORKDIR /var/www/html

# Copia i file del progetto
COPY . .

# Installa le dipendenze di Composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Configura Apache per Symfony
##variabile di ambiente Apace
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Esponi la porta 80, porta principali per il traffico HTTP
EXPOSE 80

# Comando per avviare Apache
CMD ["apache2-foreground"]

