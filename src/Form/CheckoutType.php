<?php
// src/Form/CheckoutType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('first_name', TextType::class, [
            'label' => 'Prénom :',
            'attr' => ['class' => 'form-control'],
        ])
        ->add('last_name', TextType::class, [
            'label' => 'Nom de famille :',
            'attr' => ['class' => 'form-control'],
        ])
        ->add('email', EmailType::class, [
            'label' => 'Email :',
            'attr' => ['class' => 'form-control'],
        ])
        ->add('address', TextareaType::class, [
            'label' => 'Adresse :',
            'attr' => ['class' => 'form-control', 'rows' => '3'],
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Passer à la Commande',
            'attr' => ['class' => 'btn btn-primary btn-block btn-lg'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure data class or entity for the form
            // 'data_class' => 'App\Entity\Checkout',
        ]);
    }
}
