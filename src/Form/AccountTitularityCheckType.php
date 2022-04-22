<?php

namespace App\Form;

use App\Entity\AccountTitularityCheck;
use App\Validator\IsValidIBAN;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AccountTitularityCheckType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idNumber',null, [
                'label' => 'accountTitularityCheck.idNumber',
            ])
            ->add('type',ChoiceType::class, [
                'label' => 'accountTitularityCheck.type',
                'choices' => [
                    'choice.iban' => 'IBAN',
                    'choice.cc' => 'CC',
                ]
            ])
            ->add('accountNumber', null, [
                'label' => 'accountTitularityCheck.accountNumber',
                'constraints' => [
//                    new IsValidIBAN(),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AccountTitularityCheck::class,
        ]);
    }
}
