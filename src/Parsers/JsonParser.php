<?php

namespace App\Parsers;

function parseJson(string $data): array
{
    return (array) json_decode($data, true);
}
