<?php

namespace OdaliskProject\Bundle\Scraper\CKAN\UK;

use OdaliskProject\Bundle\Scraper\CKAN\BaseCkanPortal;

use Symfony\Component\DomCrawler\Crawler;

/**
 * The scraper for data.gov.uk
 */
class UKGovPortal extends BaseCkanPortal
{
    public function __construct()
    {
        $this->criteria = array(
            'setName' => '//*[@id="content"]//h1/text()',              
            'setSummary' => '//*[@id="dataset-overview"]/div[1]/div/p/text()',
            'setReleasedOn' => '//strong[.="Added to data.gov.uk: "]/../text()',
            'setLastUpdatedOn' => '//strong[.="Modified on data.gov.uk: "]/../text()',
            'setOwner' => '//h4[.="Publisher"]/../p/a/text()',
            'setMaintainer' => '//dataset//contributor/description/name',
            'setRawLicense' => '//*[@id="dataset-license"]/div/ul/li/a[1]/text()',
            'setCategories' => '//tr[td[.="ONS Category" and @class="dataset-label"]]/td[2]/text()',
            'setFormats' => './/*[@property="dc:format"]'

        );
    }

    protected function additionalExtraction($crawler, &$data)
    {
        if (!array_key_exists('setReleasedOn', $data)) {
            $nodes = $crawler->filterXPath('//*[(@id = "tagline")]');

            if (0 < count($nodes)) {
                $content = trim(join(
                    ";",
                    $nodes->each(
                        function($node,$i) {
                            return $node->nodeValue;
                        }
                    )
                ));

                if (preg_match('/^Posted by ([a-zA-Z &,\'-]+) on ([0-9]{2}\/[0-9]{2}\/[0-9]{4})/', $content, $matches)) {
                    $data['setProvider'] = $matches[1];
                    $data['setReleasedOn'] = $matches[2];
                } else {
                    error_log('>>' . $content);
                }
            }
        }

        if (array_key_exists('setRawLicense', $data)) {
            $licenses = json_decode($data['setRawLicense']);
            if (is_array($licenses)) {
                $data['setRawLicense'] = implode(';', $licenses);
            }

            /*
            if (preg_match('/CCGC\/CCW/i',$data['setRawLicense'])) {
                $data['setRawLicense'] = "CCW/CROWN";
            }
            if (preg_match('/CCW/i',$data['setRawLicense'])) {
                $data['setRawLicense'] = "CCW/CROWN";
            }
            if (preg_match('/Crown/i',$data['setRawLicense'])) {
                $data['setRawLicense'] = "CCW/CROWN";
            }

            if (preg_match('/UK Climate Projections Licence/i',$data['setRawLicense'])) {
                $data['setRawLicense'] = "UK Climate Projections Licence";
            }

            if (preg_match('/^OKD Compliant/i',$data['setRawLicense'])) {
                if (preg_match("/pddl/i", $data['setRawLicense'])) {
                    $data['setRawLicense'] = "PDDL";

                    return;
                }
                $data['setRawLicense'] = "ODBL";
            }
            */
        }
    }
}
