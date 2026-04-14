<?php

namespace App\Service;

use Symfony\Component\Finder\Finder;

class JsonConfigManager
{
    private array $config = [];

    public function __construct(string $inctypeBodyDefinitionsPath)
    {
        $finder = new Finder();
        $finder->files()->in($inctypeBodyDefinitionsPath)->name('*.json');

        foreach ($finder as $file) {
            $data = json_decode($file->getContents(), true);

            if (!is_array($data)) {
                throw new \RuntimeException(sprintf(
                    'JSON inválido en %s',
                    $file->getRealPath()
                ));
            }

            // Fitxategiaren izena gakoa moduan erabiltzen dugu
            $key = $file->getFilenameWithoutExtension();
            $this->config[$key] = $data;
        }
    }

    public function all(): array
    {
        return $this->config;
    }

    public function get(string $fileKey): ?array
    {
        return $this->config[$fileKey] ?? null;
    }

    public function getValue(string $fileKey, string $path): mixed
    {
        // e.g: getValue('api', 'endpoints.user')
        $parts = explode('.', $path);
        $value = $this->config[$fileKey] ?? null;

        foreach ($parts as $p) {
            if (!is_array($value) || !array_key_exists($p, $value)) {
                return null;
            }
            $value = $value[$p];
        }

        return $value;
    }
}