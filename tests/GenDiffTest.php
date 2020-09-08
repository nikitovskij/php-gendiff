<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Formatters;

use function App\{getFileContent, checkDiff, makeCompare};

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
    public function testRenderPrettyData(array $data)
    {
        $expectedOutput = __DIR__ . '/fixtures/prettyPlainData.json';
        $expected = json_decode(file_get_contents($expectedOutput), true);

        $this->assertSame($expected, Formatters\Pretty\render($data));
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

    public function testPlainRenderData()
    {
        $expectedOutput = __DIR__ . '/fixtures/plainFormattedData.json';
        $expected = json_decode(file_get_contents($expectedOutput), true);
        
        $filePathFirst    = __DIR__ . '/fixtures/file1.json';
        $filePathSecond   = __DIR__ . '/fixtures/file2.json';

        $actual = checkDiff($filePathFirst, $filePathSecond, 'plain');

        $this->assertSame($expected, $actual);
    }

    public function testJsonRenderData()
    {
        $expectedOutput = __DIR__ . '/fixtures/jsonFormattedData.json';
        $expected = file_get_contents($expectedOutput);
        
        $filePathFirst    = __DIR__ . '/fixtures/file1.json';
        $filePathSecond   = __DIR__ . '/fixtures/file2.json';

        $actual = checkDiff($filePathFirst, $filePathSecond, 'json');

        $this->assertSame($expected, $actual);
    }
}
