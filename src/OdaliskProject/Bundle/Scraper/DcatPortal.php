<?php

namespace OdaliskProject\Bundle\Scraper;


use OdaliskProject\Bundle\Scraper\BasePortal;


class DcatPortal extends BasePortal
{
    protected $datasets = array();

    public function __construct()
    {
        $this->criteria = array(
            'setName' => '//dataset/label/text()',
            'setSummary' => '//dataset/description/text()',
            'setReleasedOn' => '//dataset//relation/description[label="date_released"]/value/text()',
            'setOwner' => '//dataset/creator/description/name/text()',
            'setMaintainer' => '//dataset//contributor/description/name',
            'setLastUpdatedOn' => '//dataset//relation/description[label="date_updated"]/value/text()',
            'setProvider' => '//dataset//relation/description[label="creator"]/value/text()',
            'setRawLicense' => '//rights/@*',
            'setCategories' => '//dataset//relation/description[label="categories"]/value/text()',
            'setFormats' => '//dataset//distribution/format/imt/value/text()',
            'setUrl' => '//dataset/@*'
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
