<?php

namespace GenDiff\DiffGenerator\Parsers;

function parseJson(string $data): object
{
    return json_decode($data);
}
