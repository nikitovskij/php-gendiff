<?php

namespace App;

use App\Parsers;
use App\Formatters;

use function Funct\Collection\union;

function genDiff(string $firstFile, string $secondFile, string $format = 'pretty'): string
{
    $contentFirst = getFileContent($firstFile);
    $contentSecond = getFileContent($secondFile);

    $comparedTree = makeCompare($contentFirst, $contentSecond);
    $formattedOutput = Formatters\getFormattedData($format, $comparedTree);

    return $formattedOutput;
}

function getFileContent(string $file): array
{
    $filePath = realpath($file);
    if (!$filePath) {
        throw new \Exception("File `{$file}` not found.\n");
    }

    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
    if ($fileExtension === '') {
        throw new \Exception("Unknown file extension.\n");
    }

    $contentOfFile = file_get_contents($filePath);
    if (!$contentOfFile) {
        throw new \Exception("Can not read the file `{$file}`.\n");
    }

    return getParsedData($fileExtension, $contentOfFile);
}

function getParsedData(string $parserType, string $data = ''): array
{
    $parsers = [
        'json' => fn($data) => Parsers\parseJson($data),
        'yml'  => fn($data) => Parsers\parseYml($data),
        'yaml' => fn($data) => Parsers\parseYml($data)
    ];

    if (!isset($parsers[$parserType])) {
        throw new \Exception("Unsupported format `{$parserType}`. Supported formats: json/yaml.\n");
    }

    return $parsers[$parserType]($data);
}

function makeCompare(array $contentFirst, array $contentSecond): array
{
    $listOfKeys = union(array_keys($contentFirst), array_keys($contentSecond));
    $sortedKeys = sortKeys($listOfKeys);

    $checkChildren = function ($key) use ($contentFirst, $contentSecond) {
        $firstChild  = $contentFirst[$key] ?? null;
        $secondChild = $contentSecond[$key] ?? null;

        if (is_array($firstChild) && is_array($secondChild)) {
            return [ 'key' => $key, 'state' => 'same', 'value' => makeCompare($firstChild, $secondChild)];
        }

        return getComparingData($key, $contentFirst, $contentSecond);
    };

    return array_values(array_map($checkChildren, $sortedKeys));
}

function getComparingData(string $key, array $contentFirst, array $contentSecond): array
{
    $isChanged = isItemsChanged($key, $contentFirst, $contentSecond);
    $isAdded   = isItemAdded($key, $contentFirst, $contentSecond);
    $isDeleted = isItemDeleted($key, $contentFirst, $contentSecond);

    if ($isChanged) {
        return [
            'key' => $key,
            'state' => 'change',
            'value' => [
                'before' => $contentFirst[$key],
                'after' => $contentSecond[$key]
                ]
            ];
    }

    if ($isAdded) {
        return ['key' => $key, 'state' => 'new', 'value' => $contentSecond[$key]];
    }

    if ($isDeleted) {
        return ['key' => $key, 'state' => 'delete', 'value' => $contentFirst[$key]];
    }

    return ['key' => $key, 'state' => 'same', 'value' => $contentFirst[$key]];
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
