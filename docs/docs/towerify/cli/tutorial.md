# Publier votre première application

Cet article explique comment déployer une application de type statique. 

C'est le type le plus simple : l'application est un ensemble de pages Internet (HTML, CSS, JS, etc)
que vous souhaitez publier pour la rendre accessible à vos utilisateurs grâce à une URL
du type https://my-app.acme.towerify.io/.

??? warning "TODO: une application exemple"

    Nous pourrions créer une repo exemple avec une application statique.
    Notre client pourrait alors la cloner sur son PC puis dérouler cette procédure
    pour la publier sur son Towerify

    Ce pourrait être une app TODO list comme
    [comme celle-ci](https://www.w3schools.com/howto/howto_js_todolist.asp).



## Installation de l'outil

``` bash
curl -sL https://cli.towerify.io/install.sh | bash
```

Towerify télécharge et installe notre outil en ligne de commande :

``` output linenums="0"
Installation de Towerify pour l'instance acme.towerify.io
##################################################################### 100,0%
```

Towerify confirme l'installation :

``` output linenums="0"
Towerify CLI est installé pour l'instance acme.towerify.io

Pour le configurer avec vos login et mot de passe, utilisez :
  towerify configure
```

!!! tips

    Ajoutez le domaine de votre propre Towerify au bout de la commande. Par exemple, si votre domaine
    est my-corp.towerify.io, faîtes :

    ``` bash
    curl -sL https://cli.towerify.io/install.sh | bash -s -- my-corp.towerify.io
    ```

## Configuration des paramètres d'accès

``` bash
towerify configure
```

Towerify vous demande votre domaine, votre login et votre mot de passe :

``` output linenums="0"
? Quel est le domaine de votre Towerify ?
> acme.towerify.io

? Quel est votre login Towerify ?
> jdoe

? Quel est votre mot de passe Towerify ?
(Par sécurité, les caractères que vous tapez ne s'afficheront à l'écran)
> 
```

Towerify confirme l'installation :

``` output linenums="0"
Tentative de connexion à Towerify... ==> Connexion réussie.

Towerify CLI est correctement configuré pour l'instance Towerify acme.towerify.io

Pour déployer votre première application, allez dans le répertoire de votre 
application et utilisez :
  towerify init
```

!!! tips

    Vous pouvez donner vos paramètres d'accès directement dans la commande :

    ``` bash
    towerify configure --domain my-corp.towerify.io --login jdoe --password P@ssw0rd
    ```


## Initialisation de votre première application

Vous devez vous placer dans le répertoire de votre application avant de faire
la commande.

``` bash
towerify init
```

``` output linenums="0"
? Quel est le nom de votre application ?
> my-app
```

``` output linenums="0"
? Choissisez le type de votre application ?
1) static
2) laravel-10
3) laravel-9
Votre choix : 1

```

``` output linenums="0"
Application my-app initialisée

Vous pouvez maintenant déployer votre application avec :
    towerify deploy
```


## Déploiement de votre application

Vous devez vous placer dans le répertoire de votre application avant de faire
la commande.

``` bash
towerify deploy
```

``` output linenums="0"
Tentative de connexion à Towerify ..................................... [OK]
Vérification du pipeline de déploiement ............................... [OK]
Envoi des secrets ..................................................... [OK]
Compression des fichiers de votre application ......................... [OK]
Lancement du déploiement .............................................. [OK]
Le job est terminé avec le statut : SUCCESS ........................... [OK]
==> Le job a réussi.
Application compressée dans ./towerify/dev/app.20240205-210918.tar.gz

Vous pouvez accéder à votre application avec :
https://dev.my-app.acme.towerify.io/

```

Vous pouvez visiter le lien fourni par Towerify (https://dev.my-app.acme.towerify.io/)
pour afficher votre application dans votre navigateur.

??? failure "Pas de fichier `towerify/config.yaml`"

    Si le fichier de configuration n'existe pas, on affiche une erreur :
    ``` output linenums="0"
    Impossible de publier une application depuis ce répertoire.
    Vous devez d'abord initialiser votre application avec :
        towerify init
    ```


## Et maintenant ?

Vous pouvez personnaliser la configuration de votre application en fonction de son type :
   
* options pour une [application statique](app-static.md)
* options pour une [application Lavarel](app-laravel.md)

Vous pouvez également utiliser des options communes à tous les types d'application :

* options pour publier dans [plusieurs environnements](avance/environments.md)
* options pour utiliser [votre propre Dockerfile](avance/mydockerfile.md)
