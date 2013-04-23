<?php

namespace OdaliskProject\Bundle\Controller;

use OdaliskProject\Bundle\Controller\OdaliskController;
use OdaliskProject\Bundle\Entity\Contact;
use OdaliskProject\Bundle\Form\ContactType;

use Symfony\Component\HttpFoundation\Response;


class DefaultController extends OdaliskController
{
    /**
     * index.
     *
     * @return array
     */
    public function indexAction()
    {
        // put action your code here

		return $this->render('OdaliskBundle:Default:index.html.twig', array('maintenance_status' => false));

        /*return array(
            'name' => 'Julien Sanchez',
            'maintenance_status' => $this->container->getParameter('app.maintenance'),
        );*/
    }

    /**
     * deleteSearchPortal.
     */
    public function deleteSearchPortal($portal_id)
    {
        $session = $this->getRequest()->getSession();
        $session->set('search','');

        return $this->redirect($this->generateUrl('portal_details', array('portal_number' => $portal_id)));
    }

    /**
     * contact.
     */
    public function contactAction()
    {
        $previousUrl = $this->getRequest()->getRequestUri();
        $contact = new Contact();
        $contact->setCurrentPage($previousUrl);

        $form = $this->createForm(new ContactType(), $contact);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $contact = $form->getData();
                // Perform some action, such as sending an email
                $message = \Swift_Message::newInstance()
                        ->setSubject($contact->getSubject())
                        ->setFrom($contact->getEmail())
                        ->setTo('contact@odalisk.org')
                        ->setBody($contact->getBody()."\nMail de contact : ".$contact->getEmail()."\nNom d'utilisateur : ".$contact->getName()."\nPage d'origine : ".$contact->getCurrentPage())
                    ;

                $this->get('mailer')->send($message);
                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page
                error_log($contact->getCurrentPage());
                return $this->redirect($contact->getCurrentPage());
            }
        }

        return $this->render('OdaliskBundle:Default:contact.html.twig', array(
            'form' => $form->createView(),
            'maintenance_status' => false
        ));
    }

}
