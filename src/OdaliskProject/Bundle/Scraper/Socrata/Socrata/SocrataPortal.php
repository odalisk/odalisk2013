<?php

namespace OdaliskProject\Bundle\Scraper\Socrata\Socrata;

use OdaliskProject\Bundle\Scraper\Socrata\BaseSocrataPortal;

class SocrataPortal extends BaseSocrataPortal
{
    public function __construct()
    {
        parent::__construct();
        $this->datasetsListUrl = 'https://opendata.socrata.com/browse?&page=';
    }
}
