API permettant une utilisation FULLSTACK du projet numéro 7 de la formation Developpeur web d'Openclassroom : **Créez une application web de location immobilière avec React**

## Comment tester l'API

**En local, via des conteneurs Docker**

Docker est un applicatif permettant de faire fonctionner des micro-machines virtuelles. Docker doit être installé et lancé avant d'executer les commandes suivantes depuis un terminal pointant à la racine du projet.

- Sous Linux : wsl docker compose up -d
- Sous Windows avec WSL (sous-système Linux) : wsl docker compose up -d
- Sous MAC : avec Docker Desktop

**En local, via FrankenPHP et un service MySQL local**

1. Installer et configurer MySQL en version 8, le service dit être démarré pour que l'API soit fonctionnelle
2. Installer et configurer FrankenPHP
3. Ajouter les informations de connexion à la base de données dans le fichier ".env"
4. Ouvrir un terminal et le faire pointer dans le dossier "public" du projet puis executer la commande : frankenphp php-server

**Initialiser la base de données**

1. Pour créer la structure de la base (ajouter "wsl" au début de la commande sous Windows) : docker compose exec php php bin/console doctrine:migrations:migrate
2. Pour ajouter les données de test : docker compose exec php php bin/console populate-db