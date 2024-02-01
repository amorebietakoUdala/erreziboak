<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Category;
use App\Entity\Concept;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Description of ConceptTypeForm.
 *
 * @author ibilbao
 */
class CategoryTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $readonly = $options['readonly'];
        $builder->add('name', null, [
            'label' => 'category.name',
            'disabled' => $readonly,
        ])
        ->add('nameEu', null, [
            'label' => 'category.name_eu',
            'disabled' => $readonly,
        ])
        ->add('concept', EntityType::class, [
            'class' => Concept::class,
            'label' => 'category.concept',
            'disabled' => $readonly,
        ]);
        if (!$readonly) {
            $builder->add('save', SubmitType::class, [
                'label' => 'btn.save',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        'csrf_protection' => true,
        'data_class' => Category::class,
//	    'roles' => null,
        'readonly' => false,
    ]);
    }
}
