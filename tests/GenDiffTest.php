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
     * @dataProvider fixturesProvider
     */
    public function testRenderData(string $file1, string $file2, string $expected, string $outputFormat): void
    {
        $expected = file_get_contents($this->makeFilePath($expected));
        $actual   = genDiff($this->makeFilePath($file1), $this->makeFilePath($file2), $outputFormat);

        $this->assertSame($expected, $actual);
    }

    public function fixturesProvider(): array
    {
        return [
            'prettyNested' => [
                'file1.json',
                'file2.json',
                'prettyNestedData.txt',
                'pretty'
            ],
            'plainFormatted' => [
                'file1.json',
                'file2.json',
                'plainFormattedData.txt',
                'plain'
            ]
        ];
    }
}
