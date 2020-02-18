<?php

namespace App\Command;

use App\Entity\ReceiptsFile;
use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConvertDaemonCommand extends Command
{
    protected static $defaultName = 'app:convert-daemon';
    private $em;
    private $container;
    private $mailer;
    private $twig;
    private $transport;
    private $sleep;

    public function __construct(
          \Doctrine\ORM\EntityManagerInterface $em,
          \Symfony\Component\DependencyInjection\ContainerInterface $container,
          \Swift_Mailer $mailer,
          \Twig\Environment $twig,
          \Swift_Transport $transport
      ) {
        parent::__construct();
        $this->em = $em;
        $this->mailer = $mailer;
        $this->container = $container;
        $this->twig = $twig;
        $this->transport = $transport;
        $this->sleep = 5 * 60;
    }

    protected function configure()
    {
        $this
            ->setDescription('Starts the daemon to convert files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $command = $this->getApplication()->find('app:convert-file');

        while (true) {
            $receiptFiles = $this->em->getRepository(ReceiptsFile::class)->findBy([
                'status' => ReceiptsFile::STATUS_UNPROCESSED,
            ]);
            $io->writeln('Files to process: '.count($receiptFiles));
            $index = 1;
            foreach ($receiptFiles as $receiptFile) {
                $io->writeln('Processing file: '.$index.' of '.count($receiptFiles));
                $file = $receiptFile->getFileName();
                $receiptType = $receiptFile->getReceiptsType();
                $receiptsFinishStatus = $receiptFile->getReceiptsFinishStatus();
                $arguments = [
                        'command' => 'app:convert-file',
                        'file' => $this->container->getParameter('receipt_files_directory').'/'.$file,
                        'receiptType' => $receiptType,
                        'receiptsFinishStatus' => $receiptsFinishStatus,
                    ];
                $converFileInput = new ArrayInput($arguments);
                try {
                    $io->writeln('Processing file: '.$file);
                    $receiptFile->setStatus(ReceiptsFile::STATUS_PROCESSING);
                    $returnCode = $command->run($converFileInput, $output);
                    $receiptFile->setStatus(ReceiptsFile::STATUS_PROCESSED);
                    $receiptFile->setProcessedDate(new DateTime());
                    $this->em->persist($receiptFile);
                    $this->em->flush();
//                    $this->__sendMessage($receiptFile);
                } catch (Exception $e) {
                    $io->error($e->getMessage());
                    $returnCode = 1;
                    /* @var $receiptFile ReceiptsFile */
                    $receiptFile->setStatus(ReceiptsFile::STATUS_FAILED);
                    $receiptFile->setProcessedDate(new DateTime());
                    $this->em->persist($receiptFile);
                    $this->em->flush();
                }
                ++$index;
            }
            $io->writeln('Going to sleep...');
            sleep($this->sleep);
        }

        return 0;
    }

//    private function __sendMessage(ReceiptsFile $receiptFile)
//    {
//        $sent_from = $this->container->getParameter('mailer_user');
//        $sent_to = $this->container->getParameter('delivery_addresses');
//        $message = (new \Swift_Message('Conversión de ficheros'))
//        ->setFrom($sent_from)
//        ->setTo($sent_to)
//        ->setBody(
//            $this->twig->render(
//                'emails/mail.html.twig',
//                ['receiptFile' => $receiptFile]
//            ),
//            'text/html'
//        );
//        $this->mailer->send($message);
//        $spool = $this->mailer->getTransport()->getSpool();
//        $spool->flushQueue($this->mailer->getTransport());
//    }
}
