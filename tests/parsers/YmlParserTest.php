<?php

use PHPUnit\Framework\TestCase;
use App\Parsers\YmlParser;

class YmlParserTest extends TestCase
{
    public function testYmlParsing()
    {
        $expected = [ 'host' => 'hexlet.io', 'timeout' => 50, 'proxy' => '123.234.53.22'];
        $path = __DIR__ . '/../fixtures/before.json';

        $this->assertSame($expected, YmlParser\parseYml(file_get_contents($path)));
    }
}