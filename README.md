ArchiStore
À propos du projet

ArchiStore est une plateforme web conçue pour un cabinet d'architectes afin de faciliter la gestion des fichiers clients de manière sécurisée. Elle permet aux clients de s'inscrire, de se connecter, d'accéder à leur espace de stockage personnel pour visualiser, uploader, et supprimer leurs fichiers. En outre, les clients peuvent acheter de l'espace de stockage supplémentaire et supprimer leur compte si nécessaire. Pour le cabinet (super admin), la plateforme offre une vue d'ensemble des clients, la gestion de l'espace de stockage, et un accès aux fichiers clients, ainsi qu'un tableau de bord avec des statistiques d'utilisation.
Utilité

Ce système est essentiel pour le cabinet d'architectes afin de :

    Sécuriser les fichiers clients.
    Offrir une gestion centralisée et facile d'accès des fichiers.
    Permettre une gestion efficace de l'espace de stockage.
    Fournir des insights via des statistiques sur l'utilisation de la plateforme.

Prérequis

Pour installer et exécuter cette plateforme, vous aurez besoin de :

    PHP et Composer pour la gestion du backend avec Symfony.
    MySQL pour la base de données.
    Node.js et npm (ou Yarn) pour la gestion des dépendances front-end et la compilation des assets avec Webpack Encore.
    Un serveur web comme Apache ou Nginx.

Installation

    Cloner le repository :

    bash

git clone url_du_repository ArchiStore
cd ArchiStore

Installer les dépendances PHP avec Composer :

bash

composer install

Configurer l'environnement :

    Copiez .env en .env.local et ajustez les paramètres de la base de données.

Créer la base de données et les tables :

bash

php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force

Installer les dépendances front-end et compiler les assets :

bash

npm init
npm install
npm run build

Démarrer le serveur de développement :

bash

    symfony server:start

Visitez http://localhost:8000 dans votre navigateur pour accéder à ArchiStore.


Pour l'ajout de fichier
il faut savoir que php  des tailles par défaut, il faut donc modifier le fichier php.ini
C:\wamp64\bin\apache\apacheX.X.X\bin\php.ini
Ouvrir le fichier php.ini et cherche ces directives
upload_max_filesize = 2M
post_max_size = 8M
les modifier comme suit
upload_max_filesize = 20G
post_max_size = 20G