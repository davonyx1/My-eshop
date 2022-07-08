<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * 
     * Une fonction de controller appelle une action, pour rappelle name="nomdu controller _nomde l'action
     * 
     * @Route ("/", name="default_home", methods={"GET"})
     */
  public function home(EntityManagerInterface $entityManager): Response
  {

    
    return $this->render('default/home.html.twig', [
        'produits' =>$entityManager->getRepository(Produit::class)->findBy(['deletedAt' => null])
    ]);
  }
}
