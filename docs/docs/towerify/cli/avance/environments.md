# Environnements

Par défaut, Towerify publie votre application dans un environnement appelé `dev` (comme développement).
Votre application est publiée à la racine (`/`) du domaine `dev.my-app.acme.towerify.io`. Son URL est donc
`https://dev.my-app.acme.towerify.io/`.

## Ajouter un environnement

Vous pouvez publier votre application dans autant d'environnements que vous le souhaitez en précisant
le nom de l'environnement de cette façon :

``` bash
towerify deploy --env production
```

Dans l'exemple ci-dessus, l'application de votre environnement `production` aura l'URL 
`https://production.my-app.acme.towerify.io/`.

Donc, de façon générique, si vous ajouter un environnement `<env-name>` pour une application
`<app-name>` en déployant l'application avec `towerify deploy --env <env-name>` alors l'URL
de l'application sera `https://<env-name>.<app-name>.acme.towerify.io/`.

## Personnaliser l'URL d'un environnement

Si vous préférez publier `dev` avec l'URL `https://my-app.acme.towerify.io/dev/`, modifiez
la configuration de cette façon :

``` yaml 
name: my-app
type: static
config:
  envs:
    dev:
      domain: my-app.acme.towerify.io
      path: dev
```

!!! warning "Un environnement ne peut pas être contenu dans un autre" 

    Si votre environnement `production` a comme URL https://my-app.acme.towerify.io/ alors
    vous ne pouvez pas avoir l'environnement `dev` avec l'URL https://my-app.acme.towerify.io/dev/

Vous pouvez donc personnaliser le domaine et le chemin de l'URL. Le domaine peut être n'importe
quel sous domaine du domaine principal de votre serveur Towerify. Par exemple :

* `www.my-app.acme.towerify.io`
* `my-app.acme.towerify.io`
* `new-name.acme.towerify.io`
* `very.complicated.domain.for.my-app.acme.towerify.io`

Le chemin peut être n'importe quel chemin. Par exemple :

* dev
* my-app
* my-app/prod
* very/complicated/path/for/my-app

Vous pouvez paramétrer les domaines et les chemins de plusieurs environnements dans le fichier
de configuration. Par exemple :

``` yaml 
name: my-app
type: static
config:
  envs:
    dev:
      domain: dev.my-app.acme.towerify.io
      path: dev
    test:
      domain: dev.my-app.acme.towerify.io
      path: test
    prod:
      domain: my-app.acme.towerify.io
```

Vous aurez alors 3 environnements :

* `dev` avec l'URL `https://dev.my-app.acme.towerify.io/dev/`
* `test` avec l'URL `https://dev.my-app.acme.towerify.io/test/`
* `prod` avec l'URL `https://my-app.acme.towerify.io/`

### Avoir votre propre domaine

Vous pouvez avoir votre propre domaine également (contactez-nous, nous vous dirons comment
paramétrer votre DNS).

Imaginons que vous ayez le domaine `my-app.com` paramétré correctement, vous pouvez alors
utiliser n'importe quel sous domaine de ce domaine. Par exemple :

``` yaml 
name: my-app
type: static
config:
  envs:
    dev:
      domain: dev.my-app.acme.towerify.io
    v1:
      domain: v1.my-app.com
    v2:
      domain: v2.my-app.com
    preprod:
      domain: preprod.my-app.com
    prod:
      domain: my-app.com
```
