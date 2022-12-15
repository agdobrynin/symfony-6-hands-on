<?php

namespace App\Form;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class ProfileUploadImageType extends AbstractType
{
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxSize = $this->parameterBag->get('micro_post.profile_image.max_size');

        $builder
            ->add('avatar', FileType::class, [
                'label' => 'Choose an image to upload for avatar',
                'help' => 'Support format JPG or PNG',
                'mapped' => false,
                'constraints' => [
                    new File(
                        maxSize: $maxSize,
                        mimeTypes: ['image/jpeg', 'image/png'],
                        mimeTypesMessage: 'Please choose valid image file types: jpg, png'
                    ),
                    new NotNull(
                        message: 'Choose an image to upload for avatar'
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
