<?php

namespace App\Formatters\Pretty;

const INDENT = 4;
const INITIAL_DEPTH = 0;

function render(array $tree): string
{
    $prettyOutput = makePrettyOutput($tree, INITIAL_DEPTH);
    return "{\n{$prettyOutput}\n}";
}

function makePrettyOutput(array $tree, int $depth): string
{
    $iter = function ($node) use ($depth) {
        return renderNode($node, $depth);
    };

    return implode("\n", array_map($iter, $tree));
}

function renderNode(array $node, int $depth): string
{
    ['key' => $key, 'state' => $state, 'value' => $value] = $node;

    $spaces = generateSpaces($depth);
    
    if (is_array($value)) {
        if ($state !== 'changed') {
            $children = reset($value);
            $pairState = getStateSymbol($state);
            
            if (isset($children['state'])) {
                $value  = makePrettyOutput($value, $depth + 1);
                $endSpaces = generateSpaces($depth + 1);
                return "{$spaces}{$pairState}{$key}: {\n{$value}\n{$endSpaces}}";
            }
            
            $value  = getSimpleOutput($value, $depth + 1);
            return "{$spaces}{$pairState}{$key}: {$value}";
        }
        
        if ($state === 'changed') {
            $beforeStr = getSimpleOutput($value['before'], $depth + 1);
            $afterStr  = getSimpleOutput($value['after'], $depth + 1);
            
            return "{$spaces}  - {$key}: {$beforeStr}\n" . "{$spaces}  + {$key}: {$afterStr}";
        }
    }

    $value = getSimpleOutput($value, $depth + 1);
    $pairState = getStateSymbol($state);

    return "{$spaces}{$pairState}{$key}: {$value}";
}
/**
 * @param array|string $data
 * @param int $depth
 * @return string
 */
function getSimpleOutput($data, $depth)
{
    if (!is_array($data)) {
        return replaceLogicValue($data);
    }

    $listOfKeys = array_keys($data);
    $spaces = generateSpaces($depth);

    $iter = function ($key) use ($data, $spaces, $depth) {
        $value  = getSimpleOutput($data[$key], $depth + 1);
        return "{$spaces}    {$key}: {$value}";
    };

    $string = implode("\n", array_map($iter, $listOfKeys));
    return "{\n{$string}\n{$spaces}}";
}

function getStateSymbol(string $state): string
{
    $symbols = [
        'same'    => "    ",
        'new'     => "  + ",
        'deleted' => "  - ",
    ];

    return $symbols[$state];
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

function generateSpaces(int $depth): string
{
    return str_repeat(' ', INDENT * $depth);
}
