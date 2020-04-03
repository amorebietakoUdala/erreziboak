<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form;

use App\Entity\Category;
use App\Entity\ExamInscription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of InscriptionTypeForm.
 *
 * @author ibilbao
 */
class ExamInscriptionTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $readonly = $options['readonly'];
        $locale = $options['locale'];
        $builder->add('inscription', \App\Form\InscriptionTypeForm::class, [
            'data_class' => ExamInscription::class,
        ])
        ->add('category', EntityType::class, [
        'class' => Category::class,
        'label' => 'exam.category',
        'choice_label' => function ($category) use ($locale) {
            if ('es' === $locale) {
                return $category->getName();
            } else {
                return $category->getNameEu();
            }
        },
    ]);
        if (!$readonly) {
            $builder->add('pay', SubmitType::class, [
        'label' => 'btn.pay',
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
        'data_class' => ExamInscription::class,
        'readonly' => false,
        'locale' => 'es',
    ]);
    }
}
