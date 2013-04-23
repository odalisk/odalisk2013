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
            'setCategories' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[3]/span[2]/p/text()',
            'setRawLicense' => '//*[@id="c100"]/div/div/div[2]/div[1]/div/div/span[2]',
            //'Update Frequency' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[8]/span[2]',
            'setReleasedOn' => '//*[@id="c100"]/div/div/div[1]/div[1]/span',
            'setLastUpdatedOn' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[7]/span[2]',
            'setSummary' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[2]/span/text()',
            'setMaintainer' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[9]/span[2]',
            'setOwner' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[10]/span[2]',
            //'Technical data' => ".//*[@class='tx_icsoddatastore_pi1_technical_data separator']/span[@class='value']",
            //Can't access the format unless the validation of the license on the website
            'setFormats' => '//*[@id="c100"]/div/div/div[2]/div[1]/div/div[4]/div[1]/div[1]/a/img/@alt',
            'setProvider' => '//*[@id="c100"]/div/div/div[1]/div[2]/div[11]/span[2]'
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
