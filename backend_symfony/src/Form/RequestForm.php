<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\Request;
use App\Entity\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numberOfDays')
            ->add('startDate')
            ->add('endDate')
            ->add('comment')
            ->add('createdDate')
            ->add('employee', EntityType::class, [
                'class' => Employee::class,
                'choice_label' => 'id',
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Request::class,
        ]);
    }
}
