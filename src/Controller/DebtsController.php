<?php

namespace App\Controller;

use App\Entity\DebtsFile;
use App\Entity\GTWIN\Person;
use App\Entity\ReturnsFile;
use App\Form\DebtsFileType;
use App\Service\CsvFormatValidator;
use App\Service\FileUploader;
use App\Service\GTWINIntegrationService;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Qipsius\TCPDFBundle\Controller\TCPDFController;

/**
 * @Route("/{_locale}/debts_file", requirements={
 *	    "_locale": "es|eu|en"
 * })
 * @IsGranted("ROLE_USER")
 * })
 */
class DebtsController extends AbstractController
{
    /**
     * @Route("/", name="debts_file_list")
     */
    public function list()
    {
        $em = $this->getDoctrine()->getManager();
        $debtsFiles = $em->getRepository(DebtsFile::class)->findBy([], [
            'receptionDate' => 'DESC',
        ]);

        return $this->render('debts_files/list.html.twig', [
            'debtsFiles' => $debtsFiles,
        ]);
    }

    /**
     * @Route("/upload", name="debts_file_upload")
     */
    public function upload(Request $request, CsvFormatValidator $validator, GTWINIntegrationService $gts)
    {
        $form = $this->createForm(DebtsFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            if (null === $file) {
                $this->addFlash('error', 'messages.fileNotSelected');

                return $this->render('debts_files/upload.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $validator->setRequiredFields(['Dni']);
            $validator->setType(CsvFormatValidator::DEBTS_TYPE);
            //            $validator->setValidHeaders(['Dni']);
            $validationResult = $validator->validate($file);
            if ($validationResult['status'] !== $validator::VALID) {
                $this->addFlash('error', $validationResult['message']);

                return $this->render('debts_files/upload.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            try {
                $fileUploader = new FileUploader($this->getParameter('debts_file_upload_directory'));
                $debtsFileName = $fileUploader->upload($file);
                $data['debtsFileName'] = $debtsFileName;
                $debtsFileObject = DebtsFile::createDebtsFile($data);
                $debts = $this->processDebtsFile($this->getParameter('debts_file_upload_directory'), $debtsFileObject, $gts);
                $debtsFileObject->setProcessedDate(new DateTime());
                $debtsFileObject->setStatus(ReturnsFile::STATUS_PROCESSED);
                $debtsFileObject->setTotalAmount($debts['totalDebt']);
                $em = $this->getDoctrine()->getManager();
                $em->persist($debtsFileObject);
                $em->flush();
                $this->addFlash('success', 'messages.successfullySended');

                return $this->redirectToRoute('debts_file_list');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('debts_files/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{debtsFile}/download", name="debts_file_download")
     */
    public function download(DebtsFile $debtsFile)
    {
        $without_extension = pathinfo($debtsFile->getFileName(), PATHINFO_FILENAME);
        $fileName = $this->getParameter('debts_file_upload_directory') . '/' . $without_extension . '.zip';
        $response = new BinaryFileResponse($fileName);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $without_extension . '.zip'
        );

        return $response;
    }

    private function processDebtsFile(string $path, DebtsFile $debtsFile, GTWINIntegrationService $gts)
    {
        $file = $path . '/' . $debtsFile->getFileName();
        $csv = \League\Csv\Reader::createFromPath($file);
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();
        $totalDebt = 0;
        $debts = [];
        foreach ($records as $offset => $record) {
            $dni = $record['Dni'];
            $deuda = $gts->findDeudaTotal($dni);
            $record['Deuda'] = number_format($deuda, 2, ',', '.');
            $totalDebt += floatval($deuda);
            $debts[] = [$record];
        }
        $this->writeDebtsCsvFile($path, $debtsFile, $debts);
        $this->zipDebtsFile($path, $debtsFile);

        return [
            'debts' => $debts,
            'totalDebt' => $totalDebt,
        ];
    }

    private function writeDebtsCsvFile(string $path, DebtsFile $debtsFile, array $deudas)
    {
        $file = $path . '/' . $debtsFile->getFileName() . '-processed.csv';
        $csv = \League\Csv\Writer::createFromFileObject(new \SplFileObject($file, 'w'));
        $csv->setDelimiter(';');
        $csv->setNewline("\r\n");
        $headers = array_keys((array_values($deudas)[0])[0]);
        $csv->insertOne($headers);
        foreach ($deudas as $key => $value) {
            $csv->insertOne($value[0]);
        }
        $csv->output();
    }

    private function zipDebtsFile(string $path, DebtsFile $debtsFile)
    {
        $without_extension = pathinfo($debtsFile->getFileName(), PATHINFO_FILENAME);
        $zipFilename = $path . '/' . $without_extension . '.zip';
        $zip = new \ZipArchive();
        if (true !== $zip->open($zipFilename, \ZipArchive::CREATE)) {
            exit("cannot open <$zipFilename>\n");
        }
        $fullPath = $path . '/' . $debtsFile->getFileName();
        $zip->addFile($fullPath, $without_extension . '.txt');
        $zip->addFile($fullPath . '-processed.csv', $without_extension . '-processed.csv');
        $zip->close();

        return $zipFilename;
    }

    /**
     * @Route("/{dni}/pdf", name="debts_free_pdf_download")
     */
    public function getDebtsFreePaper(string $dni, GTWINIntegrationService $gts, TCPDFController $pdfService)
    {
        $deuda = $gts->findDeudaTotal($dni);

        if ($deuda === "0") {
            $person = $gts->findByDni($dni);
            $html =  $this->renderView('debts_files/pdf.html.twig', [
                'person' => $person
            ]);
            $this->createPdf($html, $pdfService);

            return new Response($html);
        }

        return new Response('You got debts');
    }

    private function createPdf($html, TCPDFController $pdfService)
    {
        $pdf = $pdfService->create(
            'vertical',
            PDF_UNIT,
            PDF_PAGE_FORMAT,
            true,
            'UTF-8',
            false
        );
        $pdf->SetMargins(PDF_MARGIN_LEFT, 5, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->SetAutoPageBreak(true, 0);
        $pdf->SetAuthor('Amorebitako-Etxanoko Udala');
        $pdf->SetTitle('Zorrik ez izatearen bolantea');
        $pdf->SetSubject('Zorrik ez izatearen bolantea');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 11, '', true);
        $pdf->AddPage();
        $filename = 'document';
        $pdf->writeHTMLCell(
            $w = 0,
            $h = 0,
            $x = '',
            $y = '',
            $html,
            $border = 0,
            $ln = 1,
            $fill = 0,
            $reseth = false,
            $align = '',
            $autopadding = true
        );
        //$pdf->Output($filename . '.pdf', 'D');
        $pdf->Output($filename . '.pdf', 'I');
    }
}
