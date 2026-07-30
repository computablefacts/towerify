# Auto-hébergement

La procédure ci-dessous décrit les étapes à suivre pour déployer une *instance Towerify* sur votre
propre serveur afin d'utiliser *Towerify CLI* pour déployer votre applications dessus.

## Pré-requis

Vous devez avoir un serveur avec Debian 11 (Bullseye) pré-installé. Ce serveur doit avoir un minimum
de 512Mo de mémoire vive et un minimum de 16Go de disque.

Vous devez accéder à la ligne de commande (bash) de votre serveur soit directement soit en utilisant
SSH et vous devez être connecté en tant que root (ou utiliser `sudo`).

## Configuration d'un nom de domaine

Pour fonctionner correctement, votre *instance Towerify* devra être reliée à un nom de domaine. Votre 
serveur doit avoir une adresse IP publique (une adresse IP fixe i.e. qui ne change pas dans le temps).
Il peut s'agir d'une adresse IP v4 et/ou d'une adresse IP v6.

Nous supposons que votre domaine principal est `mycompany.com` et que vous voulez nommer votre instance
Towerify `towerify`.

Si votre serveur a une adresse IP v4, ajouter 2 entrées A pour votre domaine :

```
towerify 3600 IN A <ip_v4>
*.towerify 3600 IN A <ip_v4>
```

Si votre serveur a une adresse IP v6, ajouter 2 entrées AAAA pour votre domaine :

```
towerify 3600 IN AAAA <ip_v6>
*.towerify 3600 IN AAAA <ip_v6>
```

## Installation de Towerify

### Installation des outils

```bash
apt-get install ca-certificates curl -y
```

### Installation de YunoHost

```bash
curl https://install.yunohost.org | bash -s -- -a
```

```bash
yunohost tools postinstall --domain towerify.mycompany.com --username twr_admin --fullname "Towerify Admin" --password "<strongpassword>"
```

!!! warning

    A partir de ce moment, l'utilisateur `root` ne peut plus se connecter en SSH au serveur. Si vous
    utilisiez cet utilisateur, vous devrez utiliser `twr_admin` à la place.
    Si vous aviez autorisé des clés publiques pour `root` vous pouvez les copier pour `twr_admin` avec
    les commandes `mkdir -p /home/twr_admin/.ssh ; cp /root/.ssh/authorized_keys /home/twr_admin/.ssh/authorized_keys ; 
    chown twr_admin:twr_admin /home/twr_admin/.ssh/authorized_keys`


### L'administrateur peut utiliser `sudo` sans retaper son mot de passe (optionel)

```bash
sudo yunohost settings set security.password.passwordless_sudo -v yes
```

### Générer le certificat SSL

```bash
sudo yunohost diagnosis run --force
sudo yunohost domain cert install towerify.mycompany.com
```

## Installer le complément pour utiliser *Towerify CLI*

### Installer `pass`

```bash
sudo apt-get install pass -y
```

### Installer Jenkins

Commencez par créer un domaine dédié à Jenkins :

```bash
sudo yunohost domain add jenkins.towerify.mycompany.com
sudo yunohost diagnosis run web dnsrecords --force
sudo yunohost domain cert install jenkins.towerify.mycompany.com
```

Puis installez Jenkins :

```bash
sudo yunohost app install https://github.com/computablefacts/jenkins_ynh/tree/cf-prod --force --args "domain=jenkins.towerify.mycompany.com&path=/&is_public=yes"
```

!!! todo

    Expliquer pourquoi `is_public=yes` n'est pas un problème


!!! todo

    Documenter comment terminer l'installation de Jenkins grâce à son interface Admin (URL a saisir dans la conf notamment)

!!! warning

    Pour que *Towerify CLI* puisse faire des requêtes POST au Jenkins, il faut ajouter le plugin Strict Crumb Issuer.
    Puis aller dans "Administrer Jenkins" > "Security" > CSRF Protection, choisir "Strict Crumb Issuer", cliquer
    sur "Avancé" et décocher "Check the session ID"

!!! warning

    Pour que le pipeline Jenkins de Towerify puisse accepter un paramètre de type stashedFile, il faut installer
    le plugin File Parameter.

!!! warning

    Pour que le pipeline Jenkins de Towerify puisse lire le fichier de configuration en YAML, il faut installer
    le plugin Pipeline Utility Steps.

!!! warning

    Pour que le pipeline Jenkins de Towerify puisse utiliser Docker, il faut installer le plugin Docker Pipeline.
    Et donner à Jenkins le droit d'utiliser Docker avec la commande `sudo usermod -a -G docker jenkins`. Puis
    redémarrer Jenkins avec la commande `sudo systemctl restart jenkins.service`.

!!! danger

    Pour que le pipeline Jenkins de Towerify puisse pousser les images Docker vers le Docker Hub de ComputableFacts,
    il faut définir les crendentials `docker-hub-cf-cred` dans Jenkins.

    !!! question 
        
        Faut-il, à terme, avoir une repo locale au Towerify ???

    Une solution court terme serait de ne pas pousser les images Docker générées vers une repo. Elles ne seraient
    présentes que sur la machine Towerify. Comme le Docker Compose qui démarre l'application est sur la même
    machine, il devrait trouver l'image locale (A tester).

    L'inconvénient de cette solution de stockage en local, c'est qu'on perd la possibilité d'utiliser l'image 
    ailleurs et un "backup" gratuit des images en dehors du serveur Towerify. Peut-être pas très grave pour commencer.

!!! warning

    Pour que le pipeline Jenkins de Towerify puisse executer les commandes `sudo yunohost ...`, il faut
    ajouter l'utilisateur `jenkins` aux sudoers avec la commande : 
    `sudo sh -c 'echo "jenkins ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/50-jenkins'`


### Installer Portainer

Commencez par créer un domaine dédié à Portainer :

```bash
sudo yunohost domain add portainer.towerify.mycompany.com
sudo yunohost diagnosis run web dnsrecords --force
sudo yunohost domain cert install portainer.towerify.mycompany.com
```

Puis installez Portainer :

```bash
sudo yunohost app install https://github.com/computablefacts/portainer_ynh --force --args "domain=portainer.towerify.mycompany.com&path=/&init_main_permission=admins&admin=twr_admin&password=<strongpassword>"
```

### Utiliser *Towerify CLI*

Vous pouvez maintenant utiliser Towerify CLI avec votre instance Towerify auto-hébergée 
en suivant [notre Tutoriel](tutorial.md).
