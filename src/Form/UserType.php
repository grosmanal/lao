<?php

namespace App\Form;

use App\Entity\Doctor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'required' => true,
                'label' => 'user.form.firstname.label',
                'attr' => [
                    'placeholder' => 'user.form.firstname.placeholder',
                ],
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'label' => 'user.form.lastname.label',
                'attr' => [
                    'placeholder' => 'user.form.lastname.placeholder',
                ],
            ])
            ->add('email', TextType::class, [
                'required' => true,
                'label' => 'user.form.email.label',
                'attr' => [
                    'placeholder' => 'user.form.email.placeholder',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => false,
                'invalid_message' => 'user.error.non_matching_password',
                'first_options' => [
                    'label' => 'user.form.password.label',
                    'attr' => [
                        'placeholder' => 'user.form.password.placeholder',
                    ],
                ],
                'second_options' => [
                    'label' => 'user.form.confirm_password.label',
                    'attr' => [
                        'placeholder' => 'user.form.confirm_password.placeholder',
                    ],
                ],
                'constraints' => [
                    new RollerworksPassword\PasswordStrength(['minLength' => 7, 'minStrength' => 3]),
                ],
            ])
            ->add('avatarFile', VichImageType::class, [
                'label' => 'user.form.avatar',
                'required' => false,
                'download_uri' => false,
            ])
            ->add('update', SubmitType::class, [
                'label' => 'user.form.update',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Doctor::class,
        ]);
    }
}
