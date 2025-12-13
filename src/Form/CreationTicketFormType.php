<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CreationTicketFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Messagerie' => 'Messagerie',
                    'Serveur' => 'Serveur',
                    'Dolibarr' => 'Dolibarr',
                    'Internet' => 'Internet',
                    'Imprimante' => 'Imprimante',
                    'Logiciel' => 'Logiciel',
                    'Matériel' => 'Matériel',
                    'Autre' => 'Autre',
                ],
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => [
                    'Basse' => 'Basse',
                    'Haute' => 'Haute',
                ],
            ])
            ->add('title')
            ->add('description')
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'label' => 'Image (JPEG ou PNG, max 1 Mo)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
