<?php

namespace GenDiff\Parsers;

use function GenDiff\Parsers\parseJson;
use function GenDiff\Parsers\parseYml;

function parseData(string $parserType, string $data): object
{
    $parsers = [
        'json' => fn ($data) => parseJson($data),
        'yml'  => fn ($data) => parseYml($data),
        'yaml' => fn ($data) => parseYml($data)
    ];

    if (!array_key_exists($parserType, $parsers)) {
        throw new \Exception("Unsupported format `{$parserType}`");
    }

    return $parsers[$parserType]($data);
}
