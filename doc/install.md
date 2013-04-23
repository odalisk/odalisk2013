Installation
============

Initialize the project
-----------------

```bash
# Clone the project
git clone git@github.com:odalisk/odalisk2013.git path/to/your/project

# Move into the project directory
cd path/to/your/project


# First install curl
sudo apt-get install curl

#Then install 
curl -s https://getcomposer.org/installer | php
php composer.phar self-update



# Install the vendors
php composer.phar install
```

Configure the database
----------------------
```bash
cd app/config
cp config.yml.dist config.yml
# Edit config.yml and add your database prefs.
vi config.yml

# You are now ready to create the database
cd ..
./console doctrine:database:create
./console doctrine:schema:create
```

To initialize the dev environment you just have to dump the assets :

```bash
./console assets:install
./console assetic:dump
```

Now just point your virtualhost to ``` path/to/your/project/web ``` and you're good to go.

Some useful pointers to get started
-----------------------------------

- [Symfony2](http://symfony.com)
- [KnpRadBundle](http://rad.knplabs.com/)
- [Doctrine2](http://www.doctrine-project.org/)
