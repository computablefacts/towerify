# Contributing to Towerify

First off, thanks for expressing an interest in contributing to Towerify!

The document is a set of guidelines for contributing to Towerify code and documentation, which are hosted in the
ComputableFacts Organization on GitHub. These are strong guidelines but not set in stone rules. Please use your best
judgement, feel free to propose changes to this document in a pull request, and don't be afraid to ask questions.

## Table of content

* [Why should I contribute?](#why-should-i-contribute)
* [Before you get started](#before-you-get-started)
    * [Code of Conduct](#code-of-conduct)
    * [Which 'branch' should I contribute to?](#which-branch-should-i-contribute-to)
        * [Development branch: main](#development-branch-main)
        * [Release branches](#release-branches)
        * [Bug fixes and backporting](#bug-fixes-and-backporting)
    * [Understanding the structure of Towerify](#understanding-the-structure-of-towerify)
* [How can I contribute?](#how-can-i-contribute)
    * [Reporting bugs](#reporting-bugs)
        * [Before submitting a bug report](#before-submitting-a-bug-report)
        * [How do I submit a (good) bug report?](#how-do-i-submit-a-good-bug-report)
    * [Suggesting improvements or new features](#suggesting-improvements-or-new-features)
    * [Contributing code](#contributing-code)
        * [What to work on](#what-to-work-on)
        * [Local development](#local-development)
        * [Making a pull request](#making-a-pull-request)
    * [Writing documentation](#writing-documentation)
    * [Translating Towerify](#translating-towerify)
    * [Sponsor the project](#sponsoring-the-project)

## Why should I contribute?

In one simple sentence, every contribution means not just that you give something back to the community but also that
you get to use and enjoy better software.

By taking part in the discussion and submitting code contributions to work on the ones that are most important to you,
you get to shape the future of the project.

## Before you get started

### Code of Conduct

This project and everyone participating in it are governed by the [Code of Conduct](CODE_OF_CONDUCT.md). By
participating, you are expected to uphold this code.

Please report unacceptable behavior to <a href="mailto:engineering@computablefacts.com">
engineering@computablefacts.com</a>.

### Which 'branch' should I contribute to?

#### Development branch: `main`

The `main` branch will serve as our main development branch. This is where all the new features, enhancements, and
non-critical bug fixes will be merged. All contributors should base their work on the latest version of the `main`
branch and regularly pull changes to keep up with the development.

__Key Points:__

- Base all new developments on the `main` branch.
- Ensure that your local `main` is up-to-date before starting new work.

#### Release branches

For each new release of our software, a new release branch will be created from the `main` branch. These branches are
named according to the version of the release, for example, `0.x`, `1.x`, etc. These branches are intended for:

- Finalizing a release (last-minute fixes, documentation, etc.)
- Maintaining released versions.

__Key Points:__

- Only bug fixes and critical updates should be committed to these branches.
- No new features should be added to release branches to ensure stability.

#### Bug fixes and backporting

If a bug is discovered in a released version, it should first be fixed in the relevant release branch. Once the fix is
tested and confirmed, the same fix needs to be backported to the `main` branch. This ensures that future releases also
contain these fixes.

__Procedure:__

- Fix the bug in the appropriate release branch.
- Test the fix thoroughly in that branch.
- Backport the fix to the `main` branch.
- Optionally, backport to other affected release branches, depending on the bug's impact and the versions supported.

### Understanding the structure of Towerify

TODO

## How can I contribute?

There are a lot of different ways that you can get involved in the Towerify project. Let's take a look at some of the
main ones!

### Reporting bugs

If you find a bug in Towerify, please report it. Following these guidelines helps maintainers and the community
understand your report, reproduce the behavior, and find related reports.

#### Before submitting a bug report

> [!IMPORTANT]  
> If your report is for a potential security exploit, please do not make it public by creating an Issue, but instead,
> follow the instructions in our [Security Policy](SECURITY.md).

Firstly, **Do a [search](https://github.com/search?q=+is%3Aissue+user%3Acomputablefacts)** of the existing issues to see
if the problem has already been reported. If it has **and the issue is still open**, add a comment to the existing issue
instead of opening a new one.

> [!NOTE]
> If you find a **Closed** issue that seems like it is the same thing that you're experiencing, open a new issue and
> include a link to the original issue in the body of your new one.

#### How do I submit a (good) bug report?

Bugs are tracked as [GitHub issues](https://guides.github.com/features/issues/).

Explain the problem and include additional details to help us reproduce the problem:

* __Use a clear and descriptive title__ for the issue to identify the problem.
* __Describe the exact steps which reproduce the problem__ in as much detail as possible. For example, start by
  explaining what section exactly you used in the browser, or which API call you were using. When listing steps, **don't
  just say what you did but explain how you did it**.
* __Provide specific examples to demonstrate the steps__. Include links to files or GitHub projects, or copy/pastable
  snippets, which you use in those examples. If you're providing snippets in the issue,
  use [Markdown code blocks](https://help.github.com/articles/markdown-basics/#multiple-lines).
* __Describe the behavior you observed after following the steps__ and point out what exactly is the problem with that
  behavior.
* __Explain which behavior you expected to see instead and why.__

If possible:

* Include screenshots and animated GIFs which show you following the described steps and demonstrate the problem.
* Include details about your configuration and environment.

### Suggesting improvements or new features

> [!IMPORTANT]
> Please note the title is __*Suggesting*__, not __*Demanding*__. Be polite, appreciate other people's time, and explain
> in detail, and you are far more likely to get what you want!

Towerify is not designed to be all things to all people, but we do want it to be as useful and usable as possible. If
you have a suggestion for a new feature or an improvement to an existing one then please do submit it. Please be as
clear and explicit as you can and provide as much detail as you can, this will make it much easier for the community and
maintainers to understand your suggestion and take action.

Before creating enhancement suggestions, please check through
the [existing Issues](https://github.com/computablefacts/towerify/issues) and see if somebody has already made the same
suggestion. If they have then please don't create a new issue, but instead, add your thoughts and comments to the
existing one.

### Contributing code

The source code is the heart of Towerify, and we are always interested in quality contributions to improve it, squash
bugs, and close open issues. Please follow these guidelines to make things easier for yourself and other contributors.

#### What to work on

Check out the [existing Issues](https://github.com/computablefacts/towerify/issues) for an overview of what needs to be
done. See the `good-first-issue` label for a list of issues that should be relatively easy to get started with. If
there's anything you're unsure of, don't hesitate to ask!

If you're planning to go ahead and work on something, please leave a comment on the relevant issue or create a new one
explaining what you are doing. This helps us divide our efforts more sensibly by ensuring that we are not all doing the
same thing at the same time.

#### Local development

- Install MariaDB:
  ```
  $ sudo apt install mariadb-server
  ```
- Launch MariaDB command line then create a new database and user:
  ```
  $ sudo mysql -u root -p
  MariaDB[(none)] CREATE DATABASE tw_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  MariaDB[(none)] CREATE USER 'tw_user'@'localhost' IDENTIFIED BY 'z0rglub';
  MariaDB[(none)] GRANT ALL ON tw_db.* TO 'tw_user'@'localhost';
  MariaDB[(none)] FLUSH PRIVILEGES;
  MariaDB[(none)] EXIT;
  ```
- Get the app:
  ```
  $ git clone https://github.com/computablefacts/towerify.git
  $ cd towerify/
  ```
- Configure the environment:
    - Create a `.env` file:
      ```
      $ cp .env.example .env && php artisan key:generate
      ```
    - Add the following entries to the `.env` file:
      ```
      # Super Admin (mandatory)
      ADMIN_EMAIL=<your email>
      ADMIN_USERNAME=<your username>
      ADMIN_PASSWORD=<your password>

      # Security (mandatory)
      HASHER_NONCE=<random string>
      
      # Database (mandatory)
      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=tw_db
      DB_USERNAME=tw_user
      DB_PASSWORD=z0rglub
      
      # Queues (mandatory)
      QUEUE_CONNECTION=database
      QUEUE_DRIVER=database

      # Images (mandatory due to issue https://github.com/spatie/laravel-medialibrary/issues/3502)
      IMAGE_DRIVER=imagick

      # Telescope (optional)
      TELESCOPE_ENABLED=false
      TELESCOPE_WHITELIST_USERNAMES=<username 1>,<usename 2>
      TELESCOPE_WHITELIST_DOMAINS=<domain 1>,<domain 2>,<domain 3>
      
      # Backups (optional)
      AWS_ACCESS_KEY_ID=<your access key>
      AWS_DEFAULT_REGION=<your region>
      AWS_SECRET_ACCESS_KEY=<your secret key>
      AWS_USE_PATH_STYLE_ENDPOINT=false
      AWS_BUCKET_PUBLIC=<your public bucket>
      AWS_BUCKET_PRIVATE=<your private bucket>
      
      # AdversaryMeter (optional)
      AM_URL=https://sentinel.computablefacts.com
      AM_IP_ADDRESSES=51.159.17.217,51.159.18.48,51.159.18.50,212.129.7.115
      AM_API_KEY=<your api key>
      ```
- Install dependencies:
  ```
  $ composer install
  ```
- Seed the database:
  ```
  $ php artisan migrate --seed
  ```
- Link storage:
  ```
  $ php artisan storage:link
  ```
- Start the application:
  ```
  $ composer dump-autoload && php artisan cache:clear && php artisan serve --port=8080
  ```
- Go to `http://127.0.0.1:8080` with your favorite Web Browser.

> [!NOTE]  
> If you need to run jobs locally, use `php artisan queue:work`.

#### Making a pull request

1. __Fork and Clone.__ Fork the repository and clone it locally to your machine.
2. __Create a New Branch.__ Always create a new branch for each set of changes. This keeps modifications organized and
   separate from the main branch. Use descriptive branch names that relate to the changes made: `fix-login-bug`,
   `add-sorting-feature`, etc.
3. __Commit Your Changes.__ Make small, frequent commits. Write clear, concise commit messages that explain why the
   changes were made.
4. __Test Your Changes.__ Add tests for new features and ensure all tests pass for bug fixes.
5. __Submit a Pull Request.__  In the pull request description, clearly describe the changes made, the reason behind
   them, and any other relevant information. If possible, include references to the related issue numbers using the
   appropriate tags: `Fixes #123`.

### Writing documentation

The documentation for Towerify is hosted [here](https://docs.towerify.io).

The documentation is built using [Material for MkDocs](https://squidfunk.github.io/mkdocs-material/). You can contribute
directly to the [repo on GitHub](https://github.com/computablefacts/towerify-docs). Please, try to be thorough and clear
when writing directions. Something might seem obvious to you, but do not assume that it is to everybody else.

### Translating Towerify

We would like Towerify to be available to as many people in as many languages as possible.

Towerify is primarily written in English and French. If you are a native or fluent speaker of another language then we
could use your help with the translations.

Towerify leverages Laravel's built-in internationalization features to manage its translations efficiently and
effectively.

* __Language Files.__ Laravel utilizes language files stored in the `resources/lang` directory. Each language supported
  by Towerify would have its own subdirectory within this folder, named with a language code (e.g., `en` for
  English, `es` for Spanish). These directories contain files that return arrays of keyed strings. For instance, you
  might have `messages.php` in the English directory with content like `['welcome' => 'Welcome to Towerify!']`.
* __Retrieving Translations.__ In Towerify's codebase, translations are retrieved using Laravel's `__()` helper
  function. This function takes a key and returns the corresponding translation for the current locale. For
  example, `__('messages.welcome')` would fetch the 'welcome' string from the appropriate language file based on the
  user's selected language.
* __Locale Configuration.__ The current locale (i.e., the language preference) can be set programmatically in Towerify
  based on user preferences or application settings. Laravel maintains this locale setting and uses it to determine
  which language directory to access when translations are requested.
* __Fallback Language.__ Laravel allows specifying a fallback language, which is used when a translation string is
  missing in the current language. This ensures that Towerify can still display all its messages even if translations
  are incomplete.

### Sponsoring the project

If you find that you lack the time or specific skills required to actively participate in the development or
documentation of the project, you can still contribute significantly by providing financial support. The most effective
way to support our efforts is to purchase a license for our commercial
product, [AdversaryMeter](https://adversarymeter.io). This helps us continue our work and improve the project further.

To fully benefit from Towerify's integration with AdversaryMeter, you need to generate a new API key from AdversaryMeter
and then update the `AM_API_KEY` property in the `.env` file of your Towerify project. Simply
replace `<your api key (optional)>` with your new API key in the `.env` file to ensure seamless communication between
the two services.

## Style Guides

We haven't established formal style guides yet. Please review the existing materials and adhere to their style to ensure
coherence.