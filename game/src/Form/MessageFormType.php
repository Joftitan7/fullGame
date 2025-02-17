<?php

// src/Form/MessageFormType.php
// src/Form/MessageFormType.php
namespace App\Form;

use App\Entity\User;
use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MessageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Get the logged-in user
        $user = $options['user'];

        // Fetch user's friends
        $friends = $user->getFriends();

        // Create a dropdown for friends
        $builder->add('receiver', ChoiceType::class, [
            'choices' => $friends,
            'choice_label' => function (User $user) {
                return $user->getUsername();
            },
            'label' => 'Choose a Friend',
        ]);

        // Add content field
        $builder->add('content', TextareaType::class, [
            'label' => 'Message Content',
            'attr' => ['placeholder' => 'Enter your message here...'],
        ]);

        // Add parent_message field if it's provided
        if ($options['parent_message']) {
            $builder->add('parentMessage', HiddenType::class, [
                'data' => $options['parent_message'], // Passing the parent message to the form
            ]);
        }

        // Add submit button
        $builder->add('send', SubmitType::class, [
            'label' => 'Send Message',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'user' => null,  // Passing logged-in user
            'parent_message' => null,  // Define parent_message option
        ]);
    }
}
