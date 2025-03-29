# Image de base avec PHP et Apache
FROM php:8.1-apache

# Installe les dépendances nécessaires pour pdo_pgsql
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copie tout ton code dans le conteneur
COPY . /var/www/html/

# Définit le dossier public comme racine du serveur web
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Active le module rewrite (optionnel, mais utile pour les URL propres si besoin)
RUN a2enmod rewrite

# Expose le port 8080 (standard pour Render)
EXPOSE 8080

# Configure Apache pour écouter le port 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Image de base avec PHP et Apache
FROM php:8.1-apache

# Installe les dépendances nécessaires pour pdo_pgsql
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copie tout ton code dans le conteneur
COPY . /var/www/html/

# Définit le dossier public comme racine du serveur web
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Active le module rewrite (optionnel, mais utile pour les URL propres si besoin)
RUN a2enmod rewrite

# Crée le dossier uploads/ et définit les permissions
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads

# Expose le port 8080 (standard pour Render)
EXPOSE 8080

# Configure Apache pour écouter le port 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf


# Démarre Apache
CMD ["apache2-foreground"]