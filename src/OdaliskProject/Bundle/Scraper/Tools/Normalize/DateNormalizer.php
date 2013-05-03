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
            '01',
        ),
        '/(.+)?(février|fév|february|feb)(-|\s.+)/i' =>  array(
            '02',
        ),
        '/(.+)?(mars|mar|march)(-|\s.+)/i' =>  array(
            '03',
        ),
        '/(.+)?(avril|avr|april|apr)(-|\s.+)/i' =>  array(
            '04',
        ),
        '/(.+)?(mai|may)(-|\s.+)/i' =>  array(
            '05',
        ),
        '/(.+)?(juin|june|jun)(-|\s.+)/i' =>  array(
            '06',
        ),
        '/(.+)?(juillet|july|jul)(-|\s.+)/i' =>  array(
            '07',
        ),
        '/(.+)?(aout|août|aou|august|aug)(-|\s.+)/i' =>  array(
            '08',
        ),
        '/(.+)?(septembre|sept|sep|september)(-|\s.+)/i' =>  array(
            '09',
        ),
        '/(.+)?(octobre|oct|october)(-|\s.+)/i' =>  array(
            '10',
        ),
        '/(.+)?(novembre|nov|november)(-|\s.+)/i' =>  array(
            '11',
        ),
        '/(.+)?(décembre|dec|december)(-|\s.+)/i' =>  array(
            '12',
        ),
    );

    /**
     * Match some regex to known date formats. Order is IMPORTANT!
     *
     * @var string
     */
    protected $correctDates = array(
        '/^[0-9]{4}(.[0-9]{1,2}(.[0-9]{1,2}( [0-9]{2}(:[0-9]{2}(:[0-9]{2})?)?)?)?)?$/' =>  array(
            '!Y',
            '!Y*m',
            '!Y*m*d',
            '!Y*m*d H',
            '!Y*m*d H:i',
            '!Y*m*d H:i:s',
        ),
        '/^(([0-9]{1,2}.)?[0-9]{1,2}.)?[0-9]{4}( [0-9]{2}(:[0-9]{2}(:[0-9]{2})?)?)?$/' => array(
            '!Y',
            '!m*Y',
            '!d*m*Y',
            '!d*m*Y H',
            '!d*m*Y H:i',
            '!d*m*Y H:i:s',
        ),
        '/^(([0-9]{1,2}.)?[0-9]{1,2}.)?[0-9]{2}( [0-9]{2}(:[0-9]{2}(:[0-9]{2})?)?)?$/' => array(
            '!y',
            '!m*y',
            '!d*m*y',
            '!d*m*y H',
            '!d*m*y H:i',
            '!d*m*y H:i:s',
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
                $date = $data[$field];

                $dateO = $date;

                foreach($this->translateMonths as $tM => $month){

                    $date = preg_replace($tM, '${1}'. $month[count($month)-1] . '$3', $date);
                }
                $date = preg_replace('#[A-Za-zÁÀÂÄÉÈÊËÍÌÎÏÓÒÔÖÚÙÛÜáàâäéèêëíìîïóòôöúùûüÇç&\(\)]+#', '', $date);               
                $date = trim($date);

               // error_log($date);


                // Try to match the date against something we know
                foreach ($this->correctDates as $regex => $formats) {
                    // Check if we have a match
                    if (preg_match($regex, $date, $m)) {
                        // Depending on how many matches we have, we know which format to pick
                        $data[$field] = \Datetime::createFromFormat($formats[count($m)-1], $date)->format("d-m-Y H:i");
                        if (false === $data[$field]) {
                            error_log(
                                '[' . date('d-M-Y H:i:s') . '] [>>> False positive] ' 
                                . $date . ' with ' . $regex . ' (count = ' . (count($m)-1) .")\n", 
                                3, $this->log
                            );
                            $data[$field] = null;
                        }
                        // Check out the next field directly
                        continue 2;
                    }
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
