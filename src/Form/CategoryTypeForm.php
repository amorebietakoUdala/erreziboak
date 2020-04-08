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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //	$roles = $options['roles'];
        $readonly = $options['readonly'];
        $builder->add('name', null, [
        'label' => 'activity.name',
        'disabled' => $readonly,
    ])
    ->add('nameEu', null, [
        'label' => 'activity.name_eu',
        'disabled' => $readonly,
    ])
    ->add('concept', EntityType::class, [
        'class' => Concept::class,
        'label' => 'activity.concept',
        'disabled' => $readonly,
    ]);
        if (!$readonly) {
            $builder->add('save', SubmitType::class, [
            'label' => 'btn.save',
        ]);
        }
        $builder->add('back', ButtonType::class, [
        'label' => 'btn.cancel',
    ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        'csrf_protection' => true,
        'data_class' => Category::class,
//	    'roles' => null,
        'readonly' => false,
    ]);
    }
}
