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
        ['key' => $key, 'state' => $state, 'value' => $value, 'children' => $children] = $node;
        $indent = generateIndent($depth);

        if ($state === 'nested') {
            $value       = makePrettyOutput($children, $depth + 1);
            $indentAfter = generateIndent($depth + 1);

            return "{$indent}    {$key}: {\n{$value}\n{$indentAfter}}";
        }

        if ($state === 'changed') {
            $valueBefore = stringifyValue($value['before'], $depth + 1);
            $valueAfter  = stringifyValue($value['after'], $depth + 1);

            return "{$indent}  - {$key}: {$valueBefore}\n{$indent}  + {$key}: {$valueAfter}";
        }

        $value = stringifyValue($value, $depth + 1);

        $nodeState = [
            'new'       => '  + ',
            'deleted'   => '  - ',
            'unchanged' => '    ',
            'nested'    => '    '
        ];

        return "{$indent}{$nodeState[$state]}{$key}: {$value}";
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
