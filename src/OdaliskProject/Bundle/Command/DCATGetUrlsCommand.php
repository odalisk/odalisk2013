<?php

namespace OdaliskProject\Bundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use OdaliskProject\Bundle\Scraper\Tools\FileDumper;

/**
 * A command that will search the url of the datasets in rdf for the enabled platforms
 */
class DCATGetUrlsCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('odalisk:dcat:geturls')
            ->setDescription('DCAT Fetch rdf datasets urls for all supported platforms')
            ->addArgument('platform', InputArgument::OPTIONAL,
                'Which platform ?'
            )
            ->addOption('list', null, InputOption::VALUE_NONE,
                'If set, the task will display available platforms names'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = time();
        // Store the container so that we have an easy shortcut
        $container = $this->getContainer();
        // Initialize the file dumper
        $dataPath = $container->getParameter('config.file_dumper.data_path');
        FileDumper::setBasePath($dataPath);
        // Get the configuration value from config/portals.yml : which platforms are enabled?
        $platformServices = $container->getParameter('config.enabled_portals');
        // Initialize some arrays
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

            foreach ($platforms as $name => $platform) {
                error_log('[Get URLs] Getting urls for platform : ' . $platform->getName());
                FileDumper::saveRdfUrls($platform->getDatasetsUrls(), $name);
                error_log('[Get URLs] ' . $platform->getName() . ' has ' . $platform->getTotalCount() . ' urls');
            }
        }
        $end = time();
        error_log('[Get URLs] Processing ended after ' . ($end - $start) . ' seconds');
    }
}
