How to use the console of the project
============

You will be able to launch useful commands for the project with the console the app directory.
The detail of each command is located in src/OdaliskProject/Bundle/Command.

Commands for the Ad Hoc Crawled platforms
-----------------

When you launch the console without arguments you can have access to all the commands for the Ad Hoc crawled platforms : 
```bash
app/console 
```

A certain order of execution has to be respected : 

```bash
#For each command you can precise which platform you want to crawl 
#(the platform has to be first enable in the portals.enable part of src/OdaliskProject/Bundle/Resources/config/portals.yml)

# Command to get the urls of the html pages before crawling them : 
app/console odalisk:geturls

# Command to crawl each url found previously. Each html page is downloaded : 
app/console odalisk:crawl

# Command to extract the information from the html pages stored. With this each dataset is stored in the sql database with the criteria associated : 
app/console odalisk:extract

# Command to generate the statistics for the platforms : 
app/console odalisk:statistics:generate

```

If you want to delete the files from the geturls of the crawl command you can delete the files in the data folder.
If you want to drop all the rows in the database you can do the following : 

```bash
#To drop everything
app/console doctrine:schema:drop --force
#To recreate the schema of the database
app/console doctrine:schema:create
```

Commands for the Crawled platforms in an rdf way
-----------------

These commands are related to the new approach of the project. This approach uses the rdf versions of the datasets metadata instead of the raw html pages. It is also bringing a MongoDb database in order to store theses rdf files.

As for the Ad Hoc way you can find all the commands by calling the console without arguments.

```bash
# Command to get the urls of the rdf files before downloading them : 
app/console odalisk:dcat:geturls

# Command to crawl each rdf files found previously. Each rdf page is downloaded and stored in the MongoDb database: 
app/console odalisk:dcat:crawl

# Command to extract the information from the rdf files stored in MongoDb. With this each dataset is stored in the sql database with the criteria associated : 
app/console odalisk:dcat:extract

# Command to generate the statistics for the platforms : 
app/console odalisk:statistics:generate

# Command to generate the DCAT Catalogs for the enabled platforms with the information we can extract from the SQL.
app/console odalisk:generate:dcat
```