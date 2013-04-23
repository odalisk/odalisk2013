<?php

namespace OdaliskProject\Bundle\Controller;

use OdaliskProject\Bundle\Controller\OdaliskController;

class PortalController extends OdaliskController
{
    private static $page_size = 20;

    /**
     * index.
     *
     * @return array
     */
    public function indexAction($page_number, $_format)
    {
        // put action your code here
        $page_from = self::$page_size * ($page_number - 1);
        $page_to = self::$page_size * $page_number;
        $end = false;

        $repository = $this->getDoctrine()
            ->getRepository('OdaliskProject\Bundle\Entity\Portal');
        $portals = $repository->findAll();

        $end = (count($portals) < self::$page_size) ? true : false;

        return $this->render('OdaliskBundle:Portal:index.html.twig', array(
            'maintenance_status' => false,
            'page_number' => $page_number,
            'portals' => $portals,
            'page_number' => $page_number,
            'page_from' => $page_from,
            'page_to' => $page_to,
            'end' => $end));
    }

    /**
     * details.
     *
     * @return array
     */
    public function detailsAction($portal_number)
    {
        // put action your code here
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('OdaliskProject\Bundle\Entity\Portal');
        $portal = $repository->findOneById($portal_number);
        $formats = $repository->getFormatDistribution($portal);
        $licenses = $repository->getLicenseDistribution($portal);
        $categories = $repository->getCategoryDistribution($portal);
        
        return $this->render('OdaliskBundle:Portal:details.html.twig', array(
            'maintenance_status' => false,
            'portal' => $portal,
            'formats' => $formats,
            'licenses' => $licenses,
            'categories' => $categories));
    }

    public function getPortalListAction()
    {

    }
}
