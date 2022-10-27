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
use \Doctrine\ORM\EntityManagerInterface;
use \Symfony\Component\DependencyInjection\ContainerInterface;

class ConvertDaemonCommand extends Command
{
    protected static $defaultName = 'app:convert-daemon';
    private $em;
    private $container;
    private $sleep;

    public function __construct(EntityManagerInterface $em,ContainerInterface $container) {
        parent::__construct();
        $this->em = $em;
        $this->container = $container;
        $this->sleep = 1 * 60; // One minute
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
                /* @var $receiptFile ReceiptsFile */
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
                    $io->writeln('Return code: '.$returnCode);
                    $receiptFile->setStatus(ReceiptsFile::STATUS_PROCESSED);
                    $receiptFile->setProcessedDate(new DateTime());
                    $this->em->persist($receiptFile);
                    $this->em->flush();
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
}
