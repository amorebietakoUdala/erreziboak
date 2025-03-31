<?php


namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of ExcelGeneratorService.
 *
 * @author ibilbao
 */
class ExcelGeneratorService
{

   public function generateSpreadSheet(array $headers, $rows, $path, $fileName) {
      $sheet = new Spreadsheet();
      $writer = new Xlsx($sheet);
      $this->writeHeaders($sheet, $headers);
      $this->writeRows($sheet, $rows);
      $writer->save("$path/$fileName.xlsx");
      return "$path/$fileName.xlsx";
    }

   private function writeRow(Spreadsheet &$sheet, array $row, int $startRow = 1) {
      $startColumn = 'A';

      foreach($row as $field) {
         $sheet->getActiveSheet()->setCellValue($startColumn++.$startRow, $field);
      }
      return $sheet;
   }

   private function writeHeaders(Spreadsheet &$sheet, array $headers) {
      $this->writeRow($sheet, $headers);
      return $sheet;
   }

   private function writeRows(Spreadsheet &$sheet, array $rows, int $startRow = 2) {
      foreach ( $rows as $row ) {
         $this->writeRow($sheet, $row, $startRow++);
      }
      return $sheet;
   }

   //  private function fillContract(Worksheet &$sheet, int $startRow, Contract $contract) {
   //      $startColumn = 'A';
   //      $sheet->setCellValue($startColumn.$startRow, $contract->getCode());
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getType());
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getSubjectEs());
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getSubjectEu());
   //      $sheet->setCellValue((++$startColumn).$startRow, '');
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getAmountWithoutVAT());
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getAmountWithVAT());
   //      $sheet->setCellValue((++$startColumn).$startRow, '');
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getDurationType());
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getDuration());
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getIdentificationType());
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getIdNumber());
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getEnterprise());
   //      $sheet->setCellValue((++$startColumn).$startRow, $contract->getAwardDate()->format('d/m/Y'));
   //      $sheet->setCellValue((++$startColumn).$startRow, '');
   //      return $sheet;
   //  }

}