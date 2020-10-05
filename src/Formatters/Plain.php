<?php

namespace App\Formatters\Plain;

use function Funct\Collection\flattenAll;

function render(array $tree): string
{
    return implode("\n", makePlainOutput($tree));
}


function makePlainOutput(array $tree): array
{
    $collection = collectData($tree, '');
    return flattenAll($collection);
}


function collectData(array $tree, string $chainOfKeys): array
{
    $iter = function ($node) use ($chainOfKeys) {
        return nodeProcessing($node, $chainOfKeys);
    };

    return array_map($iter, $tree);
}
/**
 * @param array $node
 * @param string $chainOfKeys
 * @return array|string
 */
function nodeProcessing($node, $chainOfKeys)
{
    ['key' => $key, 'state' => $state, 'value' => $value] = $node;

    if (is_array($value)) {
        if ($state !== 'changed') {
            $child = reset($value);

            if (isset($child['state'])) {
                $chainOfKeys .= "{$key}.";

                return collectData($value, $chainOfKeys);
            }
        }
    }

    if ($state === 'changed') {
        $value['before'] = getSimpleOutput($value['before']);
        $value['after'] = getSimpleOutput($value['after']);
    } else {
        $value = getSimpleOutput($value);
    }

    $chainOfKeys .= $key;

    return genSentence([$chainOfKeys, $state, $value]);
}

/**
 * @param array|string $data
 * @return string
 */
function getSimpleOutput($data): string
{
    if (!is_array($data)) {
        return replaceLogicValue($data);
    }

    return "[complex value]";
}

function genSentence(array $item): string
{
    $typeOfChanges = [
        'same'    => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was not changed"),
        'new'     => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was added with value: '{$value}'"),
        'deleted' => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was removed"),
        'changed' => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was updated. " .
                                                    "From '{$value['before']}' to '{$value['after']}'")
    ];
    
    [$chainOfKeys, $state, $value] = $item;

    return $typeOfChanges[$state]($chainOfKeys, $value);
}
/**
 * @param bool|string $value
 * @return string
 */
function replaceLogicValue($value)
{
    $logicItems = [
        true  => "true",
        false => "false",
        null  => "null",
    ];

    return $logicItems[$value] ?? (string) $value;
}
