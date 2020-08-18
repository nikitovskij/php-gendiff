<?php

use PHPUnit\Framework\TestCase;
use App\Parsers\JsonParser;

class JsonParserTest extends TestCase
{
    public function testJsonParsing()
    {
        $expected = [ 'host' => 'hexlet.io', 'timeout' => 50, 'proxy' => '123.234.53.22'];
        $path = __DIR__ . '/../fixtures/before.json';

        $this->assertSame($expected, JsonParser\parseJson(file_get_contents($path)));
    }
}