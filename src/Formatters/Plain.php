<?php

namespace GenDiff\Formatters\Plain;

use function Funct\Collection\flattenAll;

function render(array $tree): string
{
    return implode("\n", makePlainOutput($tree));
}

function makePlainOutput(array $tree): array
{
    $format = function ($tree, $nodePath) use (&$format) {
        return array_map(function ($node) use (&$format, $nodePath) {
            ['key' => $key, 'state' => $state, 'value' => $value, 'children' => $children] = $node;

            $ancestors = implode('.', array_filter([$nodePath, $key]));
            if ($state === 'nested') {
                return $format($children, $ancestors);
            }

            if ($state === 'changed') {
                $dataValue['before'] = stringifyValue($value['before']);
                $dataValue['after']  = stringifyValue($value['after']);
            } else {
                $dataValue = stringifyValue($value);
            }

            return generateSentence([$ancestors, $state, $dataValue]);
        }, $tree);
    };

    return flattenAll($format($tree, ''));
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

function generateSentence(array $node): string
{
    $typeOfChanges = [
        'unchanged' => fn ($chainOfParents, $value) => "Property '{$chainOfParents}' was not changed",
        'new'       => fn ($chainOfParents, $value) => "Property '{$chainOfParents}' was added with value: '{$value}'",
        'deleted'   => fn ($chainOfParents, $value) => "Property '{$chainOfParents}' was removed",
        'changed'   => fn ($chainOfParents, $value) => implode('', [
                                                            "Property '{$chainOfParents}' was updated. ",
                                                            "From '{$value['before']}' to '{$value['after']}'"
                                                        ])
    ];
    
    [$chainOfParents, $state, $value] = $node;

    return $typeOfChanges[$state]($chainOfParents, $value);
}
