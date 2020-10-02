<?php

namespace App;

use App\Parsers;

use function App\FormatHelper\formattedData;
use function Funct\Collection\union;

function genDiff(string $filePathOne, string $filePathTwo, string $format = 'pretty'): string
{
    $contentOfFirstFile  = getFileContent($filePathOne);
    $contentOfSecondFile = getFileContent($filePathTwo);
    $comparedTree        = buildTree($contentOfFirstFile, $contentOfSecondFile);

    return formattedData($format, $comparedTree);
}

function getFileContent(string $filePath): array
{
    $absoluteFilePath = (string) realpath($filePath);
    if (!file_exists($filePath)) {
        throw new \Exception("File `{$filePath}` not found.\n");
    }

    $fileFormat    = (string) pathinfo($absoluteFilePath, PATHINFO_EXTENSION);
    $contentOfFile = (string) file_get_contents($absoluteFilePath);

    return getParsedData($fileFormat, $contentOfFile);
}

function getParsedData(string $parserType, string $data = ''): array
{
    $parsers = [
        'json' => fn($data) => Parsers\parseJson($data),
        'yml'  => fn($data) => Parsers\parseYml($data),
        'yaml' => fn($data) => Parsers\parseYml($data)
    ];

    return $parsers[$parserType]($data);
}

function buildTree(array $dataFromFirstFile, array $dataFromSecondFile): array
{
    $listOfKeys = union(array_keys($dataFromFirstFile), array_keys($dataFromSecondFile));
    $sortedKeys = sortKeys($listOfKeys);

    $checkChildren = function ($key) use ($dataFromFirstFile, $dataFromSecondFile) {
        $firstChild  = $dataFromFirstFile[$key] ?? null;
        $secondChild = $dataFromSecondFile[$key] ?? null;

        if (is_array($firstChild) && is_array($secondChild)) {
            return [ 'key' => $key, 'state' => 'same', 'value' => buildTree($firstChild, $secondChild)];
        }

        return compareData($key, $dataFromFirstFile, $dataFromSecondFile);
    };

    return array_values(array_map($checkChildren, $sortedKeys));
}

function compareData(string $key, array $dataFromFirstFile, array $dataFromSecondFile): array
{
    $isChanged = isItemsChanged($key, $dataFromFirstFile, $dataFromSecondFile);
    $isAdded   = isItemAdded($key, $dataFromFirstFile, $dataFromSecondFile);
    $isDeleted = isItemDeleted($key, $dataFromFirstFile, $dataFromSecondFile);

    if ($isChanged) {
        return [
            'key' => $key,
            'state' => 'change',
            'value' => [
                'before' => $dataFromFirstFile[$key],
                'after' => $dataFromSecondFile[$key]
                ]
            ];
    }

    if ($isAdded) {
        return ['key' => $key, 'state' => 'new', 'value' => $dataFromSecondFile[$key]];
    }

    if ($isDeleted) {
        return ['key' => $key, 'state' => 'delete', 'value' => $dataFromFirstFile[$key]];
    }

    return ['key' => $key, 'state' => 'same', 'value' => $dataFromFirstFile[$key]];
}

function isItemsSame(string $key, array $dataFirst, array $dataSecond): bool
{
    if (array_key_exists($key, $dataFirst)) {
        if (array_key_exists($key, $dataSecond)) {
            return $dataFirst[$key] === $dataSecond[$key];
        }
    }

    return false;
}

function isItemsChanged(string $key, array $dataFirst, array $dataSecond): bool
{
    if (array_key_exists($key, $dataFirst)) {
        if (array_key_exists($key, $dataSecond)) {
            return $dataFirst[$key] !== $dataSecond[$key];
        }
    }

    return false;
}

function isItemAdded(string $key, array $dataFirst, array $dataSecond): bool
{
    return !array_key_exists($key, $dataFirst) && array_key_exists($key, $dataSecond);
}

function isItemDeleted(string $key, array $dataFirst, array $dataSecond): bool
{
    return array_key_exists($key, $dataFirst) && !array_key_exists($key, $dataSecond);
}

function sortKeys(array $listOfKeys): array
{
    usort($listOfKeys, fn($firstKey, $secondKey) => $firstKey <=> $secondKey);

    return $listOfKeys;
}
