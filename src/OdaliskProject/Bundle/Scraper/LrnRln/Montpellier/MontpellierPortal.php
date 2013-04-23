<?php

namespace OdaliskProject\Bundle\Scraper\LrnRln\Montpellier;

use OdaliskProject\Bundle\Scraper\LrnRln\BaseLrnRlnPortal;

class MontpellierPortal extends BaseLrnRlnPortal
{
    public function __construct()
    {
        parent::__construct();
        $this->datasetsListUrl = 'http://opendata.montpelliernumerique.fr/Les-donnees/';
    }
}
