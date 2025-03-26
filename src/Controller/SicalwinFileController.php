<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\SicalwinFile;
use App\Form\SicalwinFileType;
use App\Repository\SicalwinFileRepository;
use App\Service\FileUploader;
use App\Service\GestionaSicalwinValidator;
use App\Service\SicalwinGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/{_locale}/sicalwin')]
#[IsGranted('ROLE_SICALWIN')]
class SicalwinFileController extends BaseController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SicalwinFileRepository $sicalwinFileRepo, 
        private readonly MailerInterface $mailer,
        private readonly SicalwinGeneratorService $generator,
        private GestionaSicalwinValidator $validator)
    {
    }

    #[Route(path: '/', name: 'sicalwin_file_list')]
    public function list(Request $request)
    {
        $this->loadQueryParameters($request);
        $sicalwinFiles = $this->sicalwinFileRepo->findBy([], [
            'receptionDate' => 'DESC',
        ]);

        return $this->render('sicalwin_files/index.html.twig', [
            'sicalwinFiles' => $sicalwinFiles,
        ]);
    }

    #[Route(path: '/upload', name: 'sicalwin_file_upload')]
    public function upload(Request $request)
    {
        $form = $this->createForm(SicalwinFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            if (null === $file) {
                $this->addFlash('error', 'messages.fileNotSelected');

                return $this->render('sicalwin_files/upload.html.twig', [
                    'form' => $form,
                ]);
            }
            $validationResult = $this->validator->validate($file);
            if ($validationResult['status'] !== $this->validator::VALID) {
                $this->addFlash('error', $validationResult['message']);

                return $this->render('sicalwin_files/upload.html.twig', [
                      'form' => $form,
                  ]);
            }

            try {
                $fileUploader = new FileUploader($this->getParameter('sicalwin_file_upload_directory'));
                $sicalwinFileName = $fileUploader->upload($file);
                $data['sicalwinFileName'] = $sicalwinFileName;
                $sicalwinFileObject = SicalwinFile::createSicalwinFile($data);

                $totalAmount = $this->processSicalwinFile($this->getParameter('sicalwin_file_upload_directory'), $sicalwinFileObject, $this->generator);
                $sicalwinFileObject->setProcessedDate(new \DateTime());
                $sicalwinFileObject->setStatus(SicalwinFile::STATUS_PROCESSED);
                $sicalwinFileObject->setTotalAmount($totalAmount);
                $this->em->persist($sicalwinFileObject);
                $this->em->flush();
                $this->addFlash('success', 'messages.successfullySended');

                if (true === $this->getParameter('send_sicalwin_file_messages')) {
                    $this->sendMail($sicalwinFileObject);
                }

                return $this->redirectToRoute('sicalwin_file_list');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('sicalwin_files/upload.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/download', name: 'sicalwin_file_download')]
    public function download($id)
    {
        $sicalwinFile = $this->sicalwinFileRepo->find($id);
        $without_extension = pathinfo($sicalwinFile->getFileName(), PATHINFO_FILENAME);
        $fileName = $this->getParameter('sicalwin_file_upload_directory').'/'.$without_extension.'.zip';
        $response = new BinaryFileResponse($fileName);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $without_extension.'.zip'
        );

        return $response;
    }

    private function processSicalwinFile(string $path, SicalwinFile $sicalwinFile)
    {
        $totalAmount = $this->generator->createFileFrom($path, $sicalwinFile);

        return $totalAmount;
    }

    private function sendMail(SicalwinFile $sicalwinFile)
    {
        $email = (new Email())
            ->from($this->getParameter('mailer_from'))
            ->to($this->getParameter('sicalwin_files_notification_email'))
            ->subject('Conversión de ficheros de sicalwin')
            ->html($this->renderView('sicalwin_files/mail.html.twig',['sicalwinFile' => $sicalwinFile]),'text/html');
        $this->mailer->send($email);
        return $this->render('sicalwin_files/mail.html.twig', [
            'sicalwinFile' => $sicalwinFile
        ]);
    }
}
