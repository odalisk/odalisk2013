Installation
============

Prerequirement
-----------------
If you want to experiment the Odalisk functionalities on your own server for instance, you will may be need to install some software, tools. Here are some points you should think about : 

The Installation of a server, a Lamp server should be a good point to start

Then do not forget to have nodejs installed on your server/computer.- [NodeJs](http://nodejs.org/).

With npm, install also less : 
```bash
npm install -g less
```

If you do not have already a Git Account, follow the ultimate guide : [SetUp Git](https://help.github.com/articles/set-up-git).





Initialize the project
-----------------

```bash
# Clone the project
git clone git@github.com:odalisk/odalisk2013.git path/to/your/project

# Move into the project directory
cd path/to/your/project


# First install curl
sudo apt-get install curl
```


Prepare the config file
----------------------
```bash
cd app/config
cp config.yml.dist config.yml
# Edit config.yml and add your database prefs.
vi config.yml
cd ../..
```

The Vendors
----------------------
```bash

# Install the update of composer 
sudo curl -s https://getcomposer.org/installer | php
sudo php composer.phar self-update

# Install the vendors
sudo php composer.phar install
```


Create the database
----------------------
```bash

# You are now ready to create the database
app/console doctrine:database:create
app/console doctrine:schema:create
```

You should then use chmod on your app/cache and app/logs folder.

To initialize the dev environment you just have to dump the assets :

```bash
app/console assets:install
app/console assetic:dump
```

Now just point your virtualhost to ``` path/to/your/project/web ``` and you're good to go.
```bash
#For instance
ln -s /path/to/odalisk/web/ /var/www/nameOfTheLink
```

If you use Apache on Linux, do not forget to add the site in the site-available folder and then enable it. Then reload/restart the server.

Some useful pointers to get started
-----------------------------------

- [Symfony2](http://symfony.com)
- [Doctrine2](http://www.doctrine-project.org/)


To learn some information about the commands of the console
-----------------------------------
- [HowToUseOdaliskConsole](https://github.com/odalisk/odalisk2013/blob/master/doc/howto.md)
