<?php

namespace App\Form;

use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SicalwinFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'sicalwinFile.ficheroCSV',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '4096k',
                        'mimeTypes' => [
                            'text/plain',
                            'application/vnd.ms-excel',
                        ],
                        'mimeTypesMessage' => 'Select a valid csv file.',
                    ]),
                ],
            ])
            ->add('codigoConvocatoria', TextType::class,[
                'label' => 'sicalwinFile.codigoConvocatoria',
                'required' => true,
            ])
            ->add('discriminadorConcesion', TextType::class,[
                'label' => 'sicalwinFile.discriminadorConcesion',
                'required' => true,
            ])
            ->add('fechaConcesion', TextType::class,[
                'label' => 'sicalwinFile.fechaConcesion',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
