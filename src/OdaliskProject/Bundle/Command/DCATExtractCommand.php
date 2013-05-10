<?php

namespace OdaliskProject\Bundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use OdaliskProject\Bundle\Entity\DatasetCriteria;

/**
 * A command that will extract from the datasets stored in MongoDb the information needed by the project
 */
class DCATExtractCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('odalisk:dcat:extract')
            ->setDescription('DCAT Extract the information from the rdf files in mongo previoulsy crawled')
            ->addArgument('platform', InputArgument::OPTIONAL,
                ' Which platform do you want to analyse?'
            )
            ->addOption('list', null, InputOption::VALUE_NONE,
                'If set, the task will display available platforms names rather than analyse them'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = time();
        // Store the container so that we have an easy shortcut
        $container = $this->getContainer();
        // Get the configuration value from config/app.yml : which platforms are enabled?
        $platformServices = $container->getParameter('config.enabled_portals.dcat');
        // Get the data directory
        $dataPath = $container->getParameter('config.file_dumper.data_path');
        // Entity repository for datasets_crawls & entity manager
        $em = $this->getEntityManager();
        //$em->getConnection()->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
        //The document manager of mongoDb
        $docManager = $this->getMongoDbManager();
        $dcatDatasetsRepo = $docManager->getRepository('OdaliskProject\Bundle\Document\DcatDataset');


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
            

            // Process each platform :
            //  - get the dataset stored in MongoDB
            //  - parse the corresponding files
            foreach ($platforms as $name => $platform) {
                error_log('[Analysis] Beginning to process ' . $platform->getName());
                // Load the portal object from the database
                $portal = $platform->loadPortal();                
                // Cache the platform path
                $platformPath = $dataPath . $name . '/';

                $count = 0;
                $codes = array();

                //$datasetsOfPortal = $dcatDatasetsRepo->findBy(
                //    array('portalName' => $name));

                error_log($name);
                $datasetsOfPortal = $docManager->createQueryBuilder('OdaliskProject\Bundle\Document\DcatDataset')->field('portalName')->equals($name)->getQuery();
                $total = count($datasetsOfPortal);
                if (!$datasetsOfPortal) {
                    error_log('[Analysis] nothing to be done. Perhaps ./console odalisk:dcat:crawl ' . $name);
                    continue;
                }

                foreach ($datasetsOfPortal as $datasetToAnalyse) {
                    
                    $count++;

                    //Creation of a new Dataset
                    $dataset = new \OdaliskProject\Bundle\Entity\Dataset();
                    $dataset->setPortal($portal);
                    $dataset->setIdMongo($datasetToAnalyse->getId());



                    //Then we extract the file stored in Mongo
                    $fileContent = $datasetToAnalyse->getFile()->getBytes();
                    $data = json_decode($fileContent,true);
                    
                    //We analyse the content
                    $platform->analyseDcatContent($data, $dataset);
                    $criteria = new DatasetCriteria($dataset);
                    $dataset->setCriteria($criteria);


                    //And we persist the data
                    $em->persist($criteria);
                    $em->persist($dataset);
                    $dataset = null;
                    $data = null;

                    if (0 == ($count % 100) || $count == $total) {
                        error_log('[Analysis] ' . $count . ' done');
                        error_log('[Analysis] currently using ' . memory_get_usage(true) / (1024 * 1024) . 'MB of memory');
                    }


                }


                $portal->setDatasetCount($count);
                $em->persist($portal);

                error_log('[Analysis] ' . $count . ' datasets analysed');
                error_log('[Analysis] Return codes repartition :');
                foreach ($codes as $code => $count) {
                   error_log('[Analysis] ' . $code . ' > ' . $count);
                }
                error_log('[Analysis] Persisting data to the database');
                error_log('[Analysis] currently using ' . memory_get_usage(true) / (1024 * 1024) . 'MB of memory');
                $em->flush();
                error_log('[Analysis] currently using ' . memory_get_usage(true) / (1024 * 1024) . 'MB of memory');
            }
        }
        $end = time();
        error_log('[Analysis] Processing ended after ' . ($end - $start) . ' seconds');
    }
}