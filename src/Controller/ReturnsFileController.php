<?php

namespace App\Controller;

use App\Entity\ReturnsFile;
use App\Form\ReturnsFileType;
use App\Service\CsvFormatValidator;
use App\Service\FileUploader;
use Exception;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale}/returns")
 */
class ReturnsFileController extends AbstractController
{
    /**
     * @Route("/", name="returns_file_list")
     */
    public function list()
    {
        $returnsFiles = $this->getDoctrine()->getRepository(ReturnsFile::class)->findBy([], [
            'receptionDate' => 'DESC',
        ]);

        return $this->render('returns_files/list.html.twig', [
            'returnsFiles' => $returnsFiles,
        ]);
    }

    /**
     * @Route("/upload", name="returns_file_upload")
     */
    public function upload(Request $request, CsvFormatValidator $validator, TranslatorInterface $translator, Swift_Mailer $mailer, \App\Service\C34XmlGenerator $generator)
    {
        $form = $this->createForm(ReturnsFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $returnsFile = $form['file']->getData();
            if ($returnsFile) {
                $validationResult = $validator->validate($returnsFile);
                if ($validationResult['status'] !== $validator::VALID) {
                    $this->__addValidationMessages($validationResult, $validator, $translator);

                    return $this->render('returns_files/upload.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
                try {
                    $fileUploader = new FileUploader($this->getParameter('returns_file_upload_directory'));
                    $returnsFileName = $fileUploader->upload($returnsFile);
                    $data['returnsFileName'] = $returnsFileName;
                    $returnsFileObject = ReturnsFile::createReturnsFile($data);

                    $totalAmount = $this->processReturnsFile($this->getParameter('returns_file_upload_directory'), $returnsFileObject, $generator);
                    $returnsFileObject->setProcessedDate(new \DateTime());
                    $returnsFileObject->setStatus(ReturnsFile::STATUS_PROCESSED);
                    $returnsFileObject->setTotalAmount($totalAmount);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($returnsFileObject);
                    $em->flush();
                    $this->addFlash('success', 'messages.successfullySended');

                    if (true === $this->getParameter('send_returns_file_messages')) {
                        $this->__sendMail($returnsFileObject, $mailer);
                    }

                    return $this->redirectToRoute('returns_file_list');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('returns_files/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{returnsFile}/download", name="returns_file_download")
     */
    public function download(ReturnsFile $returnsFile)
    {
        $without_extension = pathinfo($returnsFile->getFileName(), PATHINFO_FILENAME);
        $fileName = $this->getParameter('returns_file_upload_directory').'/'.$without_extension.'.zip';
        $response = new BinaryFileResponse($fileName);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $without_extension.'.zip'
        );

        return $response;
    }

    private function processReturnsFile(string $path, ReturnsFile $returnsFile, \App\Service\C34XmlGenerator $generator)
    {
        $totalAmount = $generator->createFileFrom($path, $returnsFile);

        return $totalAmount;
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
        if ($validationResult['status'] === $validator::IMPORTE_NOT_NUMBERIC) {
            $this->addFlash('error',
                $translator->trans('importe_not_numeric', [
                    '%invalid_row%' => $validationResult['invalid_row'],
                    '%invalid_value%' => $validationResult['invalid_value'],
                ], 'validators')
            );
        }
        if ($validationResult['status'] === $validator::INVALID_DATE) {
            $this->addFlash('error',
                $translator->trans('invalid_date', [
                    '%invalid_row%' => $validationResult['invalid_row'],
                    '%invalid_value%' => $validationResult['invalid_value'],
                ], 'validators')
            );
        }
        if ($validationResult['status'] === $validator::INVALID_BANK_ACCOUNT) {
            $this->addFlash('error',
                $translator->trans('invalid_bank_account', [
                    '%invalid_row%' => $validationResult['invalid_row'],
                    '%invalid_value%' => $validationResult['invalid_value'],
                ], 'validators')
            );
        }
    }

    public function __sendMail(ReturnsFile $returnsFile, \Swift_Mailer $mailer)
    {
        $sent_from = $this->getParameter('mailer_user');
        $sent_to = $this->getParameter('delivery_addresses');
        $message = (new Swift_Message('Conversión de fichero de devoluciones'))
        ->setFrom($sent_from)
        ->setTo($sent_to)
        ->setBody(
            $this->renderView(
                'returns_files/mail.html.twig',
                ['returnsFile' => $returnsFile]
            ),
            'text/html'
        );

        $mailer->send($message);

        return $this->render(
            'returns_files/mail.html.twig',
            ['returnsFile' => $returnsFile])
        ;
    }
}
