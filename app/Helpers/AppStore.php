<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class AppStore
{
    public static function categories(): array
    {
        return collect(self::catalog())
            ->map(fn($app) => $app['category'])
            ->unique()
            ->sort()
            ->all();
    }

    public static function findWebsiteFromSku(string $sku): ?string
    {
        $app = self::findAppFromSku($sku)->first();
        return $app['website'] ?? null;
    }

    public static function findPackageFromSku(string $sku): ?string
    {
        $app = self::findAppFromSku($sku)->first();
        return $app['package'] ?? null;
    }

    public static function findAdminDocFromSku(string $sku): ?string
    {
        $app = self::findAppFromSku($sku)->first();
        return $app['documentation_admin'] ?? null;
    }

    public static function findUserDocFromSku(string $sku): ?string
    {
        $app = self::findAppFromSku($sku)->first();
        return $app['documentation_user'] ?? null;
    }

    public static function findInstallScriptFromSku(string $sku): ?string
    {
        $app = self::findAppFromSku($sku)->first();
        return $app['install_script'] ?? null;
    }

    public static function findUninstallScriptFromSku(string $sku): ?string
    {
        $app = self::findAppFromSku($sku)->first();
        return $app['uninstall_script'] ?? null;
    }

    public static function findPermissionsFromSku(string $sku): Collection
    {
        $app = self::findAppFromSku($sku)->first();
        return isset($app['permissions']) ? collect($app['permissions']) : collect();
    }

    public static function findAppFromSku(string $sku): Collection
    {
        return collect(self::catalog())
            ->filter(fn($app) => $app['sku'] === $sku)
            ->values();
    }

    public static function catalog(): array
    {
        return [
            [
                'sku' => 'adguardhome',
                'name' => 'AdGuard',
                'logo' => 'adguardhome.png',
                'category' => 'SysAdmin',
                'website' => 'https://adguard.com/',
                'package' => 'https://github.com/YunoHost-Apps/adguardhome_ynh',
                'documentation_admin' => 'https://github.com/AdguardTeam/AdGuardHome/wiki',
                'documentation_user' => 'https://adguard.com/kb/fr/',
                'description_fr' => "AdGuard est l'un des leaders sur le marché des logiciels de blocage de publicités avec plus de 10 ans d'expérience, près d'une douzaine de produits pour différentes plateformes et plus de 30 millions d'installations par les utilisateurs.",
                'description_en' => 'AdGuard is one of the leaders on the market of ad-blocking software with 10+ years of experience, almost a dozen products for various platforms, and over 30 million user installs.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['adguardhome.main', 'adguardhome.api'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'adversarymeter',
                'name' => 'AdversaryMeter',
                'logo' => 'adversarymeter.png',
                'category' => 'SysAdmin',
                'website' => 'https://adversarymeter.io/',
                'package' => null,
                'documentation_admin' => null,
                'documentation_user' => null,
                'description_fr' => "Protégez votre infrastructure et vos données des attaquants!",
                'description_en' => 'Protect your infrastructure and data from attackers!',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['adversarymeter.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'bookstack',
                'name' => 'Bookstack',
                'logo' => 'bookstack.png',
                'category' => 'CMS',
                'website' => 'https://www.bookstackapp.com/',
                'package' => 'https://github.com/YunoHost-Apps/bookstack_ynh',
                'documentation_admin' => 'https://www.bookstackapp.com/docs/admin/',
                'documentation_user' => 'https://www.bookstackapp.com/docs/user/',
                'description_fr' => 'BookStack est une plateforme simple, auto-hébergée et facile à utiliser pour organiser et stocker des informations.',
                'description_en' => 'BookStack is a simple, self-hosted, easy-to-use platform for organising and storing information.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['bookstack.main', 'bookstack.api'],
                'install_script' => "#!/bin/bash\nsudo yunohost app install {APP_ID} -a \"domain={APPS_DOMAIN}&path=/&init_main_permission=visitors&admin={ADMIN_USERNAME}&password={ADMIN_PASSWORD}&language=fr\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'castopod',
                'name' => 'Castopod',
                'logo' => 'castopod.png',
                'category' => 'CMS',
                'website' => 'https://castopod.org/',
                'package' => 'https://github.com/YunoHost-Apps/castopod_ynh',
                'documentation_admin' => 'https://docs.castopod.org/',
                'documentation_user' => null,
                'description_fr' => 'Hébergez vous-même vos podcasts en toute simplicité, gardez le contrôle sur ce que vous créez et parlez à votre public sans intermédiaire. Votre podcast et votre public vous appartiennent, à vous et à vous seul.',
                'description_en' => 'Self-host your podcasts with ease, keep control over what you create and talk to your audience without any middleman. Your podcast and your audience belong to you and you only.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['castopod.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'clickhouse',
                'name' => 'ClickHouse',
                'logo' => 'clickhouse.png',
                'category' => 'DataEngineering',
                'website' => 'https://clickhouse.com/',
                'package' => 'https://github.com/computablefacts/clickhouse_ynh',
                'documentation_admin' => null,
                'documentation_user' => 'https://clickhouse.com/docs',
                'description_fr' => "ClickHouse est un système de gestion de base de données orienté colonnes, rapide et open-source, qui permet de générer des rapports de données analytiques en temps réel à l'aide de requêtes SQL.",
                'description_en' => 'ClickHouse is a fast open-source column-oriented database management system that allows generating analytical data reports in real-time using SQL queries.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['clickhouse.main'],
                'install_script' => "#!/bin/bash\nsudo yunohost app install https://github.com/computablefacts/clickhouse_ynh -a \"domain={APPS_DOMAIN}&path=/&init_main_permission=visitors&admin={ADMIN_USERNAME}&password={ADMIN_PASSWORD}\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'cyberchef',
                'name' => 'CyberChef',
                'logo' => 'cyberchef.png',
                'category' => 'Tools',
                'website' => 'https://github.com/gchq/CyberChef',
                'package' => 'https://github.com/YunoHost-Apps/cyberchef_ynh',
                'documentation_admin' => null,
                'documentation_user' => 'https://github.com/gchq/CyberChef/wiki',
                'description_fr' => "Le couteau suisse du cyberespace - une application web pour le cryptage, l'encodage, la compression et l'analyse des données.",
                'description_en' => 'The Cyber Swiss Army Knife - a web app for encryption, encoding, compression and data analysis.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['cyberchef.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'docker',
                'name' => 'Docker',
                'logo' => 'docker.png',
                'category' => 'DevOps',
                'website' => 'https://www.docker.com/',
                'package' => null,
                'documentation_admin' => 'https://docs.docker.com/',
                'documentation_user' => null,
                'description_fr' => "Docker propose une suite d'outils de développement, de services, de contenus fiables et d'automatismes, utilisés individuellement ou ensemble, pour accélérer la fourniture d'applications sécurisées.",
                'description_en' => 'Docker provides a suite of development tools, services, trusted content, and automations, used individually or together, to accelerate the delivery of secure applications.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['docker.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'federa',
                'name' => 'Federa',
                'logo' => 'federa.png',
                'category' => 'DataEngineering',
                'website' => 'https://federa.io/',
                'package' => null,
                'documentation_admin' => null,
                'documentation_user' => null,
                'description_fr' => "Federa est une plateforme de données zéro-trust qui vous fournit tous les outils dont vous avez besoin pour fédérer des ensembles de données hétérogènes et construire des applications Web sécurisées.",
                'description_en' => 'Federa is a zero-trust data platform that provides you with all the tools you need to federate heterogeneous datasets and build secure-by-design enterprise-grade Web Apps.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['federa.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'flarum',
                'name' => 'Flarum',
                'logo' => 'flarum.png',
                'category' => 'CMS',
                'website' => 'https://flarum.org/',
                'package' => 'https://github.com/computablefacts/flarum_ynh',
                'documentation_admin' => 'https://docs.flarum.org/',
                'documentation_user' => null,
                'description_fr' => "Un forum de nouvelle génération facilement extensible.",
                'description_en' => 'Next-generation forum made simple.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['flarum.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'grav',
                'name' => 'Grav',
                'logo' => 'grav.png',
                'category' => 'CMS',
                'website' => 'https://getgrav.org/',
                'package' => 'https://github.com/YunoHost-Apps/grav_ynh',
                'documentation_admin' => 'https://learn.getgrav.org/',
                'documentation_user' => null,
                'description_fr' => 'Grav est un CMS open source moderne à base de fichiers plats.',
                'description_en' => 'Grav is a modern open source flat-file CMS.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['grav.main', 'grav.admin', 'grav.user'],
                'install_script' => "#!/bin/bash\nsudo yunohost app install {APP_ID} -a \"domain={APPS_DOMAIN}&path=/&init_main_permission=visitors&language=en_EN&init_admin_permission={ADMIN_USERNAME}\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'jenkins',
                'name' => 'Jenkins',
                'logo' => 'jenkins.png',
                'category' => 'DevOps',
                'website' => 'https://jenkins.io/',
                'package' => 'https://github.com/computablefacts/jenkins_ynh',
                'documentation_admin' => null,
                'documentation_user' => 'https://www.jenkins.io/doc/book/',
                'description_fr' => "Principal serveur d'automatisation open source, Jenkins propose des centaines de plugins pour faciliter la construction, le déploiement et l'automatisation de n'importe quel projet.",
                'description_en' => 'The leading open source automation server, Jenkins provides hundreds of plugins to support building, deploying and automating any project.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['jenkins.main', 'jenkins.github-webhook'],
                'install_script' => "#!/bin/bash\nsudo yunohost app install https://github.com/computablefacts/jenkins_ynh/tree/cf-prod -a \"domain={APPS_DOMAIN}&path=/\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'jupyterlab',
                'name' => 'JupyterLab',
                'logo' => 'jupyterhub.png',
                'category' => 'Analytics',
                'website' => 'https://jupyter.org/',
                'package' => 'https://github.com/YunoHost-Apps/jupyterlab_ynh',
                'documentation_admin' => 'https://jupyterlab.readthedocs.io/en/stable/',
                'documentation_user' => null,
                'description_fr' => "JupyterLab est l'interface utilisateur de nouvelle génération pour le projet Jupyter, offrant tous les éléments familiers du bloc-notes Jupyter classique (bloc-notes, terminal, éditeur de texte, navigateur de fichiers, sorties riches, etc.) dans une interface utilisateur flexible et puissante.",
                'description_en' => 'JupyterLab is the next-generation user interface for Project Jupyter offering all the familiar building blocks of the classic Jupyter Notebook (notebook, terminal, text editor, file browser, rich outputs, etc.) in a flexible and powerful user interface.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['jupyterlab.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'keycloak',
                'name' => 'Keycloak',
                'logo' => 'keycloak.png',
                'category' => 'IdentityManagement',
                'website' => 'https://www.keycloak.org/',
                'package' => 'https://github.com/computablefacts/keycloak_ynh',
                'documentation_admin' => 'https://www.keycloak.org/documentation',
                'documentation_user' => null,
                'description_fr' => "Ajoutez de l'authentification à vos applications et sécurisés vos services avec un minimum d'efforts. Keycloak assure la fédération des utilisateurs, l'authentification forte, la gestion des utilisateurs, l'autorisation fine, etc.",
                'description_en' => 'Add authentication to applications and secure services with minimum effort. Keycloak provides user federation, strong authentication, user management, fine-grained authorization, and more.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['keycloak.main'],
                'install_script' => "#!/bin/bash\nsudo yunohost app install https://github.com/computablefacts/keycloak_ynh -a \"domain={APPS_DOMAIN}&path=/&init_main_permission=visitors&admin={ADMIN_USERNAME}&password={ADMIN_PASSWORD}\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'listmonk',
                'name' => 'Listmonk',
                'logo' => 'listmonk.png',
                'category' => 'CMS',
                'website' => 'https://listmonk.app/',
                'package' => 'https://github.com/yunohost-apps/listmonk_ynh',
                'documentation_admin' => 'https://listmonk.app/docs/',
                'documentation_user' => null,
                'description_fr' => "Gestionnaire de lettres d'information et de listes de diffusion auto-hébergé.",
                'description_en' => 'Self-hosted newsletter and mailing list manager.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['listmonk.admin', 'listmonk.api', 'listmonk.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'mattermost',
                'name' => 'Mattermost',
                'logo' => 'mattermost.png',
                'category' => 'CMS',
                'website' => 'https://mattermost.com/',
                'package' => 'https://github.com/YunoHost-Apps/mattermost_ynh',
                'documentation_admin' => 'https://docs.mattermost.com/guides/deployment.html',
                'documentation_user' => 'https://docs.mattermost.com/guides/use-mattermost.html',
                'description_fr' => 'Collaboration pour le travail critique. Accélérer le travail critique dans des environnements opérationnels complexes.',
                'description_en' => 'Collaboration for Mission-Critical Work. Accelerating mission critical work in complex operational environments.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['mattermost.main'],
                'install_script' => "#!/bin/bash\nsudo yunohost app install {APP_ID} -a \"domain={APPS_DOMAIN}&path=/&init_main_permission=visitors&admin={ADMIN_USERNAME}&password={ADMIN_PASSWORD}&version=Team&language=fr&team_display_name=Bumblebee\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'monitorix',
                'name' => 'Monitorix',
                'logo' => 'monitorix.png',
                'category' => 'SysAdmin',
                'website' => 'https://monitorix.org/',
                'package' => 'https://github.com/YunoHost-Apps/monitorix_ynh',
                'documentation_admin' => 'https://www.monitorix.org/documentation.html',
                'documentation_user' => null,
                'description_fr' => 'Monitorix est un outil de surveillance système gratuit, open source et léger, conçu pour surveiller autant de services et de ressources système que possible.',
                'description_en' => 'Monitorix is a free, open source, lightweight system monitoring tool designed to monitor as many services and system resources as possible.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['monitorix.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'nextcloud',
                'name' => 'Nextcloud',
                'logo' => 'nextcloud.png',
                'category' => 'CMS',
                'website' => 'https://nextcloud.com/',
                'package' => 'https://github.com/YunoHost-Apps/nextcloud_ynh',
                'documentation_admin' => 'https://docs.nextcloud.com/server/stable/admin_manual/',
                'documentation_user' => 'https://docs.nextcloud.com/server/latest/user_manual/en/',
                'description_fr' => 'La solution de travail collaboratif la plus populaire auprès de millions d’utilisateurs dans des milliers d’organisations à travers le monde.',
                'description_en' => 'The most popular collaborative working solution with millions of users in thousands of organizations worldwide.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['nextcloud.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'nifi',
                'name' => 'NiFi',
                'logo' => 'nifi.png',
                'category' => 'DataEngineering',
                'website' => 'https://nifi.apache.org/',
                'package' => 'https://github.com/computablefacts/nifi_ynh',
                'documentation_admin' => null,
                'documentation_user' => 'https://nifi.apache.org/documentation/',
                'description_fr' => 'Apache NiFi est un système facile à utiliser, puissant et fiable pour traiter et distribuer des données.',
                'description_en' => 'Apache NiFi is an easy to use, powerful, and reliable system to process and distribute data.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['nifi.main'],
                'install_script' => "#!/bin/bash\nsudo yunohost app install https://github.com/computablefacts/nifi_ynh -a \"domain={APPS_DOMAIN}&path=/&init_main_permission=visitors&admin={ADMIN_USERNAME}\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'nlmingestor',
                'name' => 'NLM Ingestor',
                'logo' => 'nlmatics.png',
                'category' => 'DataEngineering',
                'website' => 'https://github.com/nlmatics/nlm-ingestor/',
                'package' => 'https://github.com/computablefacts/nlm_ingestor_ynh',
                'documentation_admin' => null,
                'documentation_user' => null,
                'description_fr' => 'Parsers pour différents formats de fichiers : PDF, HTML, TEXT, DOCX & PPTX',
                'description_en' => 'Parsers for various file formats : PDF, HTML, TEXT, DOCX & PPTX',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => [],
                'install_script' => "#!/bin/bash\nsudo yunohost app install https://github.com/computablefacts/nlm_ingestor_ynh -a \"domain={APPS_DOMAIN}&path=/&init_main_permission=visitors\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'paheko',
                'name' => 'Paheko',
                'logo' => 'paheko.png',
                'category' => 'CMS',
                'website' => 'https://paheko.cloud/',
                'package' => 'https://github.com/YunoHost-Apps/paheko_ynh',
                'documentation_admin' => 'https://fossil.kd2.org/paheko/wiki?name=Documentation',
                'documentation_user' => null,
                'description_fr' => "Gestion d'association simple, complète et efficace.",
                'description_en' => 'Simple, comprehensive and efficient association management.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['paheko.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'paperless-ngx',
                'name' => 'Paperless',
                'logo' => 'paperless-ngx.png',
                'category' => 'CMS',
                'website' => 'https://docs.paperless-ngx.com/',
                'package' => 'https://github.com/YunoHost-Apps/paperless-ngx_ynh',
                'documentation_admin' => 'https://paperless-ngx.readthedocs.io/en/latest/index.html',
                'documentation_user' => 'https://paperless-ngx.readthedocs.io/en/latest/usage_overview.html',
                'description_fr' => "Paperless-ngx est un système de gestion documentaire qui transforme vos documents physiques en archives consultables en ligne afin que vous puissiez conserver moins de papier.",
                'description_en' => 'Paperless-ngx is a document management system that transforms your physical documents into a searchable online archive so you can keep, well, less paper.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['paperless-ngx.main', 'paperless-ngx.api'],
                'install_script' => "#!/bin/bash\nsudo yunohost app install {APP_ID} -a \"domain={APPS_DOMAIN}&path=/&init_main_permission=visitors&admin={ADMIN_USERNAME}&admin_pw={ADMIN_PASSWORD}\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'phpmyadmin',
                'name' => 'phpMyAdmin',
                'logo' => 'phpmyadmin.png',
                'category' => 'DataEngineering',
                'website' => 'https://www.phpmyadmin.net/',
                'package' => 'https://github.com/YunoHost-Apps/phpmyadmin_ynh',
                'documentation_admin' => 'https://www.phpmyadmin.net/docs/',
                'documentation_user' => null,
                'description_fr' => "phpMyAdmin est un logiciel libre écrit en PHP, destiné à gérer l'administration de MySQL sur le Web.",
                'description_en' => 'phpMyAdmin is a free software tool written in PHP, intended to handle the administration of MySQL over the Web.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['phpmyadmin.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'portainer',
                'name' => 'Portainer',
                'logo' => 'portainer.png',
                'category' => 'DevOps',
                'website' => 'https://portainer.io/',
                'package' => 'https://github.com/computablefacts/portainer_ynh',
                'documentation_admin' => 'https://docs.portainer.io/',
                'documentation_user' => null,
                'description_fr' => "Portainer est le logiciel de gestion de conteneurs le plus polyvalent qui simplifie l'adoption sécurisée de conteneurs avec une rapidité remarquable.",
                'description_en' => 'Portainer is the most versatile container management software that simplifies your secure adoption of containers with remarkable speed.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['portainer.main'],
                'install_script' => "#!/bin/bash\nsudo yunohost app install https://github.com/computablefacts/portainer_ynh -a \"domain={APPS_DOMAIN}&path=/&admin={ADMIN_USERNAME}&password={ADMIN_PASSWORD}\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'pydio',
                'name' => 'Pydio',
                'logo' => 'pydio.png',
                'category' => 'CMS',
                'website' => 'https://pydio.com/',
                'package' => 'https://github.com/YunoHost-Apps/pydio_ynh',
                'documentation_admin' => 'https://pydio.com/en/docs',
                'documentation_user' => null,
                'description_fr' => 'Pydio est une solution open source de partage de fichiers déployé dans votre infrastructure, selon vos propres règles de sécurité.',
                'description_en' => 'Pydio is an open source file-sharing solution deployed within your own infrastructure, according to your own security rules.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['pydio.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'superset',
                'name' => 'Superset',
                'logo' => 'superset.png',
                'category' => 'Visualization',
                'website' => 'https://superset.apache.org/',
                'package' => 'https://github.com/computablefacts/superset_ynh',
                'documentation_admin' => 'https://superset.apache.org/docs/intro',
                'documentation_user' => 'https://superset.apache.org/docs/using-superset/creating-your-first-dashboard',
                'description_fr' => "Apache Superset™ est une plateforme open-source d'exploration et de visualisation de données.",
                'description_en' => 'Apache Superset™ is an open-source modern data exploration and visualization platform.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['superset.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ], [
                'sku' => 'tika',
                'name' => 'Tika',
                'logo' => 'tika.png',
                'category' => 'DataEngineering',
                'website' => 'https://tika.apache.org/',
                'package' => 'https://github.com/computablefacts/tika_ds_ynh',
                'documentation_admin' => 'https://cwiki.apache.org/confluence/display/TIKA/TikaServer#TikaServer-IntroductiontoTikaserver',
                'documentation_user' => 'https://cwiki.apache.org/confluence/display/TIKA/TikaServer#TikaServer-TikaServerServices',
                'description_fr' => "La boîte à outils Apache Tika™ détecte et extrait les métadonnées et le texte de plus d'un millier de types de fichiers différents (tels que PPT, XLS et PDF). Tous ces types de fichiers peuvent être analysés via une interface unique, ce qui rend Tika utile pour l'indexation des moteurs de recherche, l'analyse de contenu, la traduction et bien plus encore.",
                'description_en' => 'The Apache Tika™ toolkit detects and extracts metadata and text from over a thousand different file types (such as PPT, XLS, and PDF). All of these file types can be parsed through a single interface, making Tika useful for search engine indexing, content analysis, translation, and much more.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => [],
                'install_script' => "#!/bin/bash\nsudo yunohost app install https://github.com/computablefacts/tika_ds_ynh -a \"domain={APPS_DOMAIN}&path=/&init_main_permission=visitors\" --force",
                'uninstall_script' => null,
            ], [
                'sku' => 'transfersh',
                'name' => 'Transfer.sh',
                'logo' => 'transfersh.png',
                'category' => 'Tools',
                'website' => 'https://github.com/dutchcoders/transfer.sh',
                'package' => 'https://github.com/YunoHost-Apps/transfersh_ynh',
                'documentation_admin' => 'https://github.com/dutchcoders/transfer.sh',
                'documentation_user' => null,
                'description_fr' => 'Partage de fichiers facile et rapide à partir de la ligne de commande.',
                'description_en' => 'Easy and fast file sharing from the command-line.',
                'state' => 'draft',
                'price' => null,
                'original_price' => null,
                'permissions' => ['transfersh.main'],
                'install_script' => null,
                'uninstall_script' => null,
            ]
        ];
    }
}