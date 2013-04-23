<?php

namespace OdaliskProject\Bundle\Controller;

use OdaliskProject\Bundle\Controller\OdaliskController;

class BrowserController extends OdaliskController
{
    /**
     * index.
     *
     * @return array
     */
    public function indexAction($_format)
    {
        $portals = $this->getDoctrine()
            ->getRepository('OdaliskProject\Bundle\Entity\Portal')
            ->findAll();

        return $this->render('OdaliskBundle:Browser:index.html.twig', array(
            'maintenance_status' => false,
            'portals' => $portals)
        );
    }
}
