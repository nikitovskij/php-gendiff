<?php

namespace GenDiff\Formatters\Plain;

use function Funct\Collection\flattenAll;

function render(array $tree): string
{
    return implode("\n", makePlainOutput($tree));
}

function makePlainOutput(array $tree): array
{
    $format = function ($tree, $ancestorPath = []) use (&$format) {
        return array_map(function ($node) use (&$format, $ancestorPath) {
            ['key' => $key, 'state' => $state, 'value' => $value, 'children' => $children] = $node;

            $ancestorPath[] = $key;
            if ($state === 'nested') {
                return $format($children, $ancestorPath);
            }

            return generateSentence($ancestorPath, $state, $value);
        }, $tree);
    };

    return flattenAll($format($tree));
}

/**
 * @param array|string $value
 * @return string
 */
function stringifyValue($value): string
{
    $typeFormats = [
        'object'  => fn ($value) => '[complex value]',
        'array'   => fn ($value) => '[complex value]',
        'string'  => fn ($value) => $value,
        'integer' => fn ($value) => (string) $value,
        'boolean' => fn ($value) => $value ? 'true' : 'false',
        'NULL'    => fn ($value) => 'null',
    ];

    $valueType = gettype($value);

    return $typeFormats[$valueType]($value);
}

/**
 * @param array $ancestorPath
 * @param string $state
 * @param array|string $value
 * @return string
 */
function generateSentence($ancestorPath, $state, $value): string
{
    $nodePath = implode('.', $ancestorPath);
    switch ($state) {
        case 'unchanged':
            return "Property '{$nodePath}' was not changed";
        case 'new':
            $formattedValue = stringifyValue($value);
            return "Property '{$nodePath}' was added with value: '{$formattedValue}'";
        case 'deleted':
            return "Property '{$nodePath}' was removed";
        case 'changed':
            ['before' => $beforeValue, 'after' => $afterValue] = $value;
            $before = stringifyValue($beforeValue);
            $after  = stringifyValue($afterValue);
            return "Property '{$nodePath}' was updated. From '{$before}' to '{$after}'";
        default:
            throw new \Exception("Unknown type of node: `{$state}`");
    }
}
