<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/contact', name: 'contact.form')]
    public function form(): Response
    {
        $contact = new Contact();
        $type = ContactType::class;
        $form = $this->createForm($type, $contact);

        $form->handleRequest($this->requestStack->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            // insérer dans la base de données
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre message a bien été envoyé');
        }

        return $this->render('contact/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
