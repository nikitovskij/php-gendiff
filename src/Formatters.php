<?php

namespace GenDiff\DiffGenerator\Formatters;

use function GenDiff\DiffGenerator\Formatters\Pretty\render as renderPretty;
use function GenDiff\DiffGenerator\Formatters\Plain\render as renderPlain;
use function GenDiff\DiffGenerator\Formatters\Json\render as renderJson;

function formatData(string $renderFormat, array $data): string
{
    $formatters = [
        'pretty' => fn ($data) => renderPretty($data),
        'plain'  => fn ($data) => renderPlain($data),
        'json'   => fn ($data) => renderJson($data),
    ];

    if (!array_key_exists($renderFormat, $formatters)) {
        throw new \Exception("Unknown report format `{$renderFormat}`.\n");
    }

    return $formatters[$renderFormat]($data);
}
