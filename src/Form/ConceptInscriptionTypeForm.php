<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form;

use App\Entity\Category;
use App\Entity\Concept;
use App\Entity\ConceptInscription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\InscriptionTypeForm;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of InscriptionTypeForm.
 *
 * @author ibilbao
 */
class ConceptInscriptionTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $readonly = $options['readonly'];
        $locale = $options['locale'];
        $builder->add('inscription', InscriptionTypeForm::class, [
            'data_class' => ConceptInscription::class,
        ])
        ->add('concept', EntityType::class, [
            'class' => Concept::class,
            'label' => 'conceptInscripcion.concept',
            'choice_label' => function ($concept) use ($locale) {
                if ('es' === $locale) {
                    return $concept->getName();
                } else {
                    return $concept->getNameEu();
                }
            },
        ])
        ->add('price', null, [
            'label' => 'conceptInscription.price',
        ])
        ->add('externalReference', null, [
            'label' => 'conceptInscription.externalReference',
        ])
        ;
        // if (!$readonly) {
        //     $builder->add('pay', SubmitType::class, [
        // 'label' => 'btn.pay',
        // ]);
        // }
        // $builder->add('back', ButtonType::class, [
        //     'label' => 'btn.cancel',
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        'csrf_protection' => true,
        'data_class' => ConceptInscription::class,
        'readonly' => false,
        'locale' => 'es',
    ]);
    }
}
