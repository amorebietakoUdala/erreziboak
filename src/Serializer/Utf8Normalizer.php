<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class Utf8Normalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        // Evita bucle infinito
        return !isset($context['utf8_normalized']);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $context['utf8_normalized'] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        return $this->convertToUtf8($data);
    }

    private function convertToUtf8(mixed $data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertToUtf8($value);
            }
            return $data;
        }
        if (is_string($data)) {
            return mb_detect_encoding($data, 'UTF-8', true)
                ? $data
                : mb_convert_encoding($data, 'UTF-8','Windows-1252');
        }

        return $data;
    }
}