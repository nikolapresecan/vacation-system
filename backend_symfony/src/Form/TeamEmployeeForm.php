<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\Team;
use App\Entity\TeamEmployee;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamEmployeeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('team', EntityType::class, [
                'class' => Team::class,
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
            ->add('employees', EntityType::class, [
                'class' => Employee::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TeamEmployee::class,
        ]);
    }
}
