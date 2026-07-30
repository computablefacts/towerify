# Fonctionnement

Ce document détail comment les demandes de changement (encore appelées commandes) émises par la tour de contrôle
circulent dans le système. Plus spécifiquement, ce document couvre :

- Le fonctionnement de la [tour de contrôle](#la-tour-de-controle) ;
- Le fonctionnement des [hôtes](#les-hotes) ;
- Le mécanisme d'[émission des demandes de changement](#emission-dune-demande-de-changement) par la tour de contrôle.
- Le mécanisme d'[exécution des demandes de changement](#execution-dune-demande-de-changement) par les hôtes.

Il est fortement conseillé de jeter un oeil au [diagramme d'architecture](overview.md#diagramme-darchitecture) de
Towerify Cloud avant de se lancer dans la lecture de ce document.

## La tour de contrôle

La tour de contrôle permet de provisionner des __hôtes__, d'appliquer des mises à jour système ou encore de déployer des
applications. Ces modifications d'infrastructure s'effectuent via l'émission de __demandes de changement__.

La tour de contrôle opère sur quatre types d'entités :

- Les __serveurs__ sont des machines physiques ou virtuelles à laquelle la tour de contrôle peut se connecter pour
  exécuter des demandes de changement.
- Les __applications__ sont des programmes développés en interne ou provenant de notre [catalogue](one-click-apps.md)
  et servant à résoudre un problème métier ou technique.
- Les __opérateurs__ sont les utilisateurs de la tour de contrôle. Ils administrent les hôtes ou certaines applications
  déployées sur les hôtes.
- Les __permissions__ permettent de décrire le périmètre fonctionnel des opérateurs.

Ces entités sont regroupées au sein de catalogues permettant d'administrer celles-ci aisément :

- Le __catalogue de produits__ permet de gérer la liste des serveurs pouvant être provisionnés par un opérateur et
  des applications pouvant être déployées sur ces serveurs ;
- Le __catalogue d'opérateurs__ permet de lister les utilisateurs de la tour de contrôle et d'attribuer ou de révoquer
  les permissions associées à un opérateur particulier.

Le catalogue de produits est lui-même sous-divisés en deux catégories :

- Le __catalogue de serveurs__ représente le matériel pouvant être provisionné par un opérateur pour être transformé en
  hôte ;
- Le __catalogue d'applications__ représente les applicatifs pouvant être déployés sur un serveur transformé en hôte.

La tour de contrôle est une application [multi-tenant](https://en.wikipedia.org/wiki/Multitenancy) et peut donc être
configurée pour être utilisée par plusieurs organisations simultanément.

## Les hôtes

Les hôtes sont des serveurs physiques ou virtualisés appartenant à une organisation. Ils exécutent les demandes de
changement émises par la tour de contrôle pour atteindre un état cible.

__Il n'est pas nécessaire que la tour de contrôle soit connectée à un hôte pour que celui-ci fonctionne!__ En effet, il
est tout à fait possible de déployer une ou plusieurs applications sur un hôte à l'aide de la tour de contrôle puis de
révoquer l'accès de la tour de contrôle à l'hôte sans pour autant que les applications déployées ne cessent de
fonctionner.

??? warning "Contraintes..."

    Les hôtes provisionnés par l'organisation doivent avoir [Linux Debian](https://www.debian.org/) pour système 
    d'exploitation. Les hôtes doivent être configurés pour être accessibles en SSH par la tour de contrôle. Lors de 
    l'enregistrement d'un hôte auprès de la tour de contrôle, celui-ci sera reformaté pour utiliser la distribution
    [YunoHost](https://yunohost.org/).

## Emission d'une demande de changement

Les demandes de changement sont des petits "programmes" décrivant les actions à effectuer sur un hôte pour que celui-ci
atteigne un état cible.

Les demandes de changement sont émises par un opérateur de la tour de contrôle et sont placées dans une file d'attente.
Cette file d'attente est surveillée par une tâche planifiée qui a en charge de choisir la prochaine demande de
changement à effectuer et de router cette demande vers l'hôte cible. Le choix de la prochaine demande de changement à
exécuter est réalisé en appliquant le principe "premier arrivé, premier servi" (FIFO).

Les demandes de changement concernant des hôtes différents s'exécutent en parallèle. Cependant, à un instant donné, une
unique demande de changement est en cours d'exécution sur chaque hôte.

## Exécution d'une demande de changement

La demande de changement émise par la tour de contrôle est envoyée à l'hôte concerné. La demande de changement est
ensuite appliquée sous forme d'une série d'étapes. Après l'exécution de chaque étape un retour est fait à la tour de
contrôle pour signifier le succès ou l'échec de l'exécution de cette étape. En cas d'échec, un retour arrière est
exécuté automatiquement. 

Toutes les modifications réalisées sur un hôte sont tracées pour pouvoir être auditées ultérieurement. 