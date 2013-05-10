<?php

namespace OdaliskProject\Bundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use OdaliskProject\Bundle\Entity\DatasetCriteria;
use OdaliskProject\Bundle\Scraper\Tools\Normalize\DateNormalizer;


/**
 * A useful command to help you testing the date normalization
 */
class TestCommand extends BaseCommand
{
      protected $translateMonths = array(
        '/(.+)?(janvier|jan|january)(-|\s.+)/i' =>  array(
            '01M',
        ),
        '/(.+)?(février|fév|february|feb)(-|\s.+)/i' =>  array(
            '02M',
        ),
        '/(.+)?(mars|mar|march)(-|\s.+)/i' =>  array(
            '03M',
        ),
        '/(.+)?(avril|avr|april|apr)(-|\s.+)/i' =>  array(
            '04M',
        ),
        '/(.+)?(mai|may)(-|\s.+)/i' =>  array(
            '05M',
        ),
        '/(.+)?(juin|june|jun)(-|\s.+)/i' =>  array(
            '06M',
        ),
        '/(.+)?(juillet|july|jul)(-|\s.+)/i' =>  array(
            '07M',
        ),
        '/(.+)?(aout|août|aou|august|aug)(-|\s.+)/i' =>  array(
            '08M',
        ),
        '/(.+)?(septembre|sept|sep|september)(-|\s.+)/i' =>  array(
            '09M',
        ),
        '/(.+)?(octobre|oct|october)(-|\s.+)/i' =>  array(
            '10M',
        ),
        '/(.+)?(novembre|nov|november)(-|\s.+)/i' =>  array(
            '11M',
        ),
        '/(.+)?(décembre|déc|dec|december)(-|\s.+)/i' =>  array(
            '12M',
        ),
    );
    protected function configure()
    {
        $this
            ->setName('odalisk:test')
            ->setDescription('command to test things')
            ->addArgument('platform', InputArgument::OPTIONAL,
                'Which platform do you want to analyse?'
            )
            ->addOption('list', null, InputOption::VALUE_NONE,
                'If set, the task will display available platforms names rather than analyse them'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dateNormalizer = new DateNormalizer ('test.log');
        
        error_log("\n", 3, $dateNormalizer->log);

        $datesToTest = array( '02/02/2012',
                              ' truc avant 03.12.2452 truc apres   ',
                              '   truc avant 03.12.2452 truc   apres',
                              'éà azd # /// 03.12.2452 (coucou)',
                              '03 12 2452',
                              ' 03 12 2452   ',
                              '03 Jan 2013',
                              '03 Janvier 2013',
                              '03 janvier 2013',
                              '03 Fév 2013',
                              '05/08/2012 03:08:09',
                              '03.12.2452 03:08:09',
                              '03-12-2452 03:08:09',
                              '03 12 2452 03:08:09',
                              '03/12/2452T03:08:09',
                              '03.12.2452T03:08:09',
                              '03-12-2452T03:08:09',
                              '03 12 2452T03:08:09',
                              'May 01, 2008',
                              'Mar 31, 2011',
                              '19 déc. 2011',
                              '19/08/2011',
                              '08/19/2011',
                              'blabla (1980)',
                              '1562',
                              '03.12.2452 there is something in the MIDDLE éé ? 03:08:09',
                              '2010-11-15',
                              '2010-03-31T00:00:00',

                     );
 
        foreach ($datesToTest as $key => $date) {
            $time = "00:00:00";
            $d = "01";
            $m = "01";
            $y = "2013";
            // date before normalization
            error_log($key . " : '" . $date . "'", 3,
                        $dateNormalizer->log);

            // begining normalization
                // 01: extract time if provided else use default value => '00:00:00'
                    if(preg_match('/(.+)(([0-9]{2}\:){2}[0-9]{2})/', $date, $matches))
                    {
                        $date = $matches[1];
                        $time = $matches[2];
                    }
                // 0 replace every separator with a space
                    $date = preg_replace('/[\/\-\.\,]/', ' ', $date); 
                    $date = strtolower($date);  

                // replace month in literal form with digits + M (ex: Jan => 01M)
                    foreach($this->translateMonths as $tM => $month){
                      $date = preg_replace($tM, '${1}'. $month[count($month)-1] . '$3', $date);
                    }

                // tag year in date
                    $date = preg_replace('/(\d{4})/', '$1Y', $date);               

                // 0 delete all which is not a date (apart from UPERCASE char marking numbers)
                // delete double spaces
                    $date = preg_replace('#[a-záàâäéèêëíìîïóòôöúùûüç&\(\)\#]+#', '', $date);               
                    $date = trim($date);
                    $date = preg_replace('/\s\s/s', ' ', $date);     


                    if(preg_match('/^([0-9]{2})\s/', $date, $matches)){
                      $d = $matches[1];
                    }
                    else{
                      if(preg_match('/^([0-9]{4})Y/', $date, $matches)){
                        $y = $matches[1];
                      }
                      if(preg_match('/([0-9]{2})M/', $date, $matches)){
                        $m = $matches[1];
                      }
                      else { 
                        if(preg_match('/^[0-9]{4}Y\s([0-9]{2})\s/', $date, $matches)){
                          $m = $matches[1];
                        }
                      }
                      if(preg_match('/[0-9]{4}Y\s[0-9]{2}\s([0-9]{2})/', $date, $matches)){
                        $d = $matches[1];
                      }
                      else{
                        if(preg_match('/[0-9]{4}Y\s[0-9]{2}M\s([0-9]{2})/', $date, $matches)){
                          $d = $matches[1];
                        }
                      }
                    }

                    if(preg_match('/([0-9]{2})M/', $date, $matches)){
                      $m = $matches[1];
                    }
                    else { 
                      if(preg_match('/^[0-9]{2}\s([0-9]{2})/', $date, $matches)){
                        $m = $matches[1];
                      }
                    }
                    if(preg_match('/([0-9]{4})Y/', $date, $matches)){
                      $y = $matches[1];
                    }

                    $date = $d . '-' . $m . '-' . $y;

                //
                    $date = $date . ' ' . $time;

            // date after normalization
            error_log("\t => \t'" . $date . "'", 3,
                        $dateNormalizer->log);

            // 2013-05-05 00:00:00
            // regex match or no (if date is in a good format or not)
            if (preg_match('/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}\s([0-9]{2}\:){2}[0-9]{2}$/i', $date, $m)) 
            {
                error_log("\t(OK)" . "\n", 3,
                        $dateNormalizer->log);
            }else{
                error_log("\t(KO) <" . "\n", 3,
                        $dateNormalizer->log);
            }

       }
    }
}
