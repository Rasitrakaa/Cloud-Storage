# Image de base avec PHP et Apache
FROM php:8.1-apache

# Installe PDO MySQL pour la connexion à la base
RUN docker-php-ext-install pdo pdo_mysql

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

# Démarre Apache
CMD ["apache2-foreground"]