<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use function App\genDiff;

class GenDiffTest extends TestCase
{
    private const FIXTURES_DIR = 'fixtures';
    
    private function makeFilePath(string $fileName): string
    {
        $parts = [__DIR__, self::FIXTURES_DIR, $fileName];
        return (string) realpath(implode(DIRECTORY_SEPARATOR, $parts));
    }

    /**
     *
     * @dataProvider defaultOutputProvider
     */
    public function testDefaultFormatOutput(string $file1, string $file2, string $expectedFile): void
    {
        $expectedOutput = file_get_contents($this->makeFilePath($expectedFile));
        $this->assertSame($expectedOutput, genDiff($this->makeFilePath($file1), $this->makeFilePath($file2)));
    }

    /**
     *
     * @dataProvider formattersProvider
     */
    public function testFormattersOutput(string $file1, string $file2, string $expectedFile, string $outputFormat): void
    {
        $expectedOutput = file_get_contents($this->makeFilePath($expectedFile));
        $this->assertSame($expectedOutput, genDiff(
            $this->makeFilePath($file1),
            $this->makeFilePath($file2),
            $outputFormat
        ));
    }

    public function defaultOutputProvider(): array
    {
        return [
            'default format output for JSON files' => [
                'file1.json',
                'file2.json',
                'prettyFormattedData.txt',
            ],
            'default format output for YAML files' => [
                'file1.yml',
                'file2.yml',
                'prettyFormattedData.txt',
            ],
        ];
    }
    

    public function formattersProvider(): array
    {
        return [
            'pretty format output' => [
                'file1.json',
                'file2.json',
                'prettyFormattedData.txt',
                'pretty'
            ],
            'plain format output' => [
                'file1.json',
                'file2.json',
                'plainFormattedData.txt',
                'plain'
            ],
            'json format output' => [
                'file1.json',
                'file2.json',
                'jsonFormattedData.txt',
                'json'
            ],
        ];
    }
}
