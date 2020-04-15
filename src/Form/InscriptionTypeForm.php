<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form;

use App\Validator\IsValidDNI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Description of InscriptionTypeForm.
 *
 * @author ibilbao
 */
class InscriptionTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dni', TextType::class, [
            'label' => 'inscription.dni',
            'constraints' => [
                new NotBlank(),
                new IsValidDNI(),
            ],
            'invalid_message' => '',
    ])
    ->add('nombre', TextType::class, [
        'label' => 'inscription.nombre',
        'constraints' => [
            new NotBlank(),
        ],
    ])
    ->add('apellido1', TextType::class, [
        'label' => 'inscription.apellido1',
        'constraints' => [
            new NotBlank(),
        ],
    ])
    ->add('apellido2', TextType::class, [
        'label' => 'inscription.apellido2',
        'constraints' => [
            new NotBlank(),
        ],
    ])
    ->add('email', TextType::class, [
        'label' => 'inscription.email',
        'constraints' => [
            new NotBlank(),
            new Email(),
        ],
        'invalid_message' => '',
    ])
    ->add('telefono', TextType::class, [
        'label' => 'inscription.telefono',
        'constraints' => [
        ],
    ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
    ]);
    }

//    public function validar_dni($dni)
//    {
//        $letra = substr($dni, -1);
//        $numeros = substr($dni, 0, -1);
//        if (substr('TRWAGMYFPDXBNJZSQVHLCKE', $numeros % 23, 1) == $letra && 1 == strlen($letra) && 8 == strlen($numeros)) {
//            return true;
//        } else {
//            return false;
//        }
//    }
//
//    public function is_valid_dni_nie($string)
//    {
//        if (9 != strlen($string) ||
//        1 !== preg_match('/^[XYZ]?([0-9]{7,8})([A-Z])$/i', $string, $matches)) {
//            return false;
//        }
//
//        $map = 'TRWAGMYFPDXBNJZSQVHLCKE';
//
//        list(, $number, $letter) = $matches;
//
//        return strtoupper($letter) === $map[((int) $number) % 23];
//    }
}
