<?php

namespace App\Controller;

use App\Entity\ReceiptsFile;
use App\Form\ReceiptsFileType;
use App\Service\CsvFormatValidator;
use App\Service\FileUploader;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @Route("/receipts")
 * @IsGranted("ROLE_USER")
 */
class ReceiptsFileController extends AbstractController
{
    /**
     * @Route("/upload", name="receipts_file_upload")
     */
    public function upload(Request $request, FileUploader $fileUploader, CsvFormatValidator $validator, TranslatorInterface $translator, KernelInterface $kernel)
    {
        $form = $this->createForm(ReceiptsFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $receiptsFile */
            $data = $form->getData();
            $receiptsFile = $form['file']->getData();
            if ($receiptsFile) {
                $validationResult = $validator->validate($receiptsFile);
                if ($validationResult['status'] !== $validator::VALID) {
                    $this->__addValidationMessages($validationResult, $validator, $translator);

                    return $this->render('receipts_file/upload.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
                try {
                    $receiptsFileName = $fileUploader->upload($receiptsFile);
                    $data['receiptsFileName'] = $receiptsFileName;
                    $receiptsFileObject = ReceiptsFile::createReceiptsFile($data);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($receiptsFileObject);
                    $em->flush();
                    $this->addFlash('success', 'messages.successfullySended');

                    return $this->redirectToRoute('receipts_file_list');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('receipts_file/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/", name="receipts_file_list")
     */
    public function list()
    {
        $em = $this->getDoctrine()->getManager();
        $receiptsFiles = $em->getRepository(ReceiptsFile::class)->findBy([], [
            'receptionDate' => 'DESC',
        ]);

        return $this->render('receipts_file/list.html.twig', [
            'receiptsFiles' => $receiptsFiles,
        ]);
    }

    private function __addValidationMessages($validationResult, CsvFormatValidator $validator, TranslatorInterface $translator)
    {
        if ($validationResult['status'] === $validator::TOO_MUCH_FIELDS) {
            $this->addFlash('error',
                $translator->trans('too_much_fields',
                    [
                        '%invalid_headers%' => implode(',', $validationResult['invalid_headers']),
                    ],
                    'validators'
                )
            );
        }
        if ($validationResult['status'] === $validator::INCORRECT_FIELD_NAMES) {
            $this->addFlash('error',
                $translator->trans('incorrect_field_names',
                    [
                        '%invalid_headers%' => implode(',', $validationResult['invalid_headers']),
                    ],
                    'validators'
                )
            );
        }
        if ($validationResult['status'] === $validator::MISSING_VALUES_ON_REQUIRED_FIELDS) {
            $this->addFlash('error',
                $translator->trans('fields_with_missing_values',
                    [
                        '%fields%' => implode(',', $validationResult['fields_with_missing_values']),
                    ],
                    'validators'
                )
            );
        }
    }
}
