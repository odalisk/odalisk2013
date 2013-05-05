<?php

namespace OdaliskProject\Bundle\Scraper\Tools\Normalize;

class DateNormalizer
{
    /**
     * Match some regex to translate months from word to number ie: jan => 01
     * 
     * @var string
     */
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
        '/(.+)?(décembre|dec|december)(-|\s.+)/i' =>  array(
            '12M',
        ),
    );

    /**
     * Values for date fields that are considered equivalent to empty
     *
     * @var array $emptyDates
     */
    protected $emptyDates = array('/', 'TBC', 'not known', '');

    /**
     * The date type fields we need to process so we can transform them into DateTime objects
     *
     * @var array $dateFields
     */
    protected $dateFields = array('setReleasedOn', 'setLastUpdatedOn');

    public function __construct($log)
    {
        $this->log = $log;
    }

    public function normalize(array &$data)
    {
        $i = 0;
        // We transform dates strings in datetime.
        foreach ($this->dateFields as $field) {
            if (array_key_exists($field, $data)) {
                // Define default values
                $time = "00:00:00";
                $d = "01";
                $m = "01";
                $y = "2013";

                $date = $data[$field];
                $dateO = $date;

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
                    $date = $date . ' ' . $time;

                // Try to match the date against something we know
                    if (preg_match('/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}\s([0-9]{2}\:){2}[0-9]{2}$/i', $date, $m)) {
                        // Depending on how many matches we have, we know which format to pick
                        $data[$field] = \Datetime::createFromFormat("d-m-Y H:m:s", $date)->format("d-m-Y H:m:s");
                        if (false === $data[$field]) {
                            error_log(
                                '[' . date('d-M-Y H:i:s') . '] [>>> False positive] ' 
                                . $date . ' with ' . $regex . ' (count = ' . (count($m)-1) .")\n", 
                                3, $this->log
                            );
                            $data[$field] = null;
                        }
                        // Check out the next field directly
                        continue 1;
                    }

                // This is executed only if we have no match
                // Check if it is a known empty-ish value
                if (in_array($date, $this->emptyDates)) {
                    $data[$field] = null;
                } else {
                    // Not something we recognize
                    error_log('[' . date('d-M-Y H:i:s') . '] [Unknown date format] ' .$dateO. ' => ' . $date . "\n", 3, $this->log);
                    $data[$field] = $date;
                }
                $date = null;
            }
        }
    }
}
