<?php

namespace OdaliskProject\Bundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class RdfController extends Controller
{


    public function dcatAction($idMongo)
    {
        //We look for the mongdb document manager and for the file we want to send
        $mana = $this->container->get('doctrine.odm.mongodb.document_manager')->getRepository('OdaliskProject\Bundle\Document\DcatDataset');
        $file = $mana->find($idMongo);

        //And then goes the response construction
        $response = new Response();
        $response->setContent(json_decode($file->getFile()->getBytes(),true));
        $response->headers->set('Content-type', 'application/rdf');
        $filename = $idMongo.".rdf";
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;

    }



    public function downloadArchiveAction($portal_name)
    {
        //We look for the mongdb document manager and for the file we want to send
        $mana = $this->container->get('doctrine.odm.mongodb.document_manager')->getRepository('OdaliskProject\Bundle\Document\DcatArchive');
        $file = $mana->findOneBy(array('archiveName'=>$portal_name));

        //And then goes the response construction
        $response = new Response();
        $response->setContent($file->getFile()->getBytes());
        $response->headers->set('Content-type', 'application/zip');
        $filename = $file->getArchiveName().'.zip';
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);
        
        return $response;

    }
}
