<?php

namespace OdaliskProject\Bundle\Scraper\CKAN\DataEU;

use OdaliskProject\Bundle\Scraper\CKAN\BaseCkanPortal;

class EUPortal extends BaseCkanPortal
{
    public function __construct()
    {
        $this->criteria = array(
            'setName' => '//section[@class="module-content"]/h1',
            'setSummary' => '//div[@class="notes embedded-content"]/p[1]',
            'setReleasedOn' => '//tr[@rel="dc:relation" and th[.="date_released" and @class="dataset-label"]]/td/text()',
            'setOwner' => './/*[@property="dc:creator"]',
            'setMaintainer' => './/*[@property="dc:contributor"]',
            'setLastUpdatedOn' => '//tr[@rel="dc:relation" and th[.="date_updated" and @class="dataset-label"]]/td/text()',
            'setProvider' => '//td[.="published_by" and @class="dataset-label"]/../td[2]',
            'setRawLicense' => '//*[@id="content"]/div[3]/aside/section[3]/p/a[1]/text()',
            'setCategories' => '//td[text()="categories"]/following-sibling::*',
            'setFormats' => './/*[@property="dc:format"]'
        );

        $this->inChargeFields = array('setOwner','setMaintainer');
    }

    protected function additionalExtraction($crawler, &$data)
    {

        if (array_key_exists('setCategories', $data)) {
            if (is_array(json_decode($data['setCategories']))) {
                $data['setCategories'] = implode(';', json_decode($data['setCategories']));
            }
        }
    }

    protected function additionalNormalization(&$data)
    {
        foreach ($this->inChargeFields as $field) {
            if (array_key_exists($field, $data)) {
                if (preg_match("/not given/i",$data[$field])) {
                    unset($data[$field]);
                }
            }
        }
    }
}
