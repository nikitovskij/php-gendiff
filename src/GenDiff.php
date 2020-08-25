<?php

namespace App;

use App\DiffGenerator;
use Funct\Collection;

function genDiff($filePathOne, $filePathTwo)
{
    $dataBefore = getFileContent($filePathOne);
    $treeBefore = makeTree($dataBefore);
    
    $dataAfter = getFileContent($filePathTwo);
    $treeAfter = makeTree($dataAfter);
    $res = comparingData($treeBefore, $treeAfter);
    $res = genOutput($res);
    print_r($res);
    return $res;
}


function comparingData($firstTree, $secondTree)
{
    $listOfKeysFirstTree  = array_map(fn ($item) => $item['key'], $firstTree);
    $listOfKeysSecondTree = array_map(fn ($item) => $item['key'], $secondTree);

    $listOfKeys = Collection\union($listOfKeysFirstTree, $listOfKeysSecondTree);

    $checkKeyValue = function ($key) use ($firstTree, $secondTree) {
        $firstChild  = getCurrentNode($key, $firstTree)['value'] ?? null;
        $secondChild = getCurrentNode($key, $secondTree)['value'] ?? null;

        if (is_array($firstChild) && is_array($secondChild)) {
            return ['key' => $key, 'state' => 'same', 'value' => comparingData($firstChild, $secondChild)];
        }

        return makeCompareData($key, $firstTree, $secondTree);
    };

    return array_values(array_map($checkKeyValue, $listOfKeys));
}



function makeCompareData($key, $contentFirst, $contentSecond)
{
    $nodeFirst  = getCurrentNode($key, $contentFirst);
    $nodeSecond = getCurrentNode($key, $contentSecond);

    $isPairSame = isItemSame($key, $contentFirst, $contentSecond);
    $isChanged  = isItemChanged($key, $contentFirst, $contentSecond);
    $isAdded    = isItemAdded($key, $contentFirst, $contentSecond);
    $isDeleted  = isItemDeleted($key, $contentFirst, $contentSecond);

    if ($isPairSame) {
        $node = [$key, 'same', $nodeFirst['value']];
    }

    if ($isChanged) {
        $node = [$key, 'change', ['before' => $nodeFirst['value'], 'after' => $nodeSecond['value']]];
    }

    if ($isAdded) {
        $node = [$key, 'new', $nodeSecond['value']];
    }

    if ($isDeleted) {
        $node = [$key, 'delete', $nodeFirst['value']];
    }

    [$key, $state, $value] = $node;

    return [
        'key'   => $key,
        'state' => $state,
        'value' => $value
    ];
}

function isItemSame($key, array $dataFirst, array $dataSecond)
{
    $nodeFirst  = getCurrentNode($key, $dataFirst);
    $nodeSecond = getCurrentNode($key, $dataSecond);

    if (!empty($nodeFirst) && !empty($nodeSecond)) {
        return $nodeFirst['value'] === $nodeSecond['value'];
    }

    return  false;
}

function isItemChanged($key, array $dataFirst, array $dataSecond)
{
    $nodeFirst  = getCurrentNode($key, $dataFirst);
    $nodeSecond = getCurrentNode($key, $dataSecond);

    if (!empty($nodeFirst) && !empty($nodeSecond)) {
        return $nodeFirst['value'] !== $nodeSecond['value'];
    }

    return  false;
}

function isItemAdded($key, array $dataFirst, array $dataSecond)
{
    $nodeFirst  = getCurrentNode($key, $dataFirst);
    $nodeSecond = getCurrentNode($key, $dataSecond);

    return empty($nodeFirst) && !empty($nodeSecond);
}

function isItemDeleted($key, array $dataFirst, array $dataSecond)
{
    $nodeFirst  = getCurrentNode($key, $dataFirst);
    $nodeSecond = getCurrentNode($key, $dataSecond);

    return !empty($nodeFirst) && empty($nodeSecond);
}

function getCurrentNode($key, $data)
{
    $filterEqualItems = fn ($item) => $item['key'] === $key;
    $currentList  = Collection\first((array_filter($data, $filterEqualItems)));

    return $currentList;
}

function makeTree($data)
{
    $func = function ($key, $value) {
        if (is_array($value)) {
            return ['key' => $key, 'state' => 'default', 'value' => makeTree($value)];
        }
        return ['key' => $key, 'state' => 'default', 'value' => $value];
    };

    return array_map($func, array_keys($data), $data);
}

// TODO: module render()
// -------------------RENDER PART-----------------------------

function genOutput($tree)
{
    $iter = function ($node) {
        return iter($node);
    };

    return implode("\n", array_map($iter, $tree));
}

function iter($node)
{
    print_r($node);
    ['key' => $key, 'state' => $state, 'value' => $value] = $node;

    if (is_array($value) && $state !== 'change') {
        return genOutput($value);
    }
    
    if ($state === 'change') {
        $before = $value['before'];
        $after  = $value['after'];

        if (is_array($before)) {
            return genOutput($before);
        }

        if (is_array($after)) {
            return genOutput($after);
        }
    }

    return getReadablePair($node);
}

function getReadablePair($data)
{
    $str = [
        'default' => fn($keyName, $value) => "  {$keyName}: {$value}",
        'same'    => fn($keyName, $value) => "  {$keyName}: {$value}",
        'new'     => fn($keyName, $value) => "  + {$keyName}: {$value}",
        'delete'  => fn($keyName, $value) => "  - {$keyName}: {$value}",
        'change'  => fn($keyName, $value) => "  - {$keyName}: {$value['before']}\n  + {$keyName}: {$value['after']}\n"
    ];

    ['key' => $key, 'state' => $state, 'value' => $value] = $data;

    return $str[$state]($key, $value);
}

function getPlainTreeOutput($tree)
{
    $iter = function ($node) {
        return getReadablePair($node);
    };

    return array_map($iter, $tree);
}


function getFileContent($file)
{
    $filePath = realpath($file);
    if (!file_exists($filePath)) {
        throw new \Exception('File does not found.');
    }

    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
    $fileHandler   = fileHandlers($fileExtension);
    $contentOfFile = file_get_contents($filePath);

    return $fileHandler($contentOfFile);
}

function fileHandlers($fileExtension)
{
    $handlers = [
        'json' => fn($data) => getJsonData($data),
        'yml'  => fn($data) => getYamlData($data),
        'yaml' => fn($data) => getYamlData($data)
    ];

    return $handlers[$fileExtension];
}
