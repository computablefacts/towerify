# Application statique

Cet article vous explique les options que vous pouvez modifier pour changer le comportement
par défaut de Towerify pour votre application statique.

Le reste de ce document considère que le domaine principal de votre serveur Towerify 
est `acme.towerify.io` et que vous avez nommé votre application statique `my-app`.

Towerify a donc créé un fichier de configuration `towerify/config.yaml` qui contient :

``` yaml 
name: my-app
type: static
```

C'est en modifiant ce fichier que vous pouvez changer le comportement de Towerify.

## Répertoire de vos fichiers statiques

Par défaut, Towerify considère que les fichiers statiques de votre application se 
trouvent à la racine `.`.

Si vos fichiers se trouvent dans le sous-répertoire `./public` vous pouvez modifier
la configuration de cette façon :

``` yaml 
name: my-app
type: static
config:
    webroot: ./public 
```

!!! warning "TODO: cette option n'existe pas pour le moment"
