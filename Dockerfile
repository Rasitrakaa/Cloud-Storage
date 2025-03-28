# Utilise une image officielle PHP avec Apache
FROM php:8.1-apache

# Installe les extensions nécessaires (PDO MySQL pour ta base de données)
RUN docker-php-ext-install pdo pdo_mysql

# Copie ton code dans le conteneur
COPY . /var/www/html/

# Définit le dossier public comme racine du serveur web
WORKDIR /var/www/html/public

# Expose le port 8080 (Render utilise ce port par défaut)
EXPOSE 8080

# Configure Apache pour écouter le port 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Démarre Apache
CMD ["apache2-foreground"]