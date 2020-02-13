<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ReceiptsFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'receiptsFile.ficheroCSV',
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
            ->add('receiptsType', ChoiceType::class, [
                'label' => 'receiptsFile.receiptsType',
                'choices' => [
                    'choice.recibos' => 'RB',
                    'choice.autoliquidaciones' => 'AU',
                    'choice.liquidaciones' => 'ID',
                ],
            ])
            ->add('receiptsFinishStatus', ChoiceType::class, [
                'label' => 'receiptsFile.receiptsFinishStatus',
                'choices' => [
                    'choice.pending' => 'P',
                    'choice.validated' => 'V',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}
