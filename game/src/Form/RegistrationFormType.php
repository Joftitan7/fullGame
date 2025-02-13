<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType; // Import TextType for username, name, and familyName fields

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')  // This is already in your form
            ->add('username', TextType::class, [
                'label' => 'Username',
                'constraints' => [
                    new NotBlank(['message' => 'Username is required']),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your username should be at least {{ limit }} characters',
                        'max' => 50,
                    ]),
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
                'constraints' => [
                    new NotBlank(['message' => 'Name is required']),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your name should be at least {{ limit }} characters',
                        'max' => 50,
                    ]),
                ],
            ])
            ->add('familyName', TextType::class, [
                'label' => 'Family Name',
                'constraints' => [
                    new NotBlank(['message' => 'Family Name is required']),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your family name should be at least {{ limit }} characters',
                        'max' => 50,
                    ]),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone Number',
                'constraints' => [
                    new NotBlank(['message' => 'Phone number is required']),
                    new Regex([
                        'pattern' => '/^\+?\d{10,15}$/',
                        'message' => 'Enter a valid phone number',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
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
