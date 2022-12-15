<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileUploadImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avatar', FileType::class, [
                'label' => 'Choose an image to upload for avatar',
                'help' => 'Support format JPG or PNG',
                'mapped' => false,
                'constraints' => [
                    new File(
                        maxSize: 1048576,
                        mimeTypes: ['image/jpeg', 'image/png'],
                        mimeTypesMessage: 'Please choose valid image file types: jpg, png'
                    )
                ]
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
