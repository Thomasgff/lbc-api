Mon Projet
===
API permettant de créer, modifier, supprimer et récupérer des annonces.
Les annonces sont liées à une catégorie:
    - emploi
    - immobilier
    - auto
Une annonce doit posséder :
    - un titre
    - un contenu
Ainsi que deux champs dédiés uniquement à la catégorie Automobile :
    - Marque du véhicule
    - Modèle du véhicul

Les marques et les modèles de véhicules sont limités, tout véhicule non trouvé parmi cette liste sera refusé par l'API. 
Pour l'automobile, les marques et modèles disponibles seront :
    - Audi
        ○ Cabriolet, Q2, Q3, Q5, Q7, Q8, R8, Rs3, Rs4, Rs5, Rs7, S3, S4, S4 Avant,S4 Cabriolet, S5, S7, S8, SQ5, SQ7, Tt, Tts, V8
    - BMW
        ○ M3, M4, M5, M535, M6, M635, Serie 1, Serie 2, Serie 3, Serie 4, Serie 5, Serie 6, Serie 7, Serie 8
    - Citroen
        ○ C1, C15, C2, C25, C25D, C25E, C25TD, C3, C3 Aircross, C3 Picasso, C4, C4 Picasso, C5, C6, C8, Ds3, Ds4, Ds5

