<?php

namespace GenDiff\DiffGenerator\Parsers;

use function GenDiff\DiffGenerator\Parsers\parseJson;
use function GenDiff\DiffGenerator\Parsers\parseYml;

function parseData(string $parserType, string $data): object
{
    $parsers = [
        'json' => fn ($data) => parseJson($data),
        'yml'  => fn ($data) => parseYml($data),
        'yaml' => fn ($data) => parseYml($data)
    ];

    if (!array_key_exists($parserType, $parsers)) {
        throw new \Exception('Unsupported file format');
    }

    return $parsers[$parserType]($data);
}
