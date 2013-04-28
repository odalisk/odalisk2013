<?php

namespace OdaliskProject\Bundle\Scraper;


use OdaliskProject\Bundle\Scraper\BasePortal;


class DcatPortal extends BasePortal
{
    protected $datasets = array();

    public function __construct()
    {
        $this->criteria = array(
            'setName' => '//h2[@id="datasetName" and @class="clipText currentViewName"]'
        );
    }

    public function getDatasetsUrls()
    {
        $urls = array();

        // Make the API call
        $response = $this->buzz->get(
            $this->getApiUrl(),
            $this->buzzOptions
        );

        // Get the paths of the rdf files
        if (200 == $response->getStatusCode()) {
            $data = json_decode($response->getContent());

            foreach ($data as $key => $dataset_name) {
                $urls[] = $this->getBaseUrl() . $dataset_name . '.rdf';
            }
        } else {
            error_log('Couldn\'t fetch list of datasets for ' . $this->getName());
        }

        $this->totalCount = count($urls);

        return $urls;
    }
}
