<?php

namespace App;

use App\Parsers;

use function App\Formatters\GetFormatter\formattingData;
use function Funct\Collection\union;

function genDiff(string $filePathOne, string $filePathTwo, string $format = 'pretty'): string
{
    $contentOfFirstFile  = read($filePathOne);
    $contentOfSecondFile = read($filePathTwo);
    $dataFirst  = parseData((string) pathinfo($filePathOne, PATHINFO_EXTENSION), $contentOfFirstFile);
    $dataSecond = parseData((string) pathinfo($filePathTwo, PATHINFO_EXTENSION), $contentOfSecondFile);

    $diffTree = genDiffTree($dataFirst, $dataSecond);

    return formattingData($format, $diffTree);
}

function read(string $filePath): string
{
    if (!file_exists($filePath)) {
        throw new \Exception("File `{$filePath}` not found.\n");
    }

    return (string) file_get_contents((string) realpath($filePath));
}

function parseData(string $parserType, string $data): array
{
    $parsers = [
        'json' => fn ($data) => Parsers\parseJson($data),
        'yml'  => fn ($data) => Parsers\parseYml($data),
        'yaml' => fn ($data) => Parsers\parseYml($data)
    ];

    if (!isset($parsers[$parserType])) {
        throw new \Exception('Unsupported file format');
    }

    return $parsers[$parserType]($data);
}

function genDiffTree(array $dataFirst, array $dataSecond): array
{
    $listOfKeys = union(array_keys($dataFirst), array_keys($dataSecond));
    sort($listOfKeys);
    return array_map(fn ($key) => makeComparison($key, $dataFirst, $dataSecond), $listOfKeys);
}

function makeComparison(string $key, array $dataFirst, array $dataSecond): array
{
    $dataValueFirst  = $dataFirst[$key] ?? false;
    $dataValueSecond = $dataSecond[$key] ?? false;

    if (is_array($dataValueFirst) && is_array($dataValueSecond)) {
        $children = array_values(genDiffTree($dataValueFirst, $dataValueSecond));
        return makeNode('nested', $key, null, $children);
    }

    if (array_key_exists($key, $dataFirst) && array_key_exists($key, $dataSecond)) {
        if ($dataValueFirst !== $dataValueSecond) {
            return makeNode('changed', $key, ['before' => $dataValueFirst, 'after' => $dataValueSecond]);
        }
    }

    if (!array_key_exists($key, $dataFirst) && array_key_exists($key, $dataSecond)) {
        return makeNode('new', $key, $dataValueSecond);
    }

    if (array_key_exists($key, $dataFirst) && !array_key_exists($key, $dataSecond)) {
        return makeNode('deleted', $key, $dataValueFirst);
    }

    return makeNode('unchanged', $key, $dataValueFirst);
}

/**
 * @param string $state
 * @param string $key
 * @param null|string|array $value
 * @param null|array $children
 * @return array
 */
function makeNode($state, $key, $value, $children = null)
{
    return ['key' => $key, 'state' => $state, 'value' => $value, 'children' => $children];
}
