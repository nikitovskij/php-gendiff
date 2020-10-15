<?php

namespace App\Formatters\Pretty;

const INDENT = 4;
const INITIAL_DEPTH = 0;
const NODE_STATE_SYMBOLS = ['nested' => '    ', 'new' => '  + ', 'deleted' => '  - ', 'unchanged' => '    '];

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
    ['key' => $key, 'state' => $state, 'value' => $value, 'children' => $children] = $node;
    $indentedLine = generateIndentedLine($depth);
    
    if ($state === 'nested') {
        $value = makePrettyOutput($children, $depth + 1);
        $indentedLineAfter = generateIndentedLine($depth + 1);
        return "{$indentedLine}" . NODE_STATE_SYMBOLS[$state] . "{$key}: {\n{$value}\n{$indentedLineAfter}}";
    }

    if ($state === 'changed') {
        $valueBefore = stringifyData($value['before'], $depth + 1);
        $valueAfter  = stringifyData($value['after'], $depth + 1);

        return "{$indentedLine}" . NODE_STATE_SYMBOLS['deleted'] . "{$key}: {$valueBefore}\n"
                . "{$indentedLine}" . NODE_STATE_SYMBOLS['new'] . "{$key}: {$valueAfter}";
    }

    $valueStr = stringifyData($value, $depth + 1);
    return "{$indentedLine}" . NODE_STATE_SYMBOLS[$state] . "{$key}: {$valueStr}";
}

/**
 * @param array|string $data
 * @param int $depth
 * @return string
 */
function stringifyData($data, $depth)
{
    if (!is_array($data)) {
        return replaceLogicValue($data);
    }

    $listOfKeys = array_keys($data);
    $indentedLine = generateIndentedLine($depth);

    $iter = function ($key) use ($data, $indentedLine, $depth) {
        $value  = stringifyData($data[$key], $depth + 1);
        return "{$indentedLine}" . NODE_STATE_SYMBOLS['unchanged'] . "{$key}: {$value}";
    };

    $dataString = implode("\n", array_map($iter, $listOfKeys));
    return "{\n{$dataString}\n{$indentedLine}}";
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

function generateIndentedLine(int $depth): string
{
    return str_repeat(' ', INDENT * $depth);
}
