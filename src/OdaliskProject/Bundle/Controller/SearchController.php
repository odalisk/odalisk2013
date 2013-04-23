<?php

namespace OdaliskProject\Bundle\Controller;

use OdaliskProject\Bundle\Controller\OdaliskController;

class SearchController extends OdaliskController
{
    /**
     * search.
     *
     * @return array
     */
    public function searchAction()
    {
        return $this->render('OdaliskBundle:Search:search.html.twig', array(
            'maintenance_status' => false));
    }
}
