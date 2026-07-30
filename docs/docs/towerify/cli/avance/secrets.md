# Secrets

Towerify vous permet d'associer un ou plusieurs secrets à chacune de vos applications pour
chacun des environnements.

Un secret est une valeur dont votre application a besoin mais que vous ne pouvez pas 
mettre directement dans un fichier de votre application comme un mot de passe ou
une clé d'API.

## Ajouter un secret

``` bash
towerify secrets set MY_KEY=my-secret-value
```

``` bash
towerify secrets set MY_KEY=my-secret-value --env prod
```

## Supprimer un secret

``` bash
towerify secrets unset MY_KEY
```

``` bash
towerify secrets unset MY_KEY1 MY_KEY2 MY_KEY3
```

``` bash
towerify secrets unset MY_KEY --env prod
```

## Lister les secrets

``` bash
towerify secrets list
```

``` bash
towerify secrets list --env prod
```
