<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use function App\{getFileContent, checkDiff, makeCompare};
use function App\Renders\Pretty\render;

class GenDiffTest extends TestCase
{
    public function testGetFileContent()
    {
        $filePath = __DIR__ . '/fixtures/before.json';
        $expected = json_decode(file_get_contents($filePath), true);

        $this->assertSame($expected, getFileContent($filePath));
    }

    public function testCheckDiff()
    {
        $comparedDataPath = __DIR__ . '/fixtures/comparedPlainStructure.json';
        $filePathFirst     = __DIR__ . '/fixtures/before.json';
        $filePathSecond    = __DIR__ . '/fixtures/after.json';
        $expected = json_decode(file_get_contents($comparedDataPath), true);

        $this->assertSame($expected, makeCompare(getFileContent($filePathFirst), getFileContent($filePathSecond)));

        return makeCompare(getFileContent($filePathFirst), getFileContent($filePathSecond));
    }

    /**
     * @depends testCheckDiff
     */
    public function testRenderPlainData(array $data)
    {
        $expectedOutput = __DIR__ . '/fixtures/prettyPlainData.json';
        $expected = json_decode(file_get_contents($expectedOutput), true);

        $this->assertSame($expected, render($data));
    }

    public function testRenderNestedData()
    {
        $expectedOutput = __DIR__ . '/fixtures/prettyNestedData.json';
        $expected = json_decode(file_get_contents($expectedOutput), true);
        
        $filePathFirst    = __DIR__ . '/fixtures/file1.json';
        $filePathSecond   = __DIR__ . '/fixtures/file2.json';

        $actual = checkDiff($filePathFirst, $filePathSecond);

        $this->assertSame($expected, $actual);
    }
}
