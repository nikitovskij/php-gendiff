<?php

namespace GenDiff\Parsers;

function parseJson(string $data): object
{
    return json_decode($data);
}
