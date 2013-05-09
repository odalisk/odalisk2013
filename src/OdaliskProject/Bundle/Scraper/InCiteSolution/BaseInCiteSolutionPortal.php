<?php

namespace OdaliskProject\Bundle\Scraper\InCiteSolution;

use Buzz\Message;

use OdaliskProject\Bundle\Scraper\BasePortal;


/**
 * The scraper for in cite Solution Plateform
 */
abstract class BaseInCiteSolutionPortal extends BasePortal
{

    public function __construct()
    {
        $this->criteria = array(
            'setName' => '//*[@id="c100"]/div/div/div[1]/h1/text()',
            'setCategories' => '//p[@class="value categories"]/text()', 
            'setRawLicense' => '//*[@id="c100"]/div/div/div[2]/div[1]/div/div/span[2]',
            //'Update Frequency' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[8]/span[2]',
            'setReleasedOn' => '//*[@id="c100"]/div/div/div[1]/div[1]/span',
            'setLastUpdatedOn' => '//span[.="Mis à jour le : "]/../span[@class="value"]/text()',
            'setSummary' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[2]/span/text()',
            'setMaintainer' => '//span[.="Gestionnaire : "]/../span[@class="value"]/text()',
            'setOwner' => '//span[.="Propriétaire : "]/../span[@class="value"]/text()',
            //'Technical data' => ".//*[@class='tx_icsoddatastore_pi1_technical_data separator']/span[@class='value']",
            //Can't access the format unless the validation of the license on the website
            'setFormats' => '//*[@id="c100"]/div/div/div[2]/div[1]/div/div[4]/div[1]/div[1]/a/img/@alt', 
            'setProvider' => '//span[.="Diffuseur : "]/../span[@class="value"]/text()'
        );
    }

    public function getDatasetsUrls()
    {
        // API Call
        $urls = array();

        $response = $this->buzz->get(
            $this->getApiUrl(),
            $this->buzzOptions
        );

        if (200 == $response->getStatusCode()) {
            $data = json_decode($response->getContent());
            foreach ($data->opendata->answer->data->dataset as $dataset) {
                $urls[] = $this->getBaseUrl() . 'donnees/detail/' . $dataset->id;
            }
        } else {
            error_log('Couldn\'t fetch list of datasets for ' . $this->name);
        }

        $this->totalCount = count($urls);

        return $urls;
    }

    public function prepareRequestsFromUrls($urls)
    {

        $factory = new Message\Factory\Factory();
        $requests = array();

        foreach ($urls as $url) {
            $formRequest = $factory->createFormRequest();
            $formRequest->setMethod(Message\Request::METHOD_POST);
            $formRequest->fromUrl($this->sanitize($url));
            $formRequest->addHeaders($this->buzzOptions);
            $formRequest->setFields(array('tx_icsoddatastore_pi1[cgu]' => 'on'));
            $requests[] = $formRequest;
        }

        return $requests;
    }

    public function sanitize($url)
    {
        return str_replace(']', '%5D', str_replace('[', '%5B', $url));
    }
}
