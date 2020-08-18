<?php

namespace App\Parsers\YmlParser;

use Symfony\Component\Yaml\Yaml;

function parseYml($data)
{
    return Yaml::parse($data);
}
