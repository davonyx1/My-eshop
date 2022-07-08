<?php

namespace App\Form;

use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

#il ne faut jamais IMPORTER la class que vous définissez !!!!
class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'constraints' => [
                    new Email([
                        'message' => "Votre email n'est pas au bon format : mail@exemple.fr"
                    ]),
                    new NotBlank([
                        'message' => 'ce champs ne peut être vide'
                    ]),
                    new Length([
                        'min' => 4,
                        'max' => 180,
                        'minMessage' => "Votre email doit comporter {{ limit }} caractères minimum.",
                        'maxMessage' => "Votre email doit comporter {{ limit }} caractères maximum."
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'constraints' => [
                    new NotBlank([
                        'message' => " Ce champs ne peut pas être vide"
                    ]),
                    new Length([
                        'min' => 8,
                        'max' =>255,
                        'minMessage' => 'Votre mot de passe doit comporter {{ limit }} caratères minimum.',
                        'maxMessage' => 'Votre mot de passe doit comporter {{ limit }} caractère maximum'
                    ]),
                ],
                'help' => "* min caractères : 8
                           * max caractères : 255
                           * au moins 1 caractère spécial,
                           * au moins une majuscule,
                           * au moins 1 minuscule
                           * au moins 1 chiffre"
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champs ne peut pas être vide'
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champs ne peut pas être vide'
                    ]),
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Sexe',
                'expanded' => true,
                'choices' => [
                    "Homme" => 'homme',
                    "Femme" => 'femme',
                ],
                'choice_attr' => [
                    "Homme" =>['selected' => true]
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champs ne peut pas être vide'
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
