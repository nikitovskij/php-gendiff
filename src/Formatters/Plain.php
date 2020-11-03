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
            [
                'key'      => $key,
                'state'    => $state,
                'oldValue' => $oldValue,
                'newValue' => $newValue,
                'children' => $children
            ] = $node;

            $nodePath = implode('.', [...$ancestorPath, $key]);
            switch ($state) {
                case 'nested':
                    return $format($children, [...$ancestorPath, $key]);

                case 'unchanged':
                    return "Property '{$nodePath}' was not changed";

                case 'new':
                    $formattedValue = stringifyValue($newValue);
                    return "Property '{$nodePath}' was added with value: '{$formattedValue}'";

                case 'deleted':
                    return "Property '{$nodePath}' was removed";

                case 'changed':
                    $valueBefore = stringifyValue($oldValue);
                    $valueAfter  = stringifyValue($newValue);
                    return "Property '{$nodePath}' was updated. From '{$valueBefore}' to '{$valueAfter}'";

                default:
                    throw new \Exception("Unknown type of node: `{$state}`");
            }
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
