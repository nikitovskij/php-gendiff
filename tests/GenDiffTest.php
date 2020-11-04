<?php

namespace GenDiff\Tests;

use PHPUnit\Framework\TestCase;

use function GenDiff\DiffGenerator\genDiff;

class GenDiffTest extends TestCase
{
    private function makeFilePath(string $fileName): string
    {
        $parts = [__DIR__, 'fixtures', $fileName];
        return (string) realpath(implode(DIRECTORY_SEPARATOR, $parts));
    }

    /**
     *
     * @dataProvider defaultOutputProvider
     */
    public function testDefaultFormatOutput(string $fileName1, string $fileName2, string $expectedFileName): void
    {
        $expectedOutput = file_get_contents($this->makeFilePath($expectedFileName));
        $this->assertSame($expectedOutput, genDiff($this->makeFilePath($fileName1), $this->makeFilePath($fileName2)));
    }

    /**
     *
     * @dataProvider formattersProvider
     */
    public function testFormattersOutput(
        string $fileName1,
        string $fileName2,
        string $expectedFileName,
        string $outputFormat
    ): void {
        $expectedOutput = file_get_contents($this->makeFilePath($expectedFileName));
        $this->assertSame($expectedOutput, genDiff(
            $this->makeFilePath($fileName1),
            $this->makeFilePath($fileName2),
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
