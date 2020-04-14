<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form;

use App\Entity\GTWIN\Recibo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of ReceiptSearchForm.
 *
 * @author ibilbao
 */
class ReceiptSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dni', null, [
                    'label' => 'receipt.dni',
                    ]);
        $builder->add('numeroRecibo', null, [
                    'label' => 'receipt.numeroRecibo',
                    ]);
        $builder->add('email', null, [
                    'label' => 'receipt.email',
                    ]);
        $builder->add('search', SubmitType::class, [
            'label' => 'receipt.search',
                ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        'csrf_protection' => true,
        'data_class' => Recibo::class,
        'roles' => [],
    ]);
    }
}
