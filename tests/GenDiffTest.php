<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use function App\{isItemAdded, isItemDeleted, isItemsChanged, isItemsSame, getFileContents, checkDiff,
    getReadableOutput};

class GenDiffTest extends TestCase
{
    private $testingData = [
         'same' => [
            'key',
            ['key' => 'value'],
            ['key' => 'value']
         ],
         'changed' => [
            'key',
            ['key' => 'value'],
            ['key' => 'another_value']
         ],
         'deleted' => [
            'key',
            ['key' => 'value'],
            ['another_key' => 'another_value']
         ],
         'added'   => [
            'another_key',
            ['key' => 'value'],
            ['another_key' => 'another_value']
         ]
    ];

    public function testIsItemsSame()
    {
        [$key, $firstData, $secondData] = $this->testingData['same'];
        $testActualTrue   = isItemsSame($key, $firstData, $secondData);

        [$key, $firstData, $secondData] = $this->testingData['changed'];
        $testActualFalse  = isItemsSame($key, $firstData, $secondData);

        $this->assertTrue($testActualTrue);
        $this->assertFalse($testActualFalse);
    }

    public function testIsItemsChanged()
    {
        [$key, $firstData, $secondData] = $this->testingData['changed'];
        $testActualTrue   = isItemsChanged($key, $firstData, $secondData);

        [$key, $firstData, $secondData] = $this->testingData['same'];
        $testActualFalse  = isItemsChanged($key, $firstData, $secondData);

        $this->assertTrue($testActualTrue);
        $this->assertFalse($testActualFalse);
    }

    public function testIsItemAdded()
    {
        [$key, $firstData, $secondData] = $this->testingData['added'];
        $testActualTrue   = isItemAdded($key, $firstData, $secondData);

        [$key, $firstData, $secondData] = $this->testingData['deleted'];
        $testActualFalse  = isItemAdded($key, $firstData, $secondData);

        $this->assertTrue($testActualTrue);
        $this->assertFalse($testActualFalse);
    }

    public function testIsItemDeleted()
    {
        [$key, $firstData, $secondData] = $this->testingData['deleted'];
        $testActualTrue   = isItemDeleted($key, $firstData, $secondData);

        [$key, $firstData, $secondData] = $this->testingData['changed'];
        $testActualFalse  = isItemDeleted($key, $firstData, $secondData);

        $this->assertTrue($testActualTrue);
        $this->assertFalse($testActualFalse);
    }

    public function testGetFileContent()
    {
        $filePath = __DIR__ . '/fixtures/before.json';
        $expected = json_decode(file_get_contents($filePath), true);

        $this->assertSame($expected, getFileContents($filePath));
    }

    public function testCheckDiff()
    {
        $comparedDataPath = __DIR__ . '/fixtures/comparedData.json';
        $filePathFirst    = __DIR__ . '/fixtures/before.json';
        $filePathSecond   = __DIR__ . '/fixtures/after.json';
        $expected = json_decode(file_get_contents($comparedDataPath), true);

        $this->assertSame($expected, checkDiff($filePathFirst, $filePathSecond));

        return checkDiff($filePathFirst, $filePathSecond);
    }

    /**
     * @depends testCheckDiff
     */
    public function testGetReadableOutput(array $data)
    {
        $expectedOutput = [
            "{",
            "    host: hexlet.io",
            "    - timeout: 50",
            "    + timeout: 20",
            "    - proxy: 123.234.53.22",
            "    + verbose: true",
            "}"
        ];
        $expected = implode("\n", $expectedOutput);

        $this->assertSame($expected, getReadableOutput($data));
    }
}
