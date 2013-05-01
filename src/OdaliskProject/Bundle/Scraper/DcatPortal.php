<?php

namespace OdaliskProject\Bundle\Scraper;


use OdaliskProject\Bundle\Scraper\BasePortal;


class DcatPortal extends BasePortal
{
    protected $datasets = array();

    public function __construct()
    {
        $this->criteria = array(
            'setName' => '//Dataset/label/text()',
            'setSummary' => '//Dataset/description/text()',
            'setReleasedOn' => '//Dataset//relation/Description[label="deposit_date"]/value/text()',
            'setOwner' => '//Dataset/creator/Description/name/text()',
            'setMaintainer' => '//Dataset//relation/Description[label="creator"]/value/text()',
            'setLastUpdatedOn' => '//Dataset//relation/Description[label="update_date"]/value/text()',
            'setProvider' => '//Dataset//relation/Description[label="creator"]/value/text()',
            'setRawLicense' => '//Dataset//relation/Description[label="licence"]/value/text()',
            'setCategories' => '//Dataset//relation/Description[label="categories"]/value/text()',
            'setFormats' => '//Dataset//distribution/format/IMT/value/text()',
            'setUrl' => '//Dataset/@about/text()'
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
