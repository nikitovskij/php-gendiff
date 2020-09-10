<?php

namespace App\Parsers\JsonParser;

function parseJson(string $data): array
{
    return (array) json_decode($data, true);
}
