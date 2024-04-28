# BouseAuStage api

*Grp 4 : Maxime Batista / Kevin Fery / Pierre Ambroise*

## Installation

### Environnement

- Créer un fichier `.env.local`

- Y renseigner les variables d'environnement suivantes :

```bash
    # Si vous n'utilisez pas Docker
    DOCKER_ENABLED=0

    DB_NAME=db_name
    DB_HOST=db_host
    DB_USER=db_user
    DB_PASSWORD=db_password

    DATABASE_URL="mysql://$DB_USER:$DB_PASSWORD@$DB_HOST:3306/$DB_NAME?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

- Récupération du sous module

```bash 
    git submodule update --init
```

- Création de la base de donnée
    
```bash 
    make database-create
```

- Finalisation de l'installation

```bash 
    make deploy
```

### Fixtures

- Si vous souhaitez ajouter des fixtures, vous pouvez en charger avec la commande suivante :

```bash
    make database-fixtures
```
