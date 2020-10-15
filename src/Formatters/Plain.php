<?php

namespace App\Formatters\Plain;

use function Funct\Collection\flattenAll;

const START_OF_KEY_CHAIN = '';

function render(array $tree): string
{
    return implode("\n", makePlainOutput($tree));
}

function makePlainOutput(array $tree): array
{
    $collection = collectData($tree, START_OF_KEY_CHAIN);
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
    ['key' => $key, 'state' => $state, 'value' => $value, 'children' => $children] = $node;

    if ($state === 'nested') {
        $chainOfKeys .= "{$key}.";
        return collectData($children, $chainOfKeys);
    }

    if ($state === 'changed') {
        $stringValue['before'] = stringifyData($value['before']);
        $stringValue['after']  = stringifyData($value['after']);
    } else {
        $stringValue = stringifyData($value);
    }

    $chainOfKeys .= $key;
    return generatePlainSentence([$chainOfKeys, $state, $stringValue]);
}

/**
 * @param array|string $data
 * @return string
 */
function stringifyData($data): string
{
    if (!is_array($data)) {
        return replaceLogicValue($data);
    }

    return "[complex value]";
}

function generatePlainSentence(array $item): string
{
    $typeOfChanges = [
        'unchanged' => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was not changed"),
        'new'       => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was added with value: '{$value}'"),
        'deleted'   => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was removed"),
        'changed'   => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was updated. " .
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
