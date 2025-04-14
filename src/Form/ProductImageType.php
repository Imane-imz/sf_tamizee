<?php

namespace App\Form;

use App\Entity\ProductImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Ne pas afficher à nouveau l'image, seulement un champ fichier facultatif
            ->add('imageFile', FileType::class, [
                'label' => 'Image supplémentaire',
                'required' => false,
                'mapped' => false, // on gère ça dans le contrôleur
            ])
            ->add('delete', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Supprimer cette image'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductImage::class,
        ]);
    }
}
