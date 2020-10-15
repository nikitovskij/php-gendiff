<?php

namespace App\Formatters\GetFormatter;

use function App\Formatters\Pretty\render as prettyRender;
use function App\Formatters\Plain\render as plainRender;
use function App\Formatters\Json\render as jsonRender;

function formattingData(string $renderFormat, array $data = []): string
{
    $formatters = [
        'pretty' => fn($data) => prettyRender($data),
        'plain'  => fn($data) => plainRender($data),
        'json'   => fn($data) => jsonRender($data),
    ];

    if (!isset($formatters[$renderFormat])) {
        throw new \Exception("Unknown report format `{$renderFormat}`.\n");
    }

    return $formatters[$renderFormat]($data);
}
