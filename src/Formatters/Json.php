<?php

namespace GenDiff\Formatters\Json;

function render(array $tree): string
{
    return (string) json_encode($tree, JSON_PRETTY_PRINT);
}
