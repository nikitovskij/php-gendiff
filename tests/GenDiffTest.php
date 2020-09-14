<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Formatters;

use function App\{getFileContent, genDiff, makeCompare};

class GenDiffTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/fixtures/';
    
    private const PLAIN_FILE_BEFORE      = self::FIXTURES_DIR . 'before.json';
    private const PLAIN_FILE_AFTER       = self::FIXTURES_DIR . 'after.json';

    private const PLAIN_FILE_BEFORE_YAML = self::FIXTURES_DIR . 'before.yml';
    private const PLAIN_FILE_AFTER_YAML  = self::FIXTURES_DIR . 'after.yml';

    private const FIRST_FILE             = __DIR__ . '/fixtures/file1.json';
    private const SECOND_FILE            = __DIR__ . '/fixtures/file2.json';

    private function getExpectedData(string $fileName): string
    {
        $filePath = self::FIXTURES_DIR . $fileName;

        return (string) file_get_contents($filePath);
    }

    public function testRenderPrettyData(): void
    {
        $expected = $this->getExpectedData('prettyPlainData.txt');
        $actualJson = genDiff(self::PLAIN_FILE_BEFORE, self::PLAIN_FILE_AFTER);

        $expected = $this->getExpectedData('prettyPlainData.txt');
        $actualYaml = genDiff(self::PLAIN_FILE_BEFORE_YAML, self::PLAIN_FILE_AFTER_YAML);

        $this->assertSame($expected, $actualJson);
        $this->assertSame($expected, $actualYaml);
    }

    public function testRenderNestedData(): void
    {
        $expected = $this->getExpectedData('prettyNestedData.txt');
        $actual = genDiff(self::FIRST_FILE, self::SECOND_FILE);

        $this->assertSame($expected, $actual);
    }

    public function testPlainRenderData(): void
    {
        $expected = $this->getExpectedData('plainFormattedData.txt');
        $actual = genDiff(self::FIRST_FILE, self::SECOND_FILE, 'plain');

        $this->assertSame($expected, $actual);
    }

    public function testJsonRenderData(): void
    {
        $expected = $this->getExpectedData('jsonFormattedData.txt');
        $actual = genDiff(self::FIRST_FILE, self::SECOND_FILE, 'json');

        $this->assertSame($expected, $actual);
    }
}
