<?php

namespace App;

use Funct\Collection;

const FIRST_FILE = '<firstFile>';
const SECOND_FILE = '<secondFile>';

function run($args)
{
    $firstFile = $args[FIRST_FILE];
    $secondFile = $args[SECOND_FILE];

    $res = checkDiff($firstFile, $secondFile);

    return getReadableOutput($res);
}

function checkDiff($firstFile, $secondFile)
{
    $contentFirst = getFileContents($firstFile); //array
    $contentSecond = getFileContents($secondFile); //array
    $comparedData = getComparingData($contentFirst, $contentSecond);
    
    return $comparedData;
}

function getFileContents($file)
{
    $filePath = realpath($file);
    if (file_exists($filePath)) {
        $contentOfFile = file_get_contents($filePath);
        return json_decode($contentOfFile, true);
    }

    return false;
}

function getComparingData(array $contentFirst, array $contentSecond)
{
    $listOfKeys = Collection\union(array_keys($contentFirst), array_keys($contentSecond));

    $result = [];
    $comparingFunc = function ($item) use ($contentFirst, $contentSecond, $result) {
        $isPairSame = isItemsSame($item, $contentFirst, $contentSecond);
        $isChanged  = isItemsChanged($item, $contentFirst, $contentSecond);
        $isAdded    = isItemAdded($item, $contentFirst, $contentSecond);
        $isDeleted  = isItemDeleted($item, $contentFirst, $contentSecond);

        if ($isPairSame) {
            $result[$item] = $contentFirst[$item];
        }

        if ($isChanged) {
            $result["- {$item}"] = $contentFirst[$item];
            $result["+ {$item}"] = $contentSecond[$item];
        }

        if ($isAdded) {
            $result["+ {$item}"] = $contentSecond[$item];
        }

        if ($isDeleted) {
            $result["- {$item}"] = $contentFirst[$item];
        }

        return $result;
    };

    return array_values(array_map($comparingFunc, $listOfKeys));
}

function isItemsSame($key, array $dataFirst, array $dataSecond)
{
    if (array_key_exists($key, $dataFirst)) {
        if (array_key_exists($key, $dataSecond)) {
            return $dataFirst[$key] === $dataSecond[$key];
        }
    }

    return false;
}

function isItemsChanged($key, array $dataFirst, array $dataSecond)
{
    if (array_key_exists($key, $dataFirst)) {
        if (array_key_exists($key, $dataSecond)) {
            return $dataFirst[$key] !== $dataSecond[$key];
        }
    }

    return false;
}

function isItemAdded($key, array $dataFirst, array $dataSecond)
{
    return !array_key_exists($key, $dataFirst) && array_key_exists($key, $dataSecond);
}

function isItemDeleted($key, array $dataFirst, array $dataSecond)
{
    return array_key_exists($key, $dataFirst) && !array_key_exists($key, $dataSecond);
}

function getReadableOutput($data)
{
    $logicData = [
        true  => "true",
        false => "false",
        null  => "null",
        ''    => "''"
    ];

    $func = function ($value) use ($logicData) {
        $flatten = array_map(function ($key, $value) use ($logicData) {
            $value = $logicData[$value] ?? $value;
            return "    {$key} : {$value}";
        }, array_keys($value), $value);

        return $flatten;
    };
    $readableOutput = Collection\flatten(array_map($func, $data));

    return "{\n" . implode("\n", $readableOutput) . "\n}";
}
