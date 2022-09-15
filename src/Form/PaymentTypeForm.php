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
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use App\Entity\Payment;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Description of PaymentTypeForm.
 *
 * @author ibilbao
 */
class PaymentTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $search = $options['search'];
        $readonly = $options['readonly'];
        if ($search) {
            $builder->add('date_from', DateTimeType::class, [
        'widget' => 'single_text',
        'html5' => false,
        'help' => 'YYYY/MM/DD HH:MM',
        'format' => 'yyyy/MM/dd HH:mm',
        'label' => 'payment.from',
            'disabled' => $readonly,
        ])
        ->add('date_to', DateTimeType::class, [
        'widget' => 'single_text',
        'html5' => false,
        'help' => 'YYYY/MM/DD HH:MM',
        'format' => 'yyyy/MM/dd HH:mm',
        'label' => 'payment.to',
            'disabled' => $readonly,
        ]);
        } else {
            $builder->add('timestamp', DateTimeType::class, [
        'widget' => 'single_text',
        'html5' => false,
        'format' => 'yyyy/MM/dd HH:mm',
        'attr' => ['class' => 'js-datepicker'],
        'label' => 'payment.timestamp',
            'disabled' => $readonly,
        ]);
        }
        $builder->add('referenceNumber', null, [
        'label' => 'payment.referenceNumber',
            'disabled' => $readonly,
        ])
        ->add('suffix', null, [
        'label' => 'payment.suffix',
            'disabled' => $readonly,
        ])
        ->add('status', ChoiceType::class, [
        'choices' => [
            'status.any' => null,
            'status.initialized' => Payment::PAYMENT_STATUS_INITIALIZED,
            'status.paid' => Payment::PAYMENT_STATUS_OK,
            'status.unpaid' => Payment::PAYMENT_STATUS_NOK,
        ],
        'label' => 'payment.status',
            'disabled' => $readonly,
        ])
        ->add('nif', null, [
        'label' => 'payment.nif',
            'disabled' => $readonly,
        ])
        ->add('email', null, [
        'label' => 'payment.email',
            'disabled' => $readonly,
        ]);
        if (!$search) {
            $builder->add('statusMessage', null, [
            'label' => 'payment.statusMessage',
            'disabled' => $readonly,
        ])
        ->add('name', null, [
            'label' => 'payment.name',
            'disabled' => $readonly,
        ])
        ->add('surname1', null, [
            'label' => 'payment.surname1',
            'disabled' => $readonly,
        ])
        ->add('surname2', null, [
            'label' => 'payment.surname2',
            'disabled' => $readonly,
        ])
        ->add('nif', null, [
            'label' => 'payment.nif',
            'disabled' => $readonly,
        ])
        ->add('phone', null, [
            'label' => 'payment.phone',
            'disabled' => $readonly,
        ])
        ->add('registeredPaymentId', null, [
            'label' => 'payment.registeredPaymentId',
            'disabled' => $readonly,
        ])
        ->add('operationNumber', null, [
            'label' => 'payment.operationNumber',
            'disabled' => $readonly,
        ]);
        }
        if ($search) {
            $builder->add('search', SubmitType::class, [
        'label' => 'btn.search',
            'disabled' => $readonly,
        ]);
        } else {
            $builder->add('back', ButtonType::class, [
        'label' => 'btn.cancel',
        ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        'csrf_protection' => true,
        'data_class' => null,
        'readonly' => null,
        'search' => true,
    ]);
    }
}
