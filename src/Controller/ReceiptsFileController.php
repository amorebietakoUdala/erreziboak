<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\ReceiptsFile;
use App\Form\GestionaReceiptsFileType;
use App\Form\ReceiptsFileType;
use App\Repository\ReceiptsFileRepository;
use App\Service\CsvFormatValidator;
use App\Service\FileUploader;
use App\Service\GestionaFileConverter;
use App\Service\GestionaValidator;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Exception;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/{_locale}/receipts_file', requirements: ['_locale' => 'es|eu|en'])]
#[IsGranted('ROLE_RECEIPTS')]
class ReceiptsFileController extends BaseController
{

    public function __construct(
        private readonly ReceiptsFileRepository $receiptFileRepo, 
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $em,
        private readonly string $targetDirectory)
    {
    }

    #[Route(path: '/gestiona/upload', name: 'receipts_file_gestiona_upload')]
    public function gestionaUpload(Request $request, FileUploader $fileUploader, GestionaValidator $validator)
    {
        $this->loadQueryParameters($request);
        $form = $this->createForm(GestionaReceiptsFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $data = $form->getData();
            $file = $form['file']->getData();
            if (null === $file) {
                $this->addFlash('error', 'messages.fileNotSelected');

                return $this->render('receipts_file/gestiona-upload.html.twig', [
                    'form' => $form,
                ]);
            }
            $validationResult = $validator->validate($file, $data['receiptsFinishStatus']);
            if ($validationResult['status'] !== $validator::VALID) {
                $this->addFlash('error', $validationResult['message']);

                return $this->render('receipts_file/gestiona-upload.html.twig', [
                    'form' => $form,
                ]);
            }
            try {
                $converter = new GestionaFileConverter('AMOREBIE', $data['incomeType'], $data['tributeCode']);
                $receiptsFileName = $fileUploader->upload($file, true);
                $data['receiptsFileName'] = $receiptsFileName;
                $receiptsFileObject = ReceiptsFile::createReceiptsFile($data);
                $outputFile = $converter->convert($this->targetDirectory.'/'.$receiptsFileName);
                $receiptsFileObject->setFileName($outputFile);
                $receiptsFileObject->setUploadedBy($this->getUser());
                $this->em->persist($receiptsFileObject);
                $this->em->flush();
                $this->addFlash('success', 'messages.successfullySended');

                if (true === $this->getParameter('send_receiptfile_messages')) {
                    $this->sendMail($receiptsFileObject);
                }

                return $this->redirectToRoute('receipts_file_index');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('receipts_file/gestiona-upload.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/upload', name: 'receipts_file_upload')]
    public function upload(Request $request, FileUploader $fileUploader, CsvFormatValidator $validator, EntityManagerInterface $em)
    {
        $this->loadQueryParameters($request);
        $form = $this->createForm(ReceiptsFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $data = $form->getData();
            $file = $form['file']->getData();
            if (null === $file) {
                $this->addFlash('error', 'messages.fileNotSelected');

                return $this->render('receipts_file/upload.html.twig', [
                    'form' => $form,
                ]);
            }
            $validationResult = $validator->validate($file, $data['receiptsFinishStatus']);
            if ($validationResult['status'] !== $validator::VALID) {
                $this->addFlash('error', $validationResult['message']);

                return $this->render('receipts_file/upload.html.twig', [
                    'form' => $form,
                ]);
            }
            try {
                $receiptsFileName = $fileUploader->upload($file);
                $data['receiptsFileName'] = $receiptsFileName;
                $receiptsFileObject = ReceiptsFile::createReceiptsFile($data);
                $receiptsFileObject->setUploadedBy($this->getUser());
                $em->persist($receiptsFileObject);
                $em->flush();
                $this->addFlash('success', 'messages.successfullySended');

                if (true === $this->getParameter('send_receiptfile_messages')) {
                    $this->sendMail($receiptsFileObject);
                }

                return $this->redirectToRoute('receipts_file_index');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('receipts_file/upload.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{receiptFile}/download', name: 'receipts_file_download')]
    public function download(ReceiptsFile $receiptFile)
    {
        $without_extension = pathinfo($receiptFile->getFileName(), PATHINFO_FILENAME);
        $fileName = $this->getParameter('receipt_files_directory').'/'.$without_extension.'.zip';
        $response = new BinaryFileResponse($fileName);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $without_extension.'.zip'
        );

        return $response;
    }

    #[Route(path: '/', name: 'receipts_file_index')]
    public function list(Request $request)
    {
        $this->loadQueryParameters($request);
        $receiptsFiles = $this->receiptFileRepo->findAll();

        return $this->render('receipts_file/index.html.twig', [
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

    private function sendMail(ReceiptsFile $receiptsFile)
    {
        $email = (new Email())
            ->from($this->getParameter('mailer_from'))
            ->to($this->getParameter('delivery_addresses'))
            ->subject('Conversión de ficheros')
            ->html($this->renderView(
                'receipts_file/mail.html.twig',
                ['receiptFile' => $receiptsFile]
            ),
            'text/html'
        );
        $this->mailer->send($email);
        return;
    }
}
