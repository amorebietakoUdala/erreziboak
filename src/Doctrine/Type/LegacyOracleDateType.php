<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateType;

final class LegacyOracleDateType extends DateType
{
    public const NAME = 'legacy_oracle_date';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null || $value instanceof \DateTimeInterface) {
            return $value;
        }

        // Normaliza a mayúsculas por si Oracle devuelve "jun" en minúsculas
        $raw = \is_string($value) ? strtoupper(trim($value)) : $value;

        // Intenta DD/MM/YY (p.ej. 13-04-79)
        $dt = \DateTime::createFromFormat('d/m/y', $raw);
        if ($dt !== false) {
            $dt->setTime(0, 0, 0);
            return $dt;
        }

        // Intenta DD-MON-YYYY (por si acaso)
        $dt = \DateTime::createFromFormat('d/m/Y', $raw);
        if ($dt !== false) {
            $dt->setTime(0, 0, 0);
            return $dt;
        }
        
        // Fallback: que lo intente el tipo base (maneja Y-m-d)
        return parent::convertToPHPValue($value, $platform);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        // Dejamos que el tipo base formatee a lo que espera la plataforma
        return parent::convertToDatabaseValue($value, $platform);
    }
}