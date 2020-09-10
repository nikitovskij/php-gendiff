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
    $formattedOutput = getFormattedData($format, $comparedTree);

    return $formattedOutput;
}

function getFormattedData(string $format, array $data): string
{
    $handlers = [
        'pretty' => fn($data) => Formatters\Pretty\render($data),
        'plain'  => fn($data) => Formatters\Plain\render($data),
        'json'   => fn($data) => Formatters\Json\render($data),
    ];

    if (!isset($handlers[$format])) {
        throw new \Exception("Unknown report format `{$format}`.\n");
    }

    $handler = $handlers[$format] ?? $handlers['pretty'];

    return $handler($data);
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

function getParsedData(string $fileExtension, string $data): array
{
    $parsers = [
        'json' => fn($data) => Parsers\JsonParser\parseJson($data),
        'yml'  => fn($data) => Parsers\YmlParser\parseYml($data),
        'yaml' => fn($data) => Parsers\YmlParser\parseYml($data)
    ];

    if (!isset($parsers[$fileExtension])) {
        throw new \Exception("Unsupported file format `{$fileExtension}`. Supported formats: json/yaml.\n");
    }

    return $parsers[$fileExtension]($data);
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
