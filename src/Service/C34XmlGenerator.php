<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\ReturnsFile;
use League\Csv\Reader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Description of C34XmlGenerator.
 *
 * @author ibilbao
 */
class C34XmlGenerator
{
    private $fileName;
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function createFileFrom(string $path, ReturnsFile $returnsFile)
    {
        $file = $path.'/'.$returnsFile->getFileName();
        $csv = Reader::createFromPath($file);
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $totalRecords = $csv->count();
        $records = $csv->getRecords();
        $totalAmount = 0;
        $cdtTrfTxInf = '';
        foreach ($records as $offset => $record) {
            $endToEndId = str_pad($offset, 12, 0, STR_PAD_LEFT);
            $amount = floatval(str_replace(',', '.', $record['Importe']));
            $totalAmount += $amount;
            $cdtr_name = $record['Nombre'].' '.$record['Apellido1'].' '.$record['Apellido2'];
            $cdtrAcct_iban = $record['Cuenta_Corriente'];
            $concept = $record['Cuerpo1'];
            $cdtTrfTxInf = $cdtTrfTxInf."\r\n".$this->createCdtTrfTxInf($endToEndId, $amount, $cdtr_name, $cdtrAcct_iban, $concept);
        }
        $grpHdr = $this->createHeader('001', $totalRecords, $totalAmount);
        $pmtInf = $this->createPmtInf('001', $cdtTrfTxInf);
        $xml = $this->createXML($grpHdr, $pmtInf);
        $this->saveFile($xml, $file);

        return $totalAmount;
    }

    private function createXML($grpHdr, $pmtInf)
    {
        $template = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
    <Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03 file:///var/www/SF5/erreziboak/public/uploads/returns_files/pain.001.001.003.xsd">
        <CstmrCdtTrfInitn>
            {GrpHdr}
            {PmtInf}
        </CstmrCdtTrfInitn>
    </Document>

XML;
        $filledXML = str_replace([
            '{GrpHdr}', '{PmtInf}',
        ], [
            $grpHdr, $pmtInf,
        ], $template);

        return $filledXML;
    }

    private function createHeader($id, $numberOfTransactions, $controlSum)
    {
        $year = date('Y');
        $currentTime = date('Y-m-d\TH:i:s');
        $initgPty = $this->params->get('c34_initgpty_name');
        $orgIdId = $this->params->get('c34_initgpty_orgid_id');
        $header = <<< 'XML'
            <GrpHdr>
                <MsgId>T/{year}/{id}</MsgId>
                <CreDtTm>{currentTime}</CreDtTm>
                <NbOfTxs>{numberOfTransactions}</NbOfTxs>
                <CtrlSum>{controlSum}</CtrlSum>
                <InitgPty>
                  <Nm>{initgPty}</Nm>
                  <Id>
                    <OrgId>
                      <Othr>
                        <Id>{orgIdId}</Id>
                      </Othr>
                    </OrgId>
                  </Id>
                </InitgPty>
            </GrpHdr>
XML;

        $headerFilled = str_replace([
            '{year}', '{id}', '{currentTime}', '{numberOfTransactions}', '{initgPty}', '{orgIdId}', '{controlSum}',
        ],
        [
            $year, $id, $currentTime, $numberOfTransactions, $initgPty, $orgIdId, $controlSum,
        ],
        $header
        );

        return $headerFilled;
    }

    private function createPmtInf($id, $cdtTrfInfPtmtInf)
    {
        $dbtr = $this->createDbtr();
        $date = date('Y-m-d');
        $dbtr_acct_iban = $this->params->get('c34_dbtr_acct_id_iban');
        $template = <<< XML
		<PmtInf>
			<PmtInfId>T/2020/{id}</PmtInfId>
			<PmtMtd>TRF</PmtMtd>
			<ReqdExctnDt>{date}</ReqdExctnDt>
            {dbtr}
			<DbtrAcct>
				<Id>
					<IBAN>{dbtr_acct_iban}</IBAN>
				</Id>
			</DbtrAcct>
			<DbtrAgt>
				<FinInstnId/>
			</DbtrAgt>
            {CdtTrfTxInf}
		</PmtInf>
XML;
        $filledPtmtInf = str_replace([
            '{id}', '{date}', '{dbtr}', '{dbtr_acct_iban}', '{CdtTrfTxInf}',
        ], [
            $id, $date, $dbtr, $dbtr_acct_iban, $cdtTrfInfPtmtInf,
        ], $template);

        return $filledPtmtInf;
    }

