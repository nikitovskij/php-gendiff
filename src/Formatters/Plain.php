<?php

namespace App\Formatters\Plain;

use Funct\Collection;

function render($tree)
{
    return implode("\n", makePlainOutput($tree));
}


function makePlainOutput($tree)
{
    $collection = collectData($tree, '');
    return Collection\flattenAll($collection);
}


function collectData($tree, $chainOfKeys)
{
    $iter = function ($node) use ($chainOfKeys) {
        return nodeProcessing($node, $chainOfKeys);
    };

    return array_map($iter, $tree);
}

function nodeProcessing($node, $chainOfKeys)
{
    ['key' => $key, 'state' => $state, 'value' => $value] = $node;

    if (is_array($value)) {
        if ($state !== 'change') {
            $child = reset($value);

            if (isset($child['state'])) {
                $chainOfKeys .= "{$key}.";

                return collectData($value, $chainOfKeys);
            }
        }
    }

    if ($state === 'change') {
        $value['before'] = getSimpleOutput($value['before']);
        $value['after'] = getSimpleOutput($value['after']);
    } else {
        $value = getSimpleOutput($value);
    }

    $chainOfKeys .= $key;

    return genSentence([$chainOfKeys, $state, $value]);
}

function getSimpleOutput($data)
{
    if (!is_array($data)) {
        return replaceLogicValue($data);
    }

    return "[complex value]";
}

function genSentence($item)
{
    $typeOfChanges = [
        'same'   => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was not changed"),
        'new'    => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was added with value: '{$value}'"),
        'delete' => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was removed"),
        'change' => fn ($chainOfKeys, $value) => ("Property '{$chainOfKeys}' was updated. " .
                                                    "From '{$value['before']}' to '{$value['after']}'")
    ];
    
    [$chainOfKeys, $state, $value] = $item;

    return $typeOfChanges[$state]($chainOfKeys, $value);
}

function replaceLogicValue($value)
{
    $logicItems = [
        true  => "true",
        false => "false",
        null  => "null",
        ''    => "''"
    ];

    return $logicItems[$value] ?? $value;
}
