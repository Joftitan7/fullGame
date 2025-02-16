<?php

namespace App\Form;


use App\Entity\Game;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class GameFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('name', TextType::class, [
                'label' => 'Game Name',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('thumbnail', FileType::class, [
                'label' => 'Thumbnail (Image)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '2M', // Limit to 2MB
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, GIF, WEBP)',
                    ]),
                ],
            ])
            
            ->add('locationUrl', UrlType::class, [
                'label' => 'Game URL',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('visibility', ChoiceType::class, [
                'label' => 'Visibility',
                'choices' => [
                    'Public' => 'public',
                    'Private' => 'private',
                ],
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