    private function createDbtr()
    {
        $dbtr_nm = $this->params->get('c34_dbtr_nm');
        $dbtr_ctry = $this->params->get('c34_dbtr_ctry');
        $dbtr_adrline1 = $this->params->get('c34_dbtr_adrline1');
        $dbtr_adrline2 = $this->params->get('c34_dbtr_adrline2');
        $dbtr_orgid_id = $this->params->get('c34_dbtr_orgid_id');

        $template = <<<'XML'
			<Dbtr>
			  <Nm>{dbtr_nm}</Nm>
			  <PstlAdr>
				<Ctry>{dbtr_ctry}</Ctry>
				<AdrLine>{dbtr_adrline1}</AdrLine>
				<AdrLine>{dbtr_adrline2}</AdrLine>
			  </PstlAdr>
			  <Id>
				<OrgId>
				  <Othr>
					<Id>{dbtr_orgid_id}</Id>
				  </Othr>
				</OrgId>
			  </Id>
			</Dbtr>
XML;
        $filledDbtr = str_replace([
            '{dbtr_nm}', '{dbtr_ctry}', '{dbtr_adrline1}', '{dbtr_adrline2}', '{dbtr_orgid_id}',
        ], [
            $dbtr_nm, $dbtr_ctry, $dbtr_adrline1, $dbtr_adrline2, $dbtr_orgid_id,
        ], $template);

        return $filledDbtr;
    }

    private function createCdtTrfTxInf($endToEndId, float $amount, $cdtr_name, $cdtrAcct_iban, $concept)
    {
        $template = <<<XML
			<CdtTrfTxInf>
				<PmtId>
					<EndToEndId>{endToEndId}</EndToEndId>
				</PmtId>
				<Amt>
					<InstdAmt Ccy="EUR">{amount}</InstdAmt>
				</Amt>
				<Cdtr>
					<Nm>{cdtr_name}</Nm>
					<Id>
						<OrgId>
							<Othr>
								<Id>ID</Id>
							</Othr>
						</OrgId>
					</Id>
				</Cdtr>
				<CdtrAcct>
					<Id>
						<IBAN>{cdtrAcct_iban}</IBAN>
					</Id>
				</CdtrAcct>
				<RmtInf>
				  <Ustrd>{concept}</Ustrd>
				</RmtInf>				
			</CdtTrfTxInf>
XML;
        $filledCdtTrfTxInf = str_replace([
            '{endToEndId}', '{amount}', '{cdtr_name}', '{cdtrAcct_iban}', '{concept}',
        ], [
            $endToEndId, $amount, $cdtr_name, $cdtrAcct_iban, $concept,
        ], $template);

        return $filledCdtTrfTxInf;
    }

    private function saveFile(string $fileContent, string $fullPath)
    {
        $without_extension = pathinfo($fullPath, PATHINFO_FILENAME);
        $directory = pathinfo($fullPath, PATHINFO_DIRNAME);
        file_put_contents($fullPath.'.c34', $fileContent);
        $zipFilename = $directory.'/'.$without_extension.'.zip';
        $zip = new \ZipArchive();
        if (true !== $zip->open($zipFilename, \ZipArchive::CREATE)) {
            exit("cannot open <$zipFilename>\n");
        }
        $zip->addFile($fullPath, $without_extension.'.txt');
        $zip->addFile($fullPath.'.c34', $without_extension.'.c34');
        $zip->close();

        return $zipFilename;
    }
}
