<?php

namespace OdaliskProject\Bundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use OdaliskProject\Bundle\Entity\DatasetCriteria;

/**
 * A command that extracts the information from the dataset crawled previously
 */
class ExtractCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('odalisk:extract')
            ->setDescription('3 Extract the information from the HTML pages previoulsy crawled')
            ->addArgument('platform', InputArgument::OPTIONAL,
                'Which platform do you want to analyse?'
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
        $platformServices = $container->getParameter('config.enabled_portals.adhoc');
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
            //  - get successful crawls from the database
            //  - parse the corresponding files
            foreach ($platforms as $name => $platform) {
                error_log('[Analysis] Beginning to process ' . $platform->getName());
                // Load the portal object from the database
                $portal = $platform->loadPortal();                
                // Cache the platform path
                $platformPath = $dataPath . $name . '/';

                $count = 0;
                $total = count(glob($platformPath . '*')) - 1;
                $codes = array();


                echo "bouh";
                if ($dh = @opendir($platformPath)) {
                    while (($file = readdir($dh)) !== false) {
                        $data = json_decode(file_get_contents($platformPath . $file), true);
echo "bouh2";
                        if (null != $data && array_key_exists('meta', $data)) {
                            $code = $data['meta']['code'];
                            if (!is_int($code)) {
                                $code = 'timeout';
                            }
                            $codes[$code] = (array_key_exists($code, $codes)) ? $codes[$code] + 1 : 1;

                            if (200 == $code) {
                                $count++;
                                $dataset = new \OdaliskProject\Bundle\Entity\Dataset();
                                $dataset->setUrl($data['meta']['url']);
                                $dataset->setPortal($portal);
                                $platform->analyseHtmlContent($data['content'], $dataset);
                                $criteria = new DatasetCriteria($dataset);
                                $dataset->setCriteria($criteria);
                                $em->persist($criteria);
                                $em->persist($dataset);
                                $dataset = null;

                                if (0 == ($count % 100) || $count == $total) {
                                   error_log('[Analysis] ' . $count . ' / ' . $total . ' done');
                                   error_log('[Analysis] currently using ' . memory_get_usage(true) / (1024 * 1024) . 'MB of memory');
                                }
                            }
                        }
                        $data = null;
                    }
                    closedir($dh);
                } else {
                    error_log('[Analysis] nothing to be done. Perhaps ./console odalisk:crawl ' . $name);
                    continue;
                }echo "bouh3";
                $portal->setDatasetCount($count);
                $em->persist($portal);

                error_log('[Analysis] ' . $count . ' / ' . $total . ' done');
                error_log('[Analysis] ' . ($total - $count) . ' datasets failed to download' . "\n");
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
