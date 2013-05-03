<?php

namespace OdaliskProject\Bundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use OdaliskProject\Bundle\Entity\Portal;

use OdaliskProject\Bundle\Entity\Dataset;

use OdaliskProject\Bundle\Scraper\Tools\Normalize\DateNormalizer;

use Symfony\Component\DomCrawler\Crawler;





/**
 * A command that will generate a DCAT file using data from Odalisk SQL database
 * it can generate the dcat files for all platforms
 */
class GenerateDCATCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('odalisk:generate:dcat')
            ->setDescription('Generate a DCAT file for a portal already crawled')
            ->addArgument('platform', InputArgument::OPTIONAL,
                'Which platform do you want to generate a DCAT file for ?'
            )
            ->addOption('list', null, InputOption::VALUE_NONE,
                'If set, the task will display available platforms names rather than generate a DCAT file for them'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = time();


        // Store the container so that we have an easy shortcut
        $container = $this->getContainer();
        // Get the configuration value from config/app.yml : which platforms are enabled?
        $platformServices = $container->getParameter('config.enabled_portals.adhoc');

        $dcatPlatforms = $container->getParameter('config.enabled_portals.dcat');

        // If we have additionnal platforms to parse (dcat ones)
        if( !empty($dcatPlatforms) ){
                foreach($dcatPlatforms as $it){
                // adding them to enabled platforms list
                array_push($platformServices, $it);
            }
        }
        
        // Get the data directory
        $dataPath = $container->getParameter('config.file_dumper.data_path');
        // Entity repository for datasets_crawls & entity manager
        $em = $this->getEntityManager();
        //$em->getConnection()->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
        
        $portalRepo = $this->getEntityRepository('OdaliskProject\Bundle\Entity\Portal');
        
        // Initialize some arrrays
        $platforms = array();

        // If the --list switch was used, just list the enabled platforms names
        if ($input->getOption('list')) {
            foreach ($platformServices as $platform) {
                $output->writeln('<info>' . $platform . '</info>');
            }
        } else {
            // If we get an argument, replace the platformServices array with one containing just that plaform
            if ($platform = $input->getArgument('platform')) {
                 $platformServices = array($platform);
            }

            // Iterate on the enabled platforms to retrieve the actual object
            foreach ($platformServices as $platform) {
                // Store the platform object
                $platforms[$platform] = $container->get($platform);
            }
            
            // Empty the database beforehand
            foreach($platforms as $name => $platform) {
            }

            // Process each platform :
            foreach ($platforms as $name => $platform) {
                error_log('[DCATGeneration] Processing started for ' . $platform->getName());
                // Load the portal object from the database
                $portal = $platform->loadPortal();                
                // Cache the platform path
                $platformPath = $dataPath . $name . '/';

                if( $portal->getCreatedAt() != NULL ){
                    $document = new \DOMDocument();
                    $document->load("src/OdaliskProject/Bundle/Resources/dcat/catalogBaseFile.rdf");

                    $this->generatePortalInfo($document, $portal);

                    $document->save("web/bundles/odalisk/dcat/" . $portal->getName() . ".rdf");
                }
                else{
                    error_log("No data available");
                }


    /*

                $this->generatePortalInfo($portal);

                $datasets = $portal->getDatasets();
    
                foreach ($datasets as $dataset) {
                    $this->generateDatasetInfo($portal, $dataset);
                }
    */
                }
        }
        $end = time();
        error_log('[DCATGeneration] Processing ended after ' . ($end - $start) . ' seconds');
    
    }
    
    protected function generatePortalInfo(\DOMDocument $document, Portal $portal){

        // Defining language resource url
        $langUrl = "http://id.loc.gov/vocabulary/iso639-1/";
        // Define a XPath object used to make queries on the document
        $xpath = new \DOMXPath($document);

        $xpath->evaluate('//dcat:Catalog/dct:title')->item(0)->nodeValue = $portal->getName();
        $xpath->evaluate('//dcat:Catalog/dct:description')->item(0)->nodeValue = "";

        $xpath->evaluate('//dcat:Catalog/dct:issued')->item(0)->nodeValue = $portal->getCreatedAt()->format("Y-m-d");
        $xpath->evaluate('//dcat:Catalog/dct:modified')->item(0)->nodeValue = $portal->getUpdatedAt()->format("Y-m-d");;

        $lang = $this->getLangAbbreviation($portal->getCountry());
        $langUrl = $langUrl . $lang;

        $xpath->evaluate('//dcat:Catalog/dct:language')->item(0)->setAttribute( "rdf:resource" , $langUrl );

        $xpath->evaluate('//dcat:Catalog/dct:license/rdfs:label')->item(0)->nodeValue = "";

        $xpath->evaluate('//dcat:Catalog/foaf:homepage')->item(0)->setAttribute( "rdf:resource" , $portal->getUrl() );
        
        $xpath->evaluate('//dcat:Catalog/dct:spatial/dct:Location/rdfs:label')
              ->item(0)->nodeValue = $portal->getCountry();

        $xpath->evaluate('//dcat:Catalog/dct:publisher/foaf:Organization/rdfs:label')
              ->item(0)->nodeValue = $portal->getEntity();
        $xpath->evaluate('//dcat:Catalog/dct:publisher/foaf:Organization/foaf:status')
              ->item(0)->nodeValue = $portal->getStatus();              

    }

    protected function generateDatasetInfo(Portal $portal, Dataset $dataset){
   
        // Defining language resource url
        $langUrl = "http://id.loc.gov/vocabulary/iso639-1/";
        // Define a XPath object used to make queries on the document
        $xpath = new \DOMXPath($document);



        
        /*
       

       // Ecrivons quelque chose dans notre fichier.
           // Select language based on location
            $langArray = array(  'European Union' => "en", 
                            'France'         => "fr",
                         );

            if( array_key_exists($portal->getCountry(), $langArray) )
                $lang = $langArray[$portal->getCountry()];
            else
                $lang = "en";

            fwrite($handle,"\t\t<dcat:dataset>\n");
            fwrite($handle,"\t\t\t<dcat:Dataset rdf:about=\"" . $dataset->getUrl() . "\">\n"); 
            fwrite($handle,"\t\t\t\t<dct:issued rdf:datatype=\"http://www.w3.org/2001/XMLSchema#date\">" . $dataset->getReleasedOn() . "</dct:issued>\n");
            fwrite($handle,"\t\t\t\t<dct:modified rdf:datatype=\"http://www.w3.org/2001/XMLSchema#date\">" . $dataset->getLastUpdatedOn() . "</dct:modified>\n");
            fwrite($handle,"\t\t\t\t<dct:creator>" . $dataset->getProvider() . "</dct:creator>\n");
            fwrite($handle,"\t\t\t\t<dct:description xml:lang=\"" . $lang . "\">" . $dataset->getSummary() . "</dct:description>\n");
            fwrite($handle,"\t\t\t\t<dct:license rdf:resource=\"\"/>\n");

            fwrite($handle,"\t\t\t\t<dcat:keyword xml:lang=\"" . $lang . "\"></dcat:keyword>\n");
            fwrite($handle,"\t\t\t\t<dcat:distribution>\n");
               fwrite($handle,"\t\t\t\t\t<dcat:Download>\n");
                  fwrite($handle,"\t\t\t\t\t\t<dcat:accessURL>" . $dataset->getUrl() . "</dcat:accessURL>\n");



                  fwrite($handle,"\t\t\t\t\t\t<dct:format>\n");

                  $formats = $dataset->getFormats();
                  foreach($formats as $format){
                    fwrite($handle,"\t\t\t\t\t\t\t<dct:IMT>\n");
                       // fwrite($handle,"\t\t\t\t\t\t\t\t<rdf:value></rdf:value>\n");
                        fwrite($handle,"\t\t\t\t\t\t\t\t<rdfs:label>" . $format->getFormat() . "</rdfs:label>\n");
                    fwrite($handle,"\t\t\t\t\t\t\t</dct:IMT>\n");        

                  }


                  fwrite($handle,"\t\t\t\t\t\t</dct:format>\n");

                  fwrite($handle,"\t\t\t\t\t\t<dct:modified rdf:datatype=\"http://www.w3.org/2001/XMLSchema#date\">" . $dataset->getLastUpdatedOn() . "</dct:modified>\n");
               fwrite($handle,"\t\t\t\t\t</dcat:Download>\n");
            fwrite($handle,"\t\t\t\t</dcat:distribution>\n");
            fwrite($handle,"\t\t\t\t<dct:publisher>\n");
               fwrite($handle,"\t\t\t\t\t<foaf:Organization>\n");
                  fwrite($handle,"\t\t\t\t\t\t<dct:title xml:lang=\"" . $lang . "\">" . $dataset->getOwner() . "</dct:title>\n");
                  fwrite($handle,"\t\t\t\t\t\t<foaf:homepage rdf:resource=\"" . $portal->getUrl() . "\"/>\n");
               fwrite($handle,"\t\t\t\t\t</foaf:Organization>\n");
            fwrite($handle,"\t\t\t\t</dct:publisher>\n");
         fwrite($handle,"\t\t\t</dcat:Dataset>\n");
      fwrite($handle,"\t\t</dcat:dataset>\n");

      fclose($handle);
    */
    } 

    /**
     * Generate language url using the platform language
     * We don't know the platform language so we use the location
     * We use english (en) as a default value
     */
    protected function getLangAbbreviation( $portalLocation ){

        $langArray = array( 'France'   => "fr",
                            'Espana'   => "es",
                          );

        if( array_key_exists( $portalLocation, $langArray ) )
            $lang = $langArray[$portalLocation];
        else
            $lang = "en";

        return $lang;
    }   
}

