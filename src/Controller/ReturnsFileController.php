<?php

namespace App\Controller;

use App\Entity\ReturnsFile;
use App\Form\ReturnsFileType;
use App\Repository\ReturnsFileRepository;
use App\Service\C34XmlGenerator;
use App\Service\CsvFormatValidator;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale}/returns")
 */
class ReturnsFileController extends AbstractController
{

    private ReturnsFileRepository $returnsFileRepo;

    public function __construct(ReturnsFileRepository $returnsFileRepo)
    {
        $this->returnsFileRepo = $returnsFileRepo;
    }

    /**
     * @Route("/", name="returns_file_list")
     */
    public function list()
    {
        $returnsFiles = $this->returnsFileRepo->findBy([], [
            'receptionDate' => 'DESC',
        ]);

        return $this->render('returns_files/list.html.twig', [
            'returnsFiles' => $returnsFiles,
        ]);
    }

    /**
     * @Route("/upload", name="returns_file_upload")
     */
    public function upload(Request $request, CsvFormatValidator $validator, Swift_Mailer $mailer, C34XmlGenerator $generator, EntityManagerInterface $em)
    {
        $form = $this->createForm(ReturnsFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            if (null === $file) {
                $this->addFlash('error', 'messages.fileNotSelected');

                return $this->render('returns_files/upload.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $validator->setRequiredFields(['Importe', 'Nombre', 'Apellido1', 'Cuenta_Corriente', 'Cuerpo1']);
            $validator->setType(CsvFormatValidator::RETURNS_TYPE);
            $validationResult = $validator->validate($file);
            if ($validationResult['status'] !== $validator::VALID) {
                $this->addFlash('error', $validationResult['message']);

                return $this->render('returns_files/upload.html.twig', [
                      'form' => $form->createView(),
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
                    $this->__sendMail($returnsFileObject, $mailer);
                }

                return $this->redirectToRoute('returns_file_list');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
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

    private function processReturnsFile(string $path, ReturnsFile $returnsFile, C34XmlGenerator $generator)
    {
        $totalAmount = $generator->createFileFrom($path, $returnsFile);

        return $totalAmount;
    }

    public function __sendMail(ReturnsFile $returnsFile, \Swift_Mailer $mailer)
    {
        $sent_from = $this->getParameter('mailer_user');
        $sent_to = $this->getParameter('returns_files_notification_email');
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
