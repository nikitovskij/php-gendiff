<?php

namespace App\Formatters;

function getFormattedData(string $outputFormat, array $data = []): string
{
    $formatters = [
        'pretty' => fn($data) => Pretty\render($data),
        'plain'  => fn($data) => Plain\render($data),
        'json'   => fn($data) => Json\render($data),
    ];

    if (!isset($formatters[$outputFormat])) {
        throw new \Exception("Unknown report format `{$outputFormat}`.\n");
    }

    return $formatters[$outputFormat]($data);
}
