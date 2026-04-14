<?php

namespace App\Service;

use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeService
{
    public function generateCode128(string $value): string
    {
        $generator = new BarcodeGeneratorPNG();

        // Retorna los bytes PNG de la imagen
        return $generator->getBarcode('90521480034000159216046104026605800001336039', $generator::TYPE_CODE_128);
    }
}
