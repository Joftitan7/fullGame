<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class GameUploadFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

    ->add('visibility', ChoiceType::class, [
        'label' => 'Visibility',
        'choices' => [
            'Public' => 'public',
            'Private' => 'private',
        ],
        'expanded' => true,
        'multiple' => false,
    ])

        ->add('name', TextType::class, [
            'label' => 'Game Name',
            'required' => true,  // Make sure the name is required
            'attr' => ['class' => 'form-control'],
        ])

        ->add('locationUrl', TextType::class, [  // Add the 'location_url' field here
            'label' => 'Game URL',
            'required' => true, // Make sure the URL is required
            'attr' => ['class' => 'form-control'],
        ])

            ->add('gameFile', FileType::class, [
                'label' => 'Game File (HTML/JS)',

                // Add validation constraints to ensure file is an archive
                'constraints' => [
                    new File([
                        'mimeTypes' => ['application/zip', 'application/x-zip-compressed'],
                        'mimeTypesMessage' => 'Please upload a valid ZIP file.',
                    ])
                ]
            ])
            ->add('thumbnail', FileType::class, [
                'label' => 'Thumbnail Image (Optional)',
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG/PNG).',
                    ])
                ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Upload Game']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
