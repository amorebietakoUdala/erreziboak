<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form;

use App\Entity\Concept;
use App\Entity\GTWIN\Institucion;
use App\Entity\GTWIN\TipoIngreso;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of ConceptTypeForm.
 *
 * @author ibilbao
 */
class ConceptTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //	$roles = $options['roles'];
        $readonly = $options['readonly'];
        $builder->add('name', null, [
        'label' => 'concept.name',
        'disabled' => $readonly,
    ])
    ->add('nameEu', null, [
        'label' => 'concept.name_eu',
        'disabled' => $readonly,
    ])
    ->add('unitaryPrice', null, [
        'label' => 'concept.unitaryPrice',
        'disabled' => $readonly,
    ])
    ->add('entity', EntityType::class, [
        'class' => Institucion::class,
        'em' => 'oracle',
        'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('u')
                ->orderBy('u.codigo', 'ASC');
        },
        'choice_label' => 'nombre',
        'choice_value' => 'codigo',
        'label' => 'concept.entity',
        'disabled' => $readonly,
    ])
    ->add('suffix', EntityType::class, [
        'class' => TipoIngreso::class,
        'em' => 'oracle',
        'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('u')
                ->orderBy('u.codigo', 'ASC');
        },
        'choice_label' => function ($tipoIngreso) {
            return $tipoIngreso->getCodigo().'-'.$tipoIngreso->getDescripcion();
        },
        'choice_value' => 'codigo',
        'label' => 'concept.suffix',
        'disabled' => $readonly,
    ])
    ->add('accountingConcept', EntityType::class, [
        'class' => \App\Entity\GTWIN\ConceptoContable::class,
        'em' => 'oracle',
        'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('u')
                ->orderBy('u.codigo', 'ASC');
        },
        'choice_label' => function ($tipoIngreso) {
            return $tipoIngreso->getCodigo().'-'.$tipoIngreso->getDescripcion();
        },
        'choice_value' => 'codigo',
        'label' => 'concept.accountingConcept',
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
        'data_class' => Concept::class,
//	    'roles' => null,
        'readonly' => false,
    ]);
    }
}
