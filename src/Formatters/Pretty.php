<?php

namespace App\Formatters\Pretty;

const INDENT = 4;

function render($tree)
{
    $prettyOutput = makePrettyOutput($tree, 0);
    return "{\n{$prettyOutput}\n}";
}

function makePrettyOutput($tree, $depth)
{
    $iter = function ($node) use ($depth) {
        return renderNode($node, $depth);
    };

    return implode("\n", array_map($iter, $tree));
}

function renderNode($node, $depth)
{
    ['key' => $key, 'state' => $state, 'value' => $value] = $node;

    $spaces = generateSpaces($depth);
    
    if (is_array($value)) {
        if ($state !== 'change') {
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
        
        if ($state === 'change') {
            $beforeStr = getSimpleOutput($value['before'], $depth + 1);
            $afterStr  = getSimpleOutput($value['after'], $depth + 1);
            
            return "{$spaces}  - {$key}: {$beforeStr}\n" . "{$spaces}  + {$key}: {$afterStr}";
        }
    }

    $value = getSimpleOutput($value, $depth + 1);
    $pairState = getStateSymbol($state);

    return "{$spaces}{$pairState}{$key}: {$value}";
}

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
    return "{\n" . $string . "\n{$spaces}}";
}

function getStateSymbol($state)
{
    $symbols = [
        'same'   => "    ",
        'new'    => "  + ",
        'delete' => "  - ",
    ];

    return $symbols[$state];
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

function generateSpaces($depth)
{
    return str_repeat(' ', INDENT * $depth);
}
