<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    /**
     * pour l'enregistrement d'un nouvel utilisateur nous ne pouvons insérer le mot de passe en clair en BDD.
     * pour cela, Symf nous fournit un outil pour hasher (encrypter) le password.
     * Pour l'utiliser nous devons juste l'injecter comme dépendance (de notre fonction.)
     * Linjection de dépendance se fait entre les parenthèses de la fonction.
     * 
     * @Route("inscription", name="user_register", methods={"GET|POST"})
     */
    public function register(Request $request, EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher): Response
    {   #on crée une new instance de notre objet user
        $user = new User();

        $form = $this->createForm(UserFormType::class, $user)
            ->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                #nous settons les propriétés qui ne sont pas dans le form et donc auto hydratées
                # les propri createdAt et updatedAt attendent un obj de type Datetime().
               $user->setCreatedAt(new DateTime());
               $user->setUpdatedAt( new DateTime());
               #Pour assurer un rôle utili à tous les utili, on set le role egalement
               $user->setRoles(['ROLE_USER']);

               #on récupère la valeur de l'unput 'password' dans le formulaire
                $plainPassword = $form->get('password')->getData();

                # on reset le password de user en le hachant
                # pour hasher on utilise le hashage qu'on a injecté dans notre action.(la fontion au dessus)
               $user->setPassword(
                $passwordHasher->hashPassword(
                    $user, $plainPassword
             )
            );
            #notre user est correctement setter, on peut envoyer en BDD
            $entityManager->persist($user);
            $entityManager->flush();

            # grace à la methode addFlash() vous pouvez stocker des messages dans la session destinés à etre affichés en front
            #                  label          message
            $this->addFlash('success','vous êtes inscrit avec succès !');

            #on peut enfin return et rediriger l'utilisateur là ou on le souhaite
            return $this->redirectToRoute('app_login');
            
        }#end if
        
        #on rend la vue qui contient le formulaire d'inscription
         return $this->render("user/register.html.twig", [
                'form_register' => $form->createView()
            ]);
    }#end action register()
}#end class
