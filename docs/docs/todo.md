---
search:
  exclude: true
---

# TODO

Je liste ici les pages de documentation qu'il faudrait faire ensuite.

- [ ] Utiliser sa propre registry Docker
- [ ] Comment mettre son application dans une repo Git
    - [ ] avec GitHub
    - [ ] avec GitLab
- [ ] Limiter le CPU et la mémoire du Docker en modifiant `docker-compose.yaml`
- [ ] Décrire les outils mis à disposition par Towerify (Jenkins, Portainer, MySQL, etc)
- [ ] Décrire l'utilisation des branches pour 3 environnements dev, staging, prod avec, éventuellement, l'utilisation de git flow pour les gérer.




!!! warning "Repo Docker ?"

    Il faut une repo Docker pour pousser les images dedans. CF utilise Docker Hub mais ce serait mieux si 
    nous pouvions déployer une repo Docker dans Towerify.

    La [registry officielle](https://hub.docker.com/_/registry), utilisée aussi par CapRover, serait idéale.
    Mais elle n'existe pas sous la forme d'app YunoHost donc il faudrait que nous la packagions (certainement
    plus complexe qu'il n'y paraît à cause de l'accès à cette registry depuis les autres containers Docker 
    comme Jenkins ou celui de l'app du client).

    Pour l'instant, je préfère partir sur l'utilisation du compte CF sur Docker Hub pour y pousser les images
    de nos clients (elles seront privées ?). Ensuite, nous pourrons ajouter une page dans cette documentation
    pour expliquer comment le client peut configurer Jenkins et le pipeline Jenkins pour utiliser sa propre
    registry privée.
