<?php

namespace OdaliskProject\Bundle\Scraper\DataPublica;

use Symfony\Component\DomCrawler\Crawler;

use OdaliskProject\Bundle\Scraper\Tools\RequestDispatcher;

use OdaliskProject\Bundle\Scraper\BasePortal;

/**
 * The scraper for in DataPublica
 */
class DataPublicaPortal extends BasePortal
{
    protected $estimatedDatasetCount = 0;

    public function __construct()
    {
        $this->criteria = array(
            'setName' => ".//*[@id='content']/article[1]/h2",
            'setCategories' => "//div/h5[text()='Catégories']/../following-sibling::*/ul/li/a",
            'setRawLicense' => "//div/h5[text()='Licence']/../following-sibling::*",
            'setReleasedOn' => "//div/h5[text()='Date de création']/../following-sibling::*",
            'setLastUpdatedOn' => "//div/h5[text()='Date de mise à jour']/../following-sibling::*",
            'setSummary' => ".//*[@id='description']",
            //'setMaintainer' => ".//*[@id='publication_tab_container']/ul/li[1]/div[2]/a",
            'setOwner' => "//div/h5[text()='Editeur']/../following-sibling::*",
            'setFormats' => './/*[@class="format"]/li',
        );

        $this->datasetsListUrl = 'http://www.data-publica.com/search/?page=';
        $this->urlsListIndexPath = ".//*[@id='content']/article[2]/ol/li/a";
    }

    public function getDatasetsUrls()
    {
        $dispatcher = new RequestDispatcher($this->buzzOptions, 30);

        $response = $this->buzz->get($this->datasetsListUrl.'1');
        if (200 == $response->getStatusCode()) {
            // We begin by fetching the number of datasets
            $crawler = new Crawler($response->getContent());
            $nodes = $crawler->filterXPath('.//ul[@class="pagenav"]/li[last()]/a');

            if (0 < count($nodes)) {
                $pages_to_get = intval($nodes->first()->text());

                // Since we already have the first page, let's parse it !
                $this->urls = array_merge(
                    $this->urls,
                    $crawler->filterXPath($this->urlsListIndexPath)->extract(array('href'))
                );

                $this->estimatedDatasetCount = count($this->urls) * $pages_to_get;
                error_log('[Get URLs] Estimated number of datasets of the portal : ' . $this->estimatedDatasetCount);
                error_log('[Get URLs] Aproximately ' . $pages_to_get . ' requests to do');

                for ($i = 2 ; $i <= $pages_to_get ; $i++) {
                    $dispatcher->queue(
                        $this->datasetsListUrl.$i,
                        array($this,'OdaliskProject\Bundle\Scraper\DataPublica\DataPublicaPortal::crawlDatasetsList')
                    );
                }

                $dispatcher->dispatch(10);
            }
        }

        foreach ($this->urls as $key => $id) {
            $this->urls[$key] = $this->getBaseUrl() . $id;
        }

        $this->totalCount = count($this->urls);


        return $this->urls;
    }
}