## Table des matières
----------------------------------------------------------------

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Exemples](#exemples)
- [Tests](#tests)


## Installation
----------------------------------------------------------------
1. Cloner le dépôt : 
```
    git clone https://github.com/Thomasgff/lbc-api
```
2. Installer les dépendances PHP : 
```
composer install
```
3. Configurer les variables d'environnement : configurez les valeurs appropriées dans le fichier .env.
4. Créer les images et conteneurs dans Docker:
```
    docker compose build
    docker-compose up -d app
    docker ps                   #afin de voir si vos conteneurs sont bien en route
```
RAPPEL: Docker doit être démarré sur la machine afin de pouvoir lancer ces commandes
5. Migrer la base de données:
```
    php bin/console make:migration
    php bin/console doctrine:migrations:migrate
```
6. Créer les catégories (important de respecter l'ordre pour les créations de catégories)
    - ouvrir une invite de commande widows (pas powershell)
```
    curl -X POST -H "Content-Type: application/json" -d "{\"nom\":\"emploi\"}" http://localhost:4000/api/categories
    curl -X POST -H "Content-Type: application/json" -d "{\"nom\":\"immobilier\"}" http://localhost:4000/api/categories
    curl -X POST -H "Content-Type: application/json" -d "{\"nom\":\"automobile\"}" http://localhost:4000/api/categories
```
7. Voici quelques commandes permettant de créer quelques entrées dans la base de données:
```
    curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"Developpeur full stack\", \"contenu\":\"Decouvrez le monde passionnant du developpement Full Stack\", \"idCategorie\":\"1\"}" http://localhost:4000/api/annonces
    curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"Product owner\", \"contenu\":\"Decouvrez le role essentiel du Product Owner\", \"idCategorie\":\"1\"}" http://localhost:4000/api/annonces
    curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"Villa avec piscine\", \"contenu\":\"A vendre villa avec piscine\", \"idCategorie\":\"2\"}" http://localhost:4000/api/annonces
    curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"Villa sans piscine\", \"contenu\":\"A vendre villa sans piscine\", \"idCategorie\":\"2\"}" http://localhost:4000/api/annonces
    curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"Ma grosse caisse\", \"contenu\":\"Ma grosse BM pour reveiller le quartier la nuit\", \"idCategorie\":3, \"modele\":\"bmw M635\"}" http://localhost:4000/api/annonces
    curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"Ma petite caisse\", \"contenu\":\"Ma petite Citroen pour ne pas reveiller ma femme quand je rentre tard a la maison\", \"idCategorie\":3, \"modele\":\"C1\"}" http://localhost:4000/api/annonces
```
## Utilisation
----------------------------------------------------------------
1. Obtenir la liste des annonces:
```
    curl -X GET http://localhost:4000/api/annonces
```
2. Obtenir une annonce précise:
```
    curl -X GET http://localhost:4000/api/annonces/{id}
```
3. Créer une annonce:
    - catégorie 1 (emploi) ou 2 (immo):
```
    curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"<renseigner le titre>\", \"contenu\":\"<renseigner le contenu>\", \"idCategorie\":<renseigner 1 pour emploi ou 2 pour immo>}" http://localhost:4000/api/annonces
```
    - catégorie 3 (automobile):
```
    curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"<renseigner le titre>\", \"contenu\":\"<renseigner le contenu>\", \"idCategorie\":3, \"modele\":\"<renseigner le modele de la voiture, la marque n'est pas necessaire>\"}" http://localhost:4000/api/annonces
```
4. Modifier une annonce:
```
    curl -X PUT -H "Content-Type: application/json" -d "{\"<champ 1>\":\"<nouvelle valeur>\"}" http://localhost:4000/api/annonces/{id}
```
5. Supprimer une annonce:
```
    curl -X DELETE http://localhost:4000/api/annonces/{id}
```

## Exemples
----------------------------------------------------------------
1. Obtenir une annonce précise:
```
    curl -X GET http://localhost:4000/api/annonces/2
```
2. Créer une annonce:
```
    curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"Audi Q7\", \"contenu\":\"Un petit 4x4\", \"idCategorie\":3, \"modele\":\"Q7\"}" http://localhost:4000/api/annonces
```
3. Modifier une annonce:
```
    curl -X PUT -H "Content-Type: application/json" -d "{\"titre\":\"nouveau titre>\"}" http://localhost:4000/api/annonces/3
```
4. Supprimer une annonce:
```
    curl -X DELETE http://localhost:4000/api/annonces/3
```








{
    "titre":"test 1 emploi",
    "contenu":"à voir si ça marche",
    "idCategorie": 2
}

curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"test 1 emploi\", \"contenu\":\"a voir si ca marche\", \"idCategorie\":2, \"modele\":null, \"marque\":null}" http://localhost:4000/api/annonces
curl -X POST -H "Content-Type: application/json" -d "{\"titre\":\"test avec accents\", \"contenu\":\"a voir si ça marche\", \"idCategorie\":3, \"modele\":\"bmw série 5\", \"marque\":null}" http://localhost:4000/api/annonces

curl -X DELETE http://localhost:4000/api/annonces/2

curl -X GET http://localhost:4000/api/annonces

curl -X PUT -H "Content-Type: application/json" -d "{\"idCategorie\":1}" http://localhost:4000/api/annonces/7



Tests:

{
    "titre":"test auto sans modèle",
    "contenu":"ça va bloquer? j'espère que si, si tu vois ce message c'est que non :(",
    "idCategorie": 3
}


{
    "titre":"test emploi sans modèle",
    "contenu":"ça devrait passer",
    "idCategorie": 1
}

curl -X PUT -H "Content-Type: application/json" -d "{\"titre\":\"l'emploi est une voiture maintenant\", \"contenu\":\"on va modifier l'annonce 7 et la passer dans la catégorie auto mais sans ajouter de modele...\", \"idCategorie\": 3}" http://localhost:4000/api/annonces/7
Postman:
{
    "titre":"l'emploi est une voiture maintenant",
    "contenu":"on va modifier l'annonce 7 et la passer dans la catégorie auto mais sans ajouter de modele...",
    "idCategorie": 3
}


curl -X PUT -H "Content-Type: application/json" -d "{\"idCategorie\":1}" http://localhost:4000/api/annonces/7
{
    "titre":"l'emploi est une citroën maintenant",
    "contenu":"on va modifier l'annonce 7 et la passer dans la catégorie auto avec un modèle cette fois",
    "idCategorie": 3,
    "modele": "CrossBack ds 3"
}