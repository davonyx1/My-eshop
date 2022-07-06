<?php

namespace App\Controller;


use DateTime;
use App\Entity\Produit;
use App\Form\ProduitFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProduitController extends AbstractController
{
    /**
     * @Route("/voir-les-produits", name="show_produits", methods={"GET"})
     */
    public function showProduits(EntityManagerInterface $entityManager): Response
    { 
        #grace à l'entitymanager, récupérez tous les produits et envoyez les à la vue twig : show_produits.html.twig
        return $this->render("admin/produits/show_produits.html.twig", [
            'produits' => $entityManager->getRepository(Produit::class)->findAll()
        ]);
       

    }

    /**
     * 
     * @Route("/ajouter-un-produit", name="create_produit", methods={"GET|POST"})
     */
    public function createProduit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit();

        $form = $this->createForm(ProduitFormType::class, $produit)
        ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {

            $produit->setCreatedAt(new DateTime());
            $produit->setUpdatedAt(new DateTime());

                /**@var UploadedFile $photo */
            $photo = $form->get('photo')->getData();

            #1 déconstruire le nom du fichier
            # 2 variabiliser tous les éléments du noueau nom de fichier après sécurisation
            # 3 Reconstruit du nom du fichier
            # 4 Déplacement du fichier temporaire dans un dossier permanent dans notre projet.

         }   
            return $this->render("admin/form/form_produit.html.twig",[
                'form' => $form->createView()
              ]);
          
        

    }
}
