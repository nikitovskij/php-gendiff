<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Formatters;

use function App\{getFileContent, genDiff, makeCompare};

class GenDiffTest extends TestCase
{
    private const PLAIN_FILE_BEFORE = __DIR__ . '/fixtures/before.json';
    private const PLAIN_FILE_AFTER  = __DIR__ . '/fixtures/after.json';
    private const PLAIN_FILE_BEFORE_YAML = __DIR__ . '/fixtures/before.yml';
    private const PLAIN_FILE_AFTER_YAML  = __DIR__ . '/fixtures/after.yml';
    private const FIRST_FILE        = __DIR__ . '/fixtures/file1.json';
    private const SECOND_FILE       = __DIR__ . '/fixtures/file2.json';

    private const FIXTURES_DIR = __DIR__ . '/fixtures/';
    
    /**
     * @param string $fileName
     * @return mixed
     */
    public function getExpectedData(string $fileName)
    {
        $filePath = self::FIXTURES_DIR . $fileName;
        $fileContent = file_get_contents($filePath);

        return json_decode((string) $fileContent, true);
    }

    public function testMakeCompare(): array
    {
        $expected = $this->getExpectedData('comparedPlainStructure.json');
        
        $firstFileContent  = getFileContent(self::PLAIN_FILE_BEFORE);
        $secondFileContent = getFileContent(self::PLAIN_FILE_AFTER_YAML);
        $actualJson        = makeCompare($firstFileContent, $secondFileContent);

        $firstFileContent  = getFileContent(self::PLAIN_FILE_BEFORE_YAML);
        $secondFileContent = getFileContent(self::PLAIN_FILE_AFTER);
        $actualYaml        = makeCompare($firstFileContent, $secondFileContent);

        $this->assertSame($expected, $actualJson);
        $this->assertSame($expected, $actualYaml);

        return makeCompare(getFileContent(self::PLAIN_FILE_BEFORE), getFileContent(self::PLAIN_FILE_AFTER));
    }

    /**
     * @depends testMakeCompare
     */
    public function testRenderPrettyData(array $data): void
    {
        $expected = $this->getExpectedData('prettyPlainData.json');

        $this->assertSame($expected, Formatters\Pretty\render($data));
    }

    public function testRenderNestedData(): void
    {
        $expected = $this->getExpectedData('prettyNestedData.json');
        $actual = genDiff(self::FIRST_FILE, self::SECOND_FILE);

        $this->assertSame($expected, $actual);
    }

    public function testPlainRenderData(): void
    {
        $expected = $this->getExpectedData('plainFormattedData.json');
        $actual = genDiff(self::FIRST_FILE, self::SECOND_FILE, 'plain');

        $this->assertSame($expected, $actual);
    }

    public function testJsonRenderData(): void
    {
        $expectedOutput = self::FIXTURES_DIR . 'jsonFormattedData.json';
        $expected = file_get_contents($expectedOutput);

        $actual = genDiff(self::FIRST_FILE, self::SECOND_FILE, 'json');

        $this->assertSame($expected, $actual);
    }
}
