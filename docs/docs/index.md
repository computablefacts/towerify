![](./img/logo-towerify.svg)

# Introduction

Towerify est une plateforme extensible et adaptée au déploiement de tout type d'applications. Towerify utilise des
méthodes éprouvées pour assurer le déploiement de vos applications les plus critiques. En centralisant la gestion de vos
serveurs, Towerify simplifie le maintien en condition opérationnelle de votre système d'information.

L'objectif de Towerify est de simplifier la vie des développeurs et des administrateurs systèmes pour que ceux-ci
puissent se concentrer sur l'innovation et la création de valeur, en rendant simple le déploiement d'applications quel
qu'en soit l'environnement d'exécution. Avec Towerify, les développeurs et les administrateurs systèmes n'ont plus
besoin de perdre un temps précieux à automatiser les tâches de déploiement ou à devenir des experts des différents
fournisseurs d'infrastructure cloud ou bare metal.

# Le problème

Avec l'introduction continue de nouvelles réglementations telles la Réglementation Générale sur la Protection des
Données (RGPD), de nouvelles lois telles que la loi sur la cyberrésilience (CRA) et la mise en place de labels tels que
SecNumCloud, de plus en plus d'organisations font faces à un besoin croissant de déployer une même application dans de
nombreux environnements d'exploitations différents.

La multiplication de ces environnements et l'évolution rapide des besoins métiers oblige les organisations à déployer en
continu des mises à jour applicatives pour fournir aussi bien des patchs de sécurité que de nouvelles fonctionnalités
aux utilisateurs finaux.

Towerify permet à une organisation de s'assurer que ses ressources humaines restent tournées vers l'innovation tout en
conservant la certitude que le code développé peut-être déployé partout où cela s'avèrerait nécessaire.

# La solution

**Towerify CLI** est une ligne de commande permettant de déployer des applications développées par vos
soins, au moyen d'un processus d'intégration continue.

# La démo

<div class="video-wrapper">
  <iframe width="560" height="315" src="https://www.youtube.com/embed/nvQmI5Pls6g?si=HPXVd_zYPsEqSNNm" frameborder="0" title="Towerify" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
</div>

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
en 2 commandes : suivez notre [tutoriel pour un démarrage rapide](towerify/cli/tutorial.md).

Vous pourrez ensuite personnaliser le comportement de Towerify en fonction du type d'application que
vous voulez déployer.

Et, si vous savez écrire un `Dockerfile` ou un `docker-compose.yaml`, vous pourrez demander à
Towerify d'utiliser les vôtres.
