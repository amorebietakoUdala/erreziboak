<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form;

use App\Entity\GTWIN\Recibo;
use App\Validator\IsValidDNI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Description of ReceiptSearchForm.
 *
 * @author ibilbao
 */
class ReceiptSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('referenciaC60', TextType::class, [
            'label' => 'receipt.referenciaC60',
        ]);
        $builder->add('email', TextType::class, [
            'label' => 'receipt.email',
            'required' => false,
            'constraints' => [
                new Email(),
            ],
        ]);
        $builder->add('search', SubmitType::class, [
            'label' => 'receipt.search',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'roles' => null,
            'csrf_protection' => true,
            'data_class' => null,
        ]);
    }
}
