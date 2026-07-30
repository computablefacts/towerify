# Application Laravel 10

Cet article vous explique les options que vous pouvez modifier pour changer le comportement
par défaut de Towerify pour votre application Laravel.

Le reste de ce document considère que le domaine principal de votre serveur Towerify 
est `acme.towerify.io` et que vous avez nommé votre application Laravel `my-app`.

Towerify a donc créé un fichier de configuration `towerify/config.yaml` qui contient :

``` yaml 
name: my-app
type: laravel-10
```
ou 
``` yaml 
name: my-app
type: laravel-9
```
en fonction de la version de Laravel que vous utilisez.

C'est en modifiant ce fichier que vous pouvez changer le comportement de Towerify.

!!! warning "todo"

    Les options ci-dessous ne sont pas encore opérationnelles


## Version de PHP

``` yaml 
name: my-app
type: laravel-10
config:
  php: 8.2
```

## Version de Node / NPM

``` yaml 
name: my-app
type: laravel-10
config:
  node: 18
  npm: 10
```

## Version de Redis

``` yaml 
name: my-app
type: laravel-10
config:
  redis: 6.0
```

## Personnaliser les options de PHP

``` yaml 
name: my-app
type: laravel-10
config:
  php.ini:
    app: php.app.ini
    scheduler: php.scheduler.ini
    queue: php.queue.ini
```


