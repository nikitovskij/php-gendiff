<?php

namespace GenDiff\Formatters\Formatters;

use function GenDiff\Formatters\Pretty\render as renderPretty;
use function GenDiff\Formatters\Plain\render as renderPlain;
use function GenDiff\Formatters\Json\render as renderJson;

function formatData(string $renderFormat, array $data): string
{
    $formatters = [
        'pretty' => fn ($data) => renderPretty($data),
        'plain'  => fn ($data) => renderPlain($data),
        'json'   => fn ($data) => renderJson($data),
    ];

    if (!isset($formatters[$renderFormat])) {
        throw new \Exception("Unknown report format `{$renderFormat}`.\n");
    }

    return $formatters[$renderFormat]($data);
}
