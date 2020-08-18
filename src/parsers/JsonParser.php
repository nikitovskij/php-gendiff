<?php

namespace App\Parsers\JsonParser;

function parseJson($data)
{
    return json_decode($data, true);
}
