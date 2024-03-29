<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\ReturnsFile;
use App\Form\ReturnsFileType;
use App\Repository\ReturnsFileRepository;
use App\Service\C34XmlGenerator;
use App\Service\CsvFormatValidator;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/{_locale}/returns')]
#[IsGranted('ROLE_RETURNS')]
class ReturnsFileController extends BaseController
{

    public function __construct(private readonly ReturnsFileRepository $returnsFileRepo, private readonly MailerInterface $mailer)
    {
    }

    #[Route(path: '/', name: 'returns_file_list')]
    public function list(Request $request)
    {
        $this->loadQueryParameters($request);
        $returnsFiles = $this->returnsFileRepo->findBy([], [
            'receptionDate' => 'DESC',
        ]);

        return $this->render('returns_files/index.html.twig', [
            'returnsFiles' => $returnsFiles,
        ]);
    }

    #[Route(path: '/upload', name: 'returns_file_upload')]
    public function upload(Request $request, CsvFormatValidator $validator, C34XmlGenerator $generator, EntityManagerInterface $em)
    {
        $form = $this->createForm(ReturnsFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            if (null === $file) {
                $this->addFlash('error', 'messages.fileNotSelected');

                return $this->render('returns_files/upload.html.twig', [
                    'form' => $form,
                ]);
            }
            $validator->setRequiredFields(['Importe', 'Nombre', 'Apellido1', 'Cuenta_Corriente', 'Cuerpo1']);
            $validator->setType(CsvFormatValidator::RETURNS_TYPE);
            $validationResult = $validator->validate($file);
            if ($validationResult['status'] !== $validator::VALID) {
                $this->addFlash('error', $validationResult['message']);

                return $this->render('returns_files/upload.html.twig', [
                      'form' => $form,
                  ]);
            }

            try {
                $fileUploader = new FileUploader($this->getParameter('returns_file_upload_directory'));
                $returnsFileName = $fileUploader->upload($file);
                $data['returnsFileName'] = $returnsFileName;
                $returnsFileObject = ReturnsFile::createReturnsFile($data);

                $totalAmount = $this->processReturnsFile($this->getParameter('returns_file_upload_directory'), $returnsFileObject, $generator);
                $returnsFileObject->setProcessedDate(new \DateTime());
                $returnsFileObject->setStatus(ReturnsFile::STATUS_PROCESSED);
                $returnsFileObject->setTotalAmount($totalAmount);

                $em->persist($returnsFileObject);
                $em->flush();
                $this->addFlash('success', 'messages.successfullySended');

                if (true === $this->getParameter('send_returns_file_messages')) {
                    $this->sendMail($returnsFileObject);
                }

                return $this->redirectToRoute('returns_file_list');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('returns_files/upload.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{returnsFile}/download', name: 'returns_file_download')]
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

    private function processReturnsFile(string $path, ReturnsFile $returnsFile, C34XmlGenerator $generator)
    {
        $totalAmount = $generator->createFileFrom($path, $returnsFile);

        return $totalAmount;
    }

    private function sendMail(ReturnsFile $returnsFile)
    {
        $email = (new Email())
            ->from($this->getParameter('mailer_from'))
            ->to($this->getParameter('returns_files_notification_email'))
            ->subject('Conversión de fichero de devoluciones')
            ->html($this->renderView(
                'returns_files/mail.html.twig',
                ['returnsFile' => $returnsFile]
            ),
            'text/html'
            );
        $this->mailer->send($email);
        return $this->render('returns_files/mail.html.twig', [
            'returnsFile' => $returnsFile
        ]);
    }
}
