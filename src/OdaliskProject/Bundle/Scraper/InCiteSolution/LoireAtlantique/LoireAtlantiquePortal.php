<?php

namespace OdaliskProject\Bundle\Scraper\InCiteSolution\LoireAtlantique;

use OdaliskProject\Bundle\Scraper\InCiteSolution\BaseInCiteSolutionPortal;

use OdaliskProject\Bundle\Scraper\Tools\RequestDispatcher;

use Symfony\Component\DomCrawler\Crawler;


/**
 * The scraper for data.loire-atlantique.fr
 */
class LoireAtlantiquePortal extends BaseInCiteSolutionPortal
{
  
    public function __construct()
    {
        $this->criteria = array(
            'setName' => '//*[@id="c320"]/div/div/div[1]/h1/text()',
            'setCategories' => '//span[.="Thématique(s) : "]/../span[@class="value"]/text()', 
            'setRawLicense' => '//*[@id="c320"]/div/div/div[2]/div[1]/div/div/span[2]',
            //'Update Frequency' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[8]/span[2]',
            'setReleasedOn' => '//*[@id="c320"]/div/div/div[1]/div[1]/span',
            'setLastUpdatedOn' => '//span[.="Mis à jour le : "]/../span[@class="value"]/text()',
            'setSummary' => '//*[@id="c320"]/div/div/div[1]/div[2]/div[2]/span/text()',
            'setMaintainer' => '//span[.="Gestionnaire : "]/../span[@class="value"]/text()',
            'setOwner' => '//span[.="Propriétaire : "]/../span[@class="value"]/text()',
            //'Technical data' => ".//*[@class='tx_icsoddatastore_pi1_technical_data separator']/span[@class='value']",
            //Can't access the format unless the validation of the license on the website
            'setFormats' => '//*[@id="c320"]/div/div/div[2]/div[1]/div/div[4]/div[1]/div[1]/a/img/@alt',
            'setProvider' => '//span[.="Diffuseur : "]/../span[@class="value"]/text()'
        );

    } 

}
