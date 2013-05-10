<?php

namespace OdaliskProject\Bundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


use OdaliskProject\Bundle\Entity\Dataset;
use OdaliskProject\Bundle\Entity\DatasetCriteria;
use OdaliskProject\Bundle\Entity\Metric;

/**
 * Generates statistics
 */

class GenerateStatisticsCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('odalisk:statistics:generate')
            ->setDescription('4 Generate statistics from datasets for the portals')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getEntityManager();
        $criteriaRepo = $this->getEntityRepository('OdaliskProject\Bundle\Entity\DatasetCriteria');

        // Initialization
        $datasets = $this->em->createQuery('SELECT d FROM OdaliskProject\Bundle\Entity\Dataset d')->iterate();
        
        $this->writeBlock($output, "[Statistics] Beginning of generation");

        // Metrics generation
        error_log("[Statistics] Metrics generation");
        $container  = $this->getContainer();
        $metrics    = $container->getParameter('metrics');
        $portalRepo = $this->getEntityRepository('OdaliskProject\Bundle\Entity\Portal');
        $portals    = $portalRepo->findAll();
        $portalCriteriaRepo = $this->getEntityRepository('OdaliskProject\Bundle\Entity\PortalCriteria');

        
        //For each portal
        //We analyse the metric and store the result in SQL (all the metrics can be found in metrics.yml)
        foreach($portals as $portal) {

            $general_value = 0;
            $avgs = $criteriaRepo->getPortalAverages($portal);
            $portalcriteria = $portalCriteriaRepo->getPortalCriteria($portal);
            $metric_general = new \OdaliskProject\Bundle\Entity\Metric();

            foreach($metrics as $name => $category) {
                $value = 0;

                switch($name) {
                    case 'cataloging' :
                        $metric_parent = $this->apply_section($name,$category,$avgs);
                        $metric_parent->setName($name, $avgs);
                        $metric_parent->setCoefficient($category['weight']);
                        $metric_parent->setDescription($category['description']);
                        $this->em->persist($metric_parent);
                    break;

                    default:
                        $metric_parent = $this->apply_section($name,$category,$portalcriteria);
                        $metric_parent->setCoefficient($category['weight']);
                        $metric_parent->setDescription($category['description']);
                        $this->em->persist($metric_parent);
                    break;
                }
                $general_value += $metric_parent->getScore();
                $metric_general->addMetric($metric_parent);
                $metric_parent->setParent($metric_general);
            }
            $metric_general->setScore($general_value);
            $metric_general->setCoefficient(1);
            $metric_general->setName('Total');
            $this->em->persist($metric_general);
            $portal->setMetric($metric_general);
            $this->em->persist($portal);
            
        }
        
        $this->em->flush();
        $this->writeBlock($output, "[Statistics] The end !");
    }


    //A sub function that will fetch a metric with a result furnished by the repository    
    protected function apply_section($name, $criteria, $avgs){
        if (isset($criteria['sections'])) {
            $value = 0;
            $metric_parent = new \OdaliskProject\Bundle\Entity\Metric();
            $metric_parent->setName($name);

            foreach ($criteria['sections'] as $name => $section) {
                $metric = $this->apply_section($name, $section, $avgs);
                $value += $metric->getScore();
                $metric->setParent($metric_parent);
                $this->em->persist($metric);
                $metric_parent->addMetric($metric);
            }
            $metric_parent->setCoefficient($criteria['weight']);
            $metric_parent->setScore($criteria['weight'] * $value);
            $metric_parent->setDescription($criteria['description']);
            $this->em->persist($metric_parent);
            return $metric_parent;
        } else {
            $metric = new \OdaliskProject\Bundle\Entity\Metric();
            $metric->setScore($criteria['weight'] * $avgs[$name]);
            $metric->setDescription($criteria['description']);
            $metric->setCoefficient($criteria['weight']);
            $metric->setName($name);
            return $metric;
        }
    }
}
