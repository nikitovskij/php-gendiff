<?php

namespace GenDiff\DiffGenerator\Parsers;

use Symfony\Component\Yaml\Yaml;

function parseYml(string $data): object
{
    return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
}
