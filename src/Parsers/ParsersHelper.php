<?php

namespace App\Parsers\ParsersHelper;

use function App\Parsers\parseJson;
use function App\Parsers\parseYml;

function parseData(string $parserType, string $data): object
{
    $parsers = [
        'json' => fn ($data) => parseJson($data),
        'yml'  => fn ($data) => parseYml($data),
        'yaml' => fn ($data) => parseYml($data)
    ];

    if (!isset($parsers[$parserType])) {
        throw new \Exception('Unsupported file format');
    }

    return $parsers[$parserType]($data);
}
