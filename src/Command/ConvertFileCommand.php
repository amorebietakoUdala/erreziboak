<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

#[AsCommand(name: 'app:convert-file')]
class ConvertFileCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Converts a file from excel CSV to GTWIN .rec/.lin files.')
            ->addArgument('file', InputArgument::REQUIRED, 'File to be converted.')
            ->addArgument('receiptType', InputArgument::REQUIRED, 'ID/AU/RB')
            ->addArgument('receiptsFinishStatus', InputArgument::OPTIONAL, 'P/V (Pending/Validated)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');
        $receiptType = $input->getArgument('receiptType');
        $receiptsFinishStatus = $input->getArgument('receiptsFinishStatus');

        if ($file) {
            $io->note(sprintf('Converting file: %s', $file));
        }
        if ($receiptType) {
            $io->note(sprintf('ReceiptTypes: %s', $receiptType));
        }
        if ($receiptsFinishStatus) {
            $io->note(sprintf('ReceiptFinishStatus: %s', $receiptsFinishStatus));
        }
        $process = new Process(['./convertir.sh', $file, $receiptType, $receiptsFinishStatus], __DIR__.'/../../R');
        try {
            $process->run();
        } catch (\Exception $e) {
            throw new ProcessFailedException($process);
        }
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $io->text($process->getOutput());

        $io->success('The file has been successfully converted');

        return 0;
    }
}
