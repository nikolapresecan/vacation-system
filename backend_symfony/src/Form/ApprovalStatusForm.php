<?php

namespace App\Form;

use App\Entity\ApprovalStatus;
use App\Entity\Employee;
use App\Entity\Request;
use App\Entity\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApprovalStatusForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamLeaderApprovalDate')
            ->add('projectManagerApprovalDate')
            ->add('teamLeaderComment')
            ->add('projectManagerComment')
            ->add('request', EntityType::class, [
                'class' => Request::class,
                'choice_label' => 'id',
            ])
            ->add('teamLeader', EntityType::class, [
                'class' => Employee::class,
                'choice_label' => 'id',
            ])
            ->add('projectManager', EntityType::class, [
                'class' => Employee::class,
                'choice_label' => 'id',
            ])
            ->add('teamLeaderStatus', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'id',
            ])
            ->add('projectManagerStatus', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApprovalStatus::class,
        ]);
    }
}
