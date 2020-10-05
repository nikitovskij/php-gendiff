<?php

namespace App;

use App\Parsers;

use function App\FormatHelper\formattedData;
use function Funct\Collection\union;

function genDiff(string $filePathOne, string $filePathTwo, string $format = 'pretty'): string
{
    $contentOfFirstFile  = getFileContent($filePathOne);
    $contentOfSecondFile = getFileContent($filePathTwo);
    $comparedTree        = buildDiffTree($contentOfFirstFile, $contentOfSecondFile);

    return formattedData($format, $comparedTree);
}

function getFileContent(string $filePath): array
{
    $absoluteFilePath = (string) realpath($filePath);
    if (!file_exists($filePath)) {
        throw new \Exception("File `{$filePath}` not found.\n");
    }

    $fileFormat    = (string) pathinfo($absoluteFilePath, PATHINFO_EXTENSION);
    $contentOfFile = parseData($fileFormat, (string) file_get_contents($absoluteFilePath));

    return $contentOfFile;
}

function parseData(string $parserType, string $data = ''): array
{
    $parsers = [
        'json' => fn($data) => Parsers\parseJson($data),
        'yml'  => fn($data) => Parsers\parseYml($data),
        'yaml' => fn($data) => Parsers\parseYml($data)
    ];

    if (!isset($parsers[$parserType])) {
        throw new \Exception('Unsupported file format');
    }

    return $parsers[$parserType]($data);
}

function buildDiffTree(array $dataFirst, array $dataSecond): array
{
    $listOfKeys = union(array_keys($dataFirst), array_keys($dataSecond));
    sort($listOfKeys);

    $makeComparison = function ($key) use ($dataFirst, $dataSecond) {
        $firstDataValue  = $dataFirst[$key] ?? null;
        $secondDataValue = $dataSecond[$key] ?? null;

        if (is_array($firstDataValue) && is_array($secondDataValue)) {
            return [ 'key' => $key, 'state' => 'same', 'value' => buildDiffTree($firstDataValue, $secondDataValue)];
        }

        return compareData($key, $dataFirst, $dataSecond);
    };

    return array_values(array_map($makeComparison, $listOfKeys));
}

function compareData(string $key, array $dataFirst, array $dataSecond): array
{
    if (array_key_exists($key, $dataFirst) && array_key_exists($key, $dataSecond)) {
        if ($dataFirst[$key] !== $dataSecond[$key]) {
            return [
                'key'   => $key,
                'state' => 'changed',
                'value' => [
                    'before' => $dataFirst[$key],
                    'after'  => $dataSecond[$key]
                    ]
                ];
        }
    }

    if (!array_key_exists($key, $dataFirst) && array_key_exists($key, $dataSecond)) {
        return ['key' => $key, 'state' => 'new', 'value' => $dataSecond[$key]];
    }

    if (array_key_exists($key, $dataFirst) && !array_key_exists($key, $dataSecond)) {
        return ['key' => $key, 'state' => 'deleted', 'value' => $dataFirst[$key]];
    }

    return ['key' => $key, 'state' => 'same', 'value' => $dataFirst[$key]];
}
