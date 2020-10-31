<?php

namespace GenDiff\DiffGenerator;

use function GenDiff\Formatters\formatData;
use function Funct\Collection\union;
use function GenDiff\Parsers\parseData;

function genDiff(string $filePathOne, string $filePathTwo, string $format = 'pretty'): string
{
    $contentOfFirstFile  = read($filePathOne);
    $contentOfSecondFile = read($filePathTwo);

    $fileFormatFirst = (string) pathinfo($filePathOne, PATHINFO_EXTENSION);
    $fileFormatSecond = (string) pathinfo($filePathTwo, PATHINFO_EXTENSION);

    $dataFirst  = parseData($fileFormatFirst, $contentOfFirstFile);
    $dataSecond = parseData($fileFormatSecond, $contentOfSecondFile);

    $diffTree = genDiffTree($dataFirst, $dataSecond);

    return formatData($format, $diffTree);
}

function read(string $filePath): string
{
    if (!file_exists($filePath)) {
        throw new \Exception("File `{$filePath}` not found.\n");
    }

    return (string) file_get_contents((string) realpath($filePath));
}

function genDiffTree(object $dataFirst, object $dataSecond): array
{
    $listOfKeys = union(array_keys(get_object_vars($dataFirst)), array_keys(get_object_vars($dataSecond)));
    sort($listOfKeys);
    return array_map(function ($key) use ($dataFirst, $dataSecond) {
        $dataValueFirst  = $dataFirst->$key ?? null;
        $dataValueSecond = $dataSecond->$key ?? null;

        if (is_object($dataValueFirst) && is_object($dataValueSecond)) {
            $children = array_values(genDiffTree($dataValueFirst, $dataValueSecond));
            return makeNode('nested', $key, null, $children);
        }

        if (!property_exists($dataFirst, $key) && property_exists($dataSecond, $key)) {
            return makeNode('new', $key, $dataValueSecond);
        }

        if (property_exists($dataFirst, $key) && !property_exists($dataSecond, $key)) {
            return makeNode('deleted', $key, $dataValueFirst);
        }

        if ($dataValueFirst !== $dataValueSecond) {
            return makeNode('changed', $key, ['before' => $dataValueFirst, 'after' => $dataValueSecond]);
        }

        return makeNode('unchanged', $key, $dataValueFirst);
    }, $listOfKeys);
}

/**
 * @param string $state
 * @param string $key
 * @param null|object|string|array $value
 * @param null|array $children
 * @return array
 */
function makeNode($state, $key, $value, $children = null)
{
    return ['key' => $key, 'state' => $state, 'value' => $value, 'children' => $children];
}
