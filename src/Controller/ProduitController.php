<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProduitController extends AbstractController
{
    /**
     * @Route("/voir-les-produits", name="show_produits", methods={"GET"})
     */
    public function showProduits(EntityManagerInterface $entityManager): Response
    {
        # Grâce à l'entityManager, récupérez tous les produits et envoyez les à la vue twig : show_produits.html.twig
        return $this->render("admin/produits/show_produits.html.twig", [
            'produits' => $entityManager->getRepository(Produit::class)->findBy(["deletedAt" => null]),
            'archived_produits' => $entityManager->getRepository(Produit::class)->findBy(["deletedAt" => null])

        ]);
    }

    /**
     * @Route("/ajouter-un-produit", name="create_produit", methods={"GET|POST"})
     */
    public function createProduit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit();

        $form = $this->createForm(ProduitFormType::class, $produit)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $produit->setCreatedAt(new DateTime());
            $produit->setUpdatedAt(new DateTime());

            # Récupération du fichier dans le formulaire. Ce sera un objet de type UploadedFile.
            //** @var UploadedFile $photo */
            $photo = $form->get('photo')->getData();

            if ($photo) {
                // Méthode privée créée par nous-même pour réutiliser la partie du code identique aux actions create() et update()
                $this->handleFile($produit, $photo, $slugger);
            } # end if($photo)

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit est en ligne avec succès ! ');
            return $this->redirectToRoute('show_produits');
        } # end if($form)

        return $this->render("admin/form/form_produit.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/modifier-un-produit_{id}", name="update_produit", methods={"GET|POST"})
     */
    public function updateProduit(Produit $produit, EntityManagerInterface $entityManager, SluggerInterface $slugger, Request $request): Response
    {
        $originalPhoto = $produit->getPhoto();

        $form = $this->createForm(ProduitFormType::class, $produit, [
            'photo' => $originalPhoto
        ])->handleRequest($request);


       

        if($form->isSubmitted() && $form->isValid()) {

            $produit->setUpdatedAt(new DateTime());

            /** @var UploadedFile $photo */
            $photo = $form->get('photo')->getData();

            if ($photo){

               $this->handleFile($produit, $photo, $slugger);
            } else {
                $produit->setPhoto($originalPhoto);
            }

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit est en ligne avec succès ! ');
            return $this->redirectToRoute('show_produits');
        } # end if($form)

        return $this->render('admin/form/form_produit.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit
        ]);
    } # end function update()

    /**
     * @Route("/archiver-un-produit_{id}", name="soft_delete_produit", methods={"GET"})
     */
    public function softDeleteProduit(Produit $produit, EntityManagerInterface $entityManager): RedirectResponse
    {
        $produit->setDeletedAt(new DateTime());

        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash('success', "l'archivage du produit". $produit->getTitle()." a été effectué avec succès");
        return $this->redirectToRoute('show_produits');
    }




    /**
     * @Route("supprimer-un-produit_{id", name="hard_delete_produit", methods={"GET"})
     */
    public function hardDeleteProduit(Produit $produit, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($produit);
        $entityManager->flush();

        $this->addFlash("success", "le produit a bien été supprimé de la base");
        return $this->redirectToRoute("show_produits");
    }
        /**
    * @Route("/restaurer-un-produit_{id}", name="restore_produit", methods={"GET"})
    */
    public function restoreProduit(Produit $produit, EntityManagerInterface $entityManager): RedirectResponse
    {
        $produit->setDeletedAT(null);

        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash("success", "Le produit a bien été restauré en ligne");
        return $this->redirectToRoute("show_produits");
    }

    /////////////////////////////////////////////// METHODE PRIVEE ///////////////////////////////

    
    private function handleFile(Produit $produit, UploadedFile $photo, SluggerInterface $slugger)
    {
        # 1 Déconstruire le nom du fichier
        $extension = '.' . $photo->guessExtension();

        # 2 Variabiliser tous les éléments du nouveau nom de fichier après sécurisation
        # Le slug assainit le titre du produit (il retire les accents, les espaces, les majuscules)
        $safeFilename = $slugger->slug($produit->getTitle());

        # 3 Reconstruit du nom du fichier
        $newFilename = $safeFilename . '_' . uniqid() . $extension;

        # 4 Déplacement du fichier temporaire dans un dossier permanent dans notre projet.
        #try/catch s'utilise lorsqu'une méthode lance (throws) une erreur (Exception)
        try {
            $photo->move($this->getParameter('uploads_dir'), $newFilename);
            $produit->setPhoto($newFilename);
        } catch (FileException $exception) {
            $this->addFlash('warning', "Une erreur est survenue pendant l'upload de votre fichier :( Veuillez recommencer.");
            # On redirige le user et notre script s'arretera là si il rentre dans le catch()
           throw new Exception(" UNe erreur est survenue pendant l'upload de votre fichier:( veulliez recommencer.");
        }# end catch()
    }

} # end class