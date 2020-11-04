<?php

namespace GenDiff\Formatters\Pretty;

const INDENT_SIZE = 4;

function render(array $tree): string
{
    $prettyOutput = makePrettyOutput($tree);
    return "{\n{$prettyOutput}\n}";
}

function makePrettyOutput(array $tree, int $depth = 0): string
{
    $iter = function ($node) use ($depth) {

        [
            'key'      => $key,
            'state'    => $state,
            'oldValue' => $oldValue,
            'newValue' => $newValue,
            'children' => $children
        ] = $node;

        $indent = generateIndent($depth);

        switch ($state) {
            case 'nested':
                $value       = makePrettyOutput($children, $depth + 1);
                $indentAfter = generateIndent($depth + 1);
                return "{$indent}    {$key}: {\n{$value}\n{$indentAfter}}";

            case 'changed':
                $formattedValueBefore = stringifyValue($oldValue, $depth + 1);
                $formattedValueAfter  = stringifyValue($newValue, $depth + 1);
                return "{$indent}  - {$key}: {$formattedValueBefore}\n{$indent}  + {$key}: {$formattedValueAfter}";

            case 'new':
                $formattedValue = stringifyValue($newValue, $depth + 1);
                return "{$indent}  + {$key}: {$formattedValue}";

            case 'deleted':
                $formattedValue = stringifyValue($oldValue, $depth + 1);
                return "{$indent}  - {$key}: {$formattedValue}";

            case 'unchanged':
                $formattedValue = stringifyValue($oldValue, $depth + 1);
                return "{$indent}    {$key}: {$formattedValue}";

            default:
                throw new \Exception("Unknown type of node: `{$state}`");
        }
    };

    return implode("\n", array_map($iter, $tree));
}

/**
 * @param mixed $value
 * @param int $depth
 * @return string
 */
function stringifyValue($value, $depth)
{
    $indent = generateIndent($depth);

    $stringifyComplexValue = function ($complexValue, $depth) use ($indent) {
        $iter = function ($value, $key) use ($depth, $indent) {
            $formattedValue = stringifyValue($value, $depth);
            return "{$indent}    {$key}: {$formattedValue}";
        };

        $formattedLine = implode("\n", array_map($iter, $complexValue, array_keys($complexValue)));
        return "{\n{$formattedLine}\n{$indent}}";
    };

    $typeFormats = [
        'string'  => fn($value) => $value,
        'integer' => fn($value) => (string) $value,
        'boolean' => fn($value) => $value ? 'true' : 'false',
        'NULL'    => fn($value) => 'null',
        'object'  => fn($value) => $stringifyComplexValue(get_object_vars($value), $depth + 1),
        'array'   => fn($value) => $stringifyComplexValue($value, $depth),
    ];

    $valueType = gettype($value);
    return $typeFormats[$valueType]($value);
}

function generateIndent(int $depth): string
{
    return str_repeat(' ', INDENT_SIZE * $depth);
}
