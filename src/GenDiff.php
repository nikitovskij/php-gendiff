<?php

namespace App;

const FIRST_FILE = '<firstFile>';
const SECOND_FILE = '<secondFile>';

function run($args)
{
    $firstFile = $args[FIRST_FILE];
    $secondFile = $args[SECOND_FILE];

    $res = checkDiff($firstFile, $secondFile);

    return genReadableOutput($res);
}

function getFileContent($file)
{
    $filePath = realpath($file);
    if (file_exists($filePath)) {
        $contentOfFile = file_get_contents($filePath);
        return json_decode($contentOfFile, true);
    }

    return false;
}

function checkDiff($firstFile, $secondFile)
{
    $contentFirst = getFileContent($firstFile); //array
    $contentSecond = getFileContent($secondFile); //array

    $result = [];

    //TODO: use filter, map, reduce instead
    foreach ($contentFirst as $key => $value) {
        foreach ($contentSecond as $skey => $svalue) {
            if (array_key_exists($key, $contentSecond)) {
                if ($key === $skey && $value === $svalue) {
                    $result[$key] = $value;
                } elseif ($key === $skey && $value !== $svalue) {
                    $removedItemKey = '- ' . $key;
                    $addedItemKey = '+ ' . $key;
                    $result[$removedItemKey] = $value;
                    $result[$addedItemKey] = $svalue;
                }
            } else {
                $removedItemKey = '- ' . $key;
                $result[$removedItemKey] = $value;
            }

            if (!array_key_exists($skey, $contentFirst)) {
                $addedItemKey = '+ ' . $skey;
                $result[$addedItemKey] = $svalue;
            }
        }
    }

    return $result;
}

function genReadableOutput($data)
{
    $func = function ($key, $value) {
        $value = $value === true ? "true" : $value;
        return "  {$key}: {$value}";
    };

    $readableOutput = array_map($func, array_keys($data), $data);

    return "{\n" . implode("\n", $readableOutput) . "\n}";
}
