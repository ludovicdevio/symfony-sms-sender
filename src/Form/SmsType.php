<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class SmsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TelType::class, [
                'label' => 'Numéro de téléphone :',
                'required' => true,
                'attr' => [
                    'placeholder' => '+33 6 12 34 56 78',
                    'pattern' => '[0-9+\s]+',
                ],
                'help' => 'Format international recommandé (+33...)',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un numéro de téléphone']),
                    new Regex([
                        'pattern' => '/^[0-9+\s]+$/',
                        'message' => 'Le numéro de téléphone ne peut contenir que des chiffres, le signe + et des espaces',
                    ]),
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'expéditeur :',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un nom d\'expéditeur']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Message :',
                'required' => true,
                'attr' => [
                    'rows' => 4,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un message']),
                    new Length([
                        'max' => 160,
                        'maxMessage' => 'Le message ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ]);
    }
}
