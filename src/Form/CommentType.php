<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommentType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Comment */
        $comment = $builder->getData();

        $builder
            ->add('content', TextareaType::class)
            ->add('authorApiUri', HiddenType::class, [
                'data' => $this->urlGenerator->generate(
                    'api_doctors_get_item',
                    ['id' => $comment->getAuthor()->getId()]
                ),
                'mapped' => false,
            ])
            ->add('careRequestApiUri', HiddenType::class, [
                'data' => $this->urlGenerator->generate(
                    'api_care_requests_get_item',
                    ['id' => $comment->getCareRequest()->getId()]
                ),
                'mapped' => false,
            ])
            ->add('apiAction', HiddenType::class, [
                'data' => $options['api_action'],
                'mapped' => false,
            ])
            ->add('apiUrl', HiddenType::class, [
                'data' => $options['api_url'],
                'mapped' => false,
            ])
            ->add('doctors', HiddenType::class, [
                'data' => json_encode($options['office_doctors']),
                'mapped' => false,
            ])
            ->add('upsert', SubmitType::class, [
                'label' => $comment->getId() ? 'comment.update_btn' : 'comment.add_btn'
            ])
        ;

        if ($comment->getId()) {
            // Le commentaire existe : ajout des champs hidden nécessaires
            $builder
                // permettre l'annulation de l'édition
                ->add('cancel', SubmitType::class, [
                    'label' => 'comment.cancel_btn'
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'api_action' => null,
            'api_url' => null,
            'office_doctors' => [],
        ]);

        $resolver->setAllowedTypes('api_action', 'string');
        $resolver->setAllowedTypes('api_url', 'string');
        $resolver->setAllowedTypes('office_doctors', 'array');
    }
}
