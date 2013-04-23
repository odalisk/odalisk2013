<?php

namespace OdaliskProject\Bundle\Scraper\Socrata\NY;

use OdaliskProject\Bundle\Scraper\Socrata\BaseSocrataPortal;

class NewYorkPortal extends BaseSocrataPortal
{
    public function __construct()
    {
        parent::__construct();
        $this->datasetsListUrl = 'https://nycopendata.socrata.com/browse?&page=';
    }
}
