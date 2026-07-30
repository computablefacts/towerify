![](../../img/logo-towerify.svg)

# C'est quoi ?

Towerify est notre tour de contrôle. 
Il facilite l'emballage, le déploiement et la réutilisation des applications et des services. 
Enregistrez votre application et regardez Towerify tout tenir à jour et en bonne santé.

Towerify met à votre disposition un ensemble d'outils vous permettant de publier vos applications
dans plusieurs environnements.

!!! warning "TODO"

    Lien vers une page décrivant les outils mis à disposition (Jenkins, phpMyAdmin, Portainer, etc).


# Objectif

Towerify vous permet d'automatiser la publication de votre application
dans plusieurs environnements en fonction du code que vous mettez à jour dans votre repo Git.


# Prérequis

Aucun.

Installez notre outil en ligne de commande, *Towerify CLI*, puis utilisez le pour déployer vos
applications.

# Principe

Une première commande, `towerify init`, initialise le répertoire de votre application. Une
deuxième commande, `towerify deploy`, paramètre votre *instance Towerify* et déclenche la
publication de votre application dessus.

Towerify gère la complexité à votre place :

* Towerify packagera votre application dans une image Docker grâce à un `Dockerfile`.
* Towerify publiera votre application grâce à Docker Compose et sa configuration dans un `docker-compose.yaml`.
* Towerify automatisera la création de l'image Docker et sa publication avec Docker Compose grâce à un pipeline Jenkins
  configuré dans un `Jenkinsfile`.

# Et maintenant ?

Après cette présentation, vous vous dîtes certainement : 

> Très bien. Tout cela est intéressant mais je ne connais ni Docker, ni Jenkins, donc encore moins comment 
> écrire un `Dockerfile`, un `Jenkinsfile` ou un `docker-compose.yaml`.

Pas de panique, cette documentation de Towerify va vous guidez pas à pas pour déployer votre application
en 2 commandes : suivez notre [tutoriel pour un démarrage rapide](tutorial.md).

Vous pourrez ensuite personnaliser le comportement de Towerify en fonction du type d'application que
vous voulez déployer.

Et, si vous savez écrire un `Dockerfile` ou un `docker-compose.yaml`, vous pourrez demander à
Towerify d'utiliser les vôtres.
