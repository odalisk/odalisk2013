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
    /** 
     * @var string
    */
    protected $resourceUrl = "src/OdaliskProject/Bundle/Resources/dcat/";

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
        $platformServices = array();

        $adhocPlatforms= $container->getParameter('config.enabled_portals.adhoc');

        $dcatPlatforms = $container->getParameter('config.enabled_portals.dcat');
        // If we have additionnal platforms to parse (dcat ones)
        if( !empty($adhocPlatforms) ){
                foreach($adhocPlatforms as $it){
                // adding them to enabled platforms list
                array_push($platformServices, $it);
            }
        }
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

                $folderUrl = $this->resourceUrl . $portal->getName() . "/";

                $document = new \DOMDocument('1.0', 'utf-8');
                $document->preserveWhiteSpace = false;
                $document->formatOutput = TRUE;
                $document->load($this->resourceUrl . "catalogBaseFile.rdf");

                error_log('[DCATGeneration] Generating catalog info');
                $this->generatePortalInfo($document, $portal);
                error_log('[DCATGeneration] Done');
                
                // get the list of every dataset and foreach of them, generate the dcat dataset part
                $datasets = $portal->getDatasets();
                $nbDat = count($datasets);
                $nbDone = 0;

                if( $nbDat > 0 ){
                    error_log('[DCATGeneration] Generating datasets info (' . $nbDat . ')');
                    foreach ($datasets as $dataset) {
                        if( $nbDone % 100 == 0 ){
                            error_log('dataset dcat generated : ' . $nbDone . '/' . $nbDat);
                        }
                        $this->generateDatasetInfo($document, $portal, $dataset);
                        $nbDone++;
                    }
                    error_log('[DCATGeneration] Done');
                }

                // create the folder if it doesn't exists
                if( !is_dir($folderUrl) ){
                    mkdir($folderUrl);    
                }

                // save the result
                file_put_contents($folderUrl . $portal->getName() . ".rdf", html_entity_decode($document->saveXML()));

                // reformat the file to have a well indented output
                /*
                $document = new \DOMDocument('1.0', 'utf-8');
                $document->preserveWhiteSpace = false;
                $document->load($folderUrl . $portal->getName() . ".rdf");
                $document->formatOutput = true; 
                $document->normalizeDocument();
                file_put_contents($folderUrl . $portal->getName() . ".rdf", html_entity_decode($document->saveXML()));

                //$document->save($folderUrl . $portal->getName() . ".rdf");*/
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

        if( $portal->getCreatedAt() != NULL ){
            $xpath->evaluate('//dcat:Catalog/dct:issued')->item(0)->nodeValue = 
               $portal->getCreatedAt()->format("Y-m-d");
        }
        if( $portal->getUpdatedAt() != NULL ){
            $xpath->evaluate('//dcat:Catalog/dct:modified')->item(0)->nodeValue = 
                $portal->getUpdatedAt()->format("Y-m-d");
        }

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

    protected function generateDatasetInfo(\DOMDocument $catalog, Portal $portal, Dataset $dataset){
        // Defining language resource url
        $langUrl = "http://id.loc.gov/vocabulary/iso639-1/";
        // load the default dataset part
        $document = new \DOMDocument('1.0', 'utf-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;
        $document->load($this->resourceUrl . "datasetBaseFile.rdf");
        
        // Define a XPath object used to make queries on the document
        $xpath = new \DOMXPath($document);
        $xpathCatalog = new \DOMXPath($catalog);

        // html entities prevent undefined reference warning
        $xpath->evaluate('//dcat:Dataset/dct:title')->item(0)->nodeValue = htmlentities($dataset->getName());
        $xpath->evaluate('//dcat:Dataset/dct:description')->item(0)->nodeValue = htmlentities($dataset->getSummary());

        if( $dataset->getReleasedOn() != NULL ){
            $xpath->evaluate('//dcat:Dataset/dct:issued')->item(0)->nodeValue = 
                $dataset->getReleasedOn()->format("Y-m-d");
        }
        if( $dataset->getLastUpdatedOn() != NULL ){
            $xpath->evaluate('//dcat:Dataset/dct:modified')->item(0)->nodeValue = 
                $dataset->getLastUpdatedOn()->format("Y-m-d");
        }

        $lang = $this->getLangAbbreviation($portal->getCountry());
        $langUrl = $langUrl . $lang;
        $xpath->evaluate('//dcat:Dataset/dct:language')->item(0)->setAttribute( "rdf:resource" , $langUrl );

        $xpath->evaluate('//dcat:Dataset/dct:spatial/dct:Location/rdfs:label')->item(0)->nodeValue = 
            $portal->getCountry();

        $xpath->evaluate('//dcat:Dataset/dcat:landingPage')->item(0)->nodeValue = 
            $dataset->getUrl();

        $xpath->evaluate('//dcat:Dataset/dcat:keyword')->item(0)->nodeValue = 
            htmlentities($dataset->getRawCategories());



        $xpath->evaluate('//dcat:Dataset/dct:license/rdfs:label')->item(0)->nodeValue = 
            $dataset->getRawLicense();
    
        $xpath->evaluate('//dcat:Dataset/dct:publisher/foaf:Organization/rdfs:label')
              ->item(0)->nodeValue = $dataset->getProvider();

        $formats = $dataset->getFormats();

        foreach($formats as $format){
            $formatNode = $document->createElement('dct:IMT');

            $formatNode->appendChild( $document->createElement('rdf:value', $format->getFormat() ) );
            $formatNode->appendChild( $document->createElement('rdf:label', $format->getFormat() ) );
            $xpath->evaluate('//dct:format')->item(0)->appendChild( $formatNode );

        }

        $document->normalizeDocument();

        // Import the node, and all its children, to the document
        $node = $catalog->importNode($xpath->evaluate('//dcat:Dataset' )->item(0), true);
        // And then append it to the "dataset container" node
        $xpathCatalog->evaluate('//dcat:dataset')->item(0)->appendChild($node);
        $catalog->normalizeDocument();
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

