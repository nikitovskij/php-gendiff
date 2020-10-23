<?php

namespace App\Parsers;

function parseJson(string $data): object
{
    return json_decode($data);
}
