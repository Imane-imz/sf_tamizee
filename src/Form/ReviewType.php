<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', HiddenType::class, [
                'required' => true,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer l\'avis',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }
}
