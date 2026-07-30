# Personnalisation Dockerfile

Towerify génère une image par défaut qui permet de publier les pages statiques de votre application
sur un site Internet.

Cette image par défaut utilise [l'image Docker officielle](https://hub.docker.com/_/nginx) de 
[nginx](https://nginx.org/) depuis un `Dockerfile` comme celui-ci :

``` Dockerfile
FROM nginx
COPY ./src /usr/share/nginx/html
```

Vous pouvez avoir besoin de modifier cette image, par exemple, si vous voulez utiliser Apache plutôt
que nginx pour publier votre site statique. Dans ce cas, créer un fichier `Dockerfile` puis modifiez
la configuration de cette façon :

``` yaml 
name: my-app
type: static
config:
    dockerfile: ./towerify/Dockerfile
```

Changer nginx par apache n'est pas le meilleur exemple. Vous pouvez avoir besoin de modifier le
`Dockerfile` car vous avez besoin de générer les fichiers statiques de votre site avant de
pouvoir les publier. Ce serait le cas si vous utiliser Material for MkDocs pour écrire une documentation.
Dans ce cas, vous pouvez utiliser un `Dockerfile` de ce type :

``` Dockerfile
FROM python:3 AS build
WORKDIR /usr/src/app
RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir mkdocs-material
COPY . .
RUN mkdocs build

FROM nginx AS publish
COPY --from=build /usr/src/app/site/ /usr/share/nginx/html/
```
