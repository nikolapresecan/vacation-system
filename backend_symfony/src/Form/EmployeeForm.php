<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\Job;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('birthDate')
            ->add('profilePicture')
            ->add('vacationDays')
            ->add('email')
            ->add('username')
            ->add('password')
            ->add('resetToken')
            ->add('tokenExpiry')
            ->add('job', EntityType::class, [
                'class' => Job::class,
                'choice_label' => 'id',
            ])
            ->add('roles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
}
