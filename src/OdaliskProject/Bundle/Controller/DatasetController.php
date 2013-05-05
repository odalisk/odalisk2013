<?php

namespace OdaliskProject\Bundle\Controller;

use OdaliskProject\Bundle\Controller\OdaliskController;

class DatasetController extends OdaliskController
{
    /**
     * index.
     *
     * @return array
     */
    public function indexAction($page_number, $_format)
    {
        // put action your code here


        return array(
            'name' => 'Julien Sanchez',
            'maintenance_status' => false,
        );
    }

    /**
     * details.
     *
     * @return array
     */
    public function detailsAction($dataset_number, $_format)
    {
        // put action your code here

        $repository = $this->getDoctrine()
            ->getRepository('OdaliskProject\Bundle\Entity\Dataset');
        $dataset = $repository->findById($dataset_number);
        
        // Undefined OFFSET
         $dataset = $dataset[0];

        return $this->render('OdaliskBundle:Dataset:details.html.twig', array(
            'maintenance_status' => false,
            'dataset' => $dataset));
    }

}
