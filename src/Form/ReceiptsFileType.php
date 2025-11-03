<?php

namespace App\Form;

use App\Entity\ReceiptsFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ReceiptsFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ->add('description', null, [
                'label' => 'receiptsFile.description',
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
#                    'choice.payd' => 'C',
                ],
            ])
            ->add('incomeType', null, [
                'label' => 'receiptsFile.incomeType',
                'help' => 'receiptsFile.help.incomeType',
            ])
            ->add('tributeCode', null, [
                'label' => 'receiptsFile.tributeCode',
                'help' => 'receiptsFile.help.tributeCode',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
